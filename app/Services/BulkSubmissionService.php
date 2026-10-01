<?php

namespace App\Services;

use App\Exceptions\InsufficientWalletBalanceException;
use App\Models\Service;
use App\Models\ServiceBatch;
use App\Models\ServiceRequest;
use App\Models\User;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use InvalidArgumentException;

class BulkSubmissionService
{
    public function __construct(
        protected WalletService $walletService
    ) {}

    /**
     * Parse raw multi-line input text into clean individual entry strings.
     * Splits on newlines, commas, semicolons, and spaces.
     * Automatically splits continuous entries into 15-character lines for 15-char services (IPE Clearing, Modification IPE).
     *
     * @return array<string>
     */
    public function parseRawInput(string $rawInput, ?Service $service = null): array
    {
        // Replace commas, semicolons, and carriage returns with newlines
        $normalized = str_replace([",", ";", "\r\n", "\r"], "\n", $rawInput);
        $lines = explode("\n", $normalized);

        $isAlphanumeric15 = $service && (
            $service->validation_rule === 'alphanumeric_15' ||
            in_array($service->slug, ['ipe-clearing', 'modification-ipe'])
        );

        $entries = [];
        foreach ($lines as $line) {
            $trimmed = trim($line);
            if ($trimmed === '') {
                continue;
            }

            if ($isAlphanumeric15) {
                // Split on spaces if multiple tokens exist on the same line
                $tokens = preg_split('/\s+/', $trimmed, -1, PREG_SPLIT_NO_EMPTY);
                foreach ($tokens as $token) {
                    $clean = strtoupper(trim($token));
                    if ($clean === '') {
                        continue;
                    }

                    // When no spacing or comma is indicated and token exceeds 15 chars,
                    // split every 15 characters into an individual entry
                    if (strlen($clean) > 15) {
                        $chunks = str_split($clean, 15);
                        foreach ($chunks as $chunk) {
                            if ($chunk !== '') {
                                $entries[] = $chunk;
                            }
                        }
                    } else {
                        $entries[] = $clean;
                    }
                }
            } else {
                $entries[] = $trimmed;
            }
        }

        // Return unique preserving order
        return array_values(array_unique($entries));
    }

    /**
     * Normalize and validate an individual entry against the service rules.
     *
     * @return array{is_valid: bool, normalized: string, error: ?string}
     */
    public function normalizeAndValidateEntry(string $entry, Service $service): array
    {
        $rule = $service->validation_rule;

        if ($rule === 'alphanumeric_15') {
            // IPE Clearing & Modification IPE: exactly 15 uppercase alphanumeric chars (SRS Sec. 17 & 21)
            $normalized = strtoupper(preg_replace('/\s+/', '', $entry));

            if (! preg_match('/^[A-Z0-9]{15}$/', $normalized)) {
                return [
                    'is_valid' => false,
                    'normalized' => $normalized,
                    'error' => 'Must be exactly 15 alphanumeric characters (A-Z, 0-9).',
                ];
            }

            return [
                'is_valid' => true,
                'normalized' => $normalized,
                'error' => null,
            ];
        }

        if ($rule === 'numeric_11') {
            // NIN Validation & Delinking: exactly 11 digits (SRS Sec. 18 & 22)
            $cleaned = str_replace([' ', '-'], '', trim($entry));

            if (! preg_match('/^[0-9]{11}$/', $cleaned)) {
                return [
                    'is_valid' => false,
                    'normalized' => trim($entry),
                    'error' => 'Must be exactly 11 numeric digits.',
                ];
            }

            return [
                'is_valid' => true,
                'normalized' => $cleaned,
                'error' => null,
            ];
        }

        // Default: general non-empty string check
        $normalized = trim($entry);
        if (strlen($normalized) < 3 || strlen($normalized) > 191) {
            return [
                'is_valid' => false,
                'normalized' => $normalized,
                'error' => 'Entry length must be between 3 and 191 characters.',
            ];
        }

        return [
            'is_valid' => true,
            'normalized' => $normalized,
            'error' => null,
        ];
    }

    /**
     * Process a bulk manual service submission applying the Partial Batch Wallet Rule.
     * (SRS Section 14: Affordability quota calculation & atomic ledger charge)
     *
     * @return array{
     *     batch: ?ServiceBatch,
     *     total_submitted: int,
     *     accepted_entries: array<string>,
     *     insufficient_balance_entries: array<string>,
     *     invalid_entries: array<array{input: string, error: string}>,
     *     total_charged: float,
     *     unit_price: float
     * }
     */
    public function processBulkSubmission(User $user, Service $service, string $rawInput): array
    {
        $rawEntries = $this->parseRawInput($rawInput, $service);

        if (empty($rawEntries)) {
            throw new InvalidArgumentException('Please enter or paste at least one valid entry.');
        }

        $validEntries = [];
        $invalidEntries = [];

        foreach ($rawEntries as $raw) {
            $validation = $this->normalizeAndValidateEntry($raw, $service);
            if ($validation['is_valid']) {
                $validEntries[] = $validation['normalized'];
            } else {
                $invalidEntries[] = [
                    'input' => $raw,
                    'error' => $validation['error'],
                ];
            }
        }

        $unitPrice = (float) $service->price;
        $totalValidCount = count($validEntries);

        // Execute atomic calculation and charging inside a database transaction with pessimistic locking
        return DB::transaction(function () use (
            $user,
            $service,
            $rawEntries,
            $validEntries,
            $invalidEntries,
            $unitPrice,
            $totalValidCount
        ) {
            // Fetch current available balance under row lock to prevent race conditions
            $wallet = $this->walletService->getOrCreateWallet($user, lock: true);
            $currentBalance = (float) $wallet->balance;

            // SRS Section 14: affordable_count = floor(balance / unit_price)
            $affordableQuota = $unitPrice > 0 ? (int) floor($currentBalance / $unitPrice) : $totalValidCount;
            $acceptedCount = min($totalValidCount, $affordableQuota);

            $acceptedEntries = array_slice($validEntries, 0, $acceptedCount);
            $insufficientBalanceEntries = array_slice($validEntries, $acceptedCount);

            $totalCharged = round($acceptedCount * $unitPrice, 2);

            $batch = null;

            // If at least one entry can be afforded, execute the atomic transaction
            if ($acceptedCount > 0) {
                // 1. Charge wallet atomically with double-entry ledger entry
                $tx = $this->walletService->charge(
                    user: $user,
                    amount: $totalCharged,
                    category: 'service_charge',
                    description: "Batch Submission for {$service->name} (₦{$totalCharged} charged for " . count($acceptedEntries) . " items)",
                    metadata: [
                        'service_id' => $service->id,
                        'service_slug' => $service->slug,
                        'items_count' => count($acceptedEntries),
                    ]
                );

                // 2. Create Service Batch with an 8-digit integer identifier
                do {
                    $batchRef = (string) random_int(10000000, 99999999);
                } while (ServiceBatch::where('batch_reference', $batchRef)->exists());
                $batch = ServiceBatch::create([
                    'user_id' => $user->id,
                    'service_id' => $service->id,
                    'batch_reference' => $batchRef,
                    'total_submitted' => count($rawEntries),
                    'accepted_count' => count($acceptedEntries),
                    'rejected_count' => count($invalidEntries) + count($insufficientBalanceEntries),
                    'unit_price' => $unitPrice,
                    'total_charged' => $totalCharged,
                    'submission_notes' => "Charged transaction ref: {$tx->reference}",
                ]);

                // 3. Create individual trackable service requests for each accepted entry (SRS Section 14)
                $now = now();
                $lastId = (int) (ServiceRequest::max('id') ?? 0);
                $seq = $lastId + 1;
                foreach ($acceptedEntries as $entry) {
                    $itemRef = ServiceRequest::generateReference($seq++);
                    ServiceRequest::create([
                        'user_id' => $user->id,
                        'service_id' => $service->id,
                        'batch_id' => $batch->id,
                        'reference' => $itemRef,
                        'tracking_input' => $entry,
                        'input_payload' => [
                            'tracking_input' => $entry,
                            'batch_reference' => $batchRef,
                        ],
                        'amount_charged' => $unitPrice,
                        'status' => 'pending',
                        'created_at' => $now,
                        'updated_at' => $now,
                    ]);
                }
            }

            return [
                'batch' => $batch,
                'total_submitted' => count($rawEntries),
                'accepted_entries' => $acceptedEntries,
                'insufficient_balance_entries' => $insufficientBalanceEntries,
                'invalid_entries' => $invalidEntries,
                'total_charged' => $totalCharged,
                'unit_price' => $unitPrice,
            ];
        });
    }

    /**
     * Process a single manual service request (e.g. single NIN, BVN Retrieval, Delinking).
     */
    public function processSingleSubmission(User $user, Service $service, array $inputPayload): ServiceRequest
    {
        $unitPrice = (float) $service->price;

        return DB::transaction(function () use ($user, $service, $inputPayload, $unitPrice) {
            // Determine primary tracking input
            $trackingInput = $inputPayload['tracking_input']
                ?? $inputPayload['phone_number']
                ?? $inputPayload['full_name']
                ?? 'MANUAL_REQUEST';

            // Charge wallet if unit price is greater than zero
            if ($unitPrice > 0) {
                $this->walletService->charge(
                    user: $user,
                    amount: $unitPrice,
                    category: 'service_charge',
                    description: "Manual Request Fee for {$service->name}",
                    metadata: [
                        'service_id' => $service->id,
                        'service_slug' => $service->slug,
                        'tracking_input' => $trackingInput,
                    ]
                );
            }

            $ref = ServiceRequest::generateReference();

            return ServiceRequest::create([
                'user_id' => $user->id,
                'service_id' => $service->id,
                'batch_id' => null,
                'reference' => $ref,
                'tracking_input' => $trackingInput,
                'input_payload' => $inputPayload,
                'amount_charged' => $unitPrice,
                'status' => 'pending',
            ]);
        });
    }
}
