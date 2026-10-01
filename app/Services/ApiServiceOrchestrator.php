<?php

namespace App\Services;

use App\Exceptions\InsufficientWalletBalanceException;
use App\Models\Service;
use App\Models\ServiceRequest;
use App\Models\User;
use App\Services\Providers\ProviderManager;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use InvalidArgumentException;
use RuntimeException;

class ApiServiceOrchestrator
{
    public function __construct(
        protected WalletService $walletService,
        protected ProviderManager $providerManager
    ) {}

    /**
     * Orchestrate end-to-end synchronous verification:
     * 1. Validate input
     * 2. Atomic wallet charge under pessimistic lock
     * 3. Provider execution via Strategy Driver
     * 4. Persist completed service request with result payload
     * 5. Return completed request with verified identity data
     *
     * @param  User  $user
     * @param  Service  $service
     * @param  string  $trackingInput
     * @param  array<string, mixed>  $extraPayload
     * @return ServiceRequest
     *
     * @throws InsufficientWalletBalanceException
     * @throws InvalidArgumentException
     * @throws RuntimeException
     */
    public function execute(User $user, Service $service, string $trackingInput, array $extraPayload = []): ServiceRequest
    {
        $normalizedInput = trim($trackingInput);
        $mode = $extraPayload['mode'] ?? 'nin';

        // 1. Input format validation
        if ($mode === 'demographics') {
            if (empty($extraPayload['first_name']) || empty($extraPayload['dob'])) {
                throw new InvalidArgumentException('Please provide both First Name and Date of Birth for demographic verification.');
            }
        } elseif ($mode === 'phone') {
            if (! preg_match('/^[0-9]{11}$/', $normalizedInput)) {
                throw new InvalidArgumentException('Please provide a valid 11-digit Nigerian phone number.');
            }
        } else {
            if (! preg_match('/^[0-9]{11}$/', $normalizedInput)) {
                throw new InvalidArgumentException('Please provide a valid 11-digit identifier (NIN or BVN).');
            }
        }

        $unitPrice = (float) $service->price;
        $driverId = $service->provider_driver ?? ($service->slug === 'bvn-verification' ? 'bvn_v1' : 'nin_v1');
        $driver = $this->providerManager->driver($driverId);

        // 2. Atomic wallet debit under lock
        $debitTransaction = DB::transaction(function () use ($user, $service, $unitPrice, $normalizedInput) {
            $wallet = $this->walletService->getOrCreateWallet($user, lock: true);

            if ((float) $wallet->balance < $unitPrice) {
                throw new InsufficientWalletBalanceException(sprintf(
                    'Insufficient wallet balance. Available: ₦%s, Service Fee: ₦%s.',
                    number_format((float) $wallet->balance, 2),
                    number_format($unitPrice, 2)
                ));
            }

            return $this->walletService->charge(
                user: $user,
                amount: $unitPrice,
                category: 'api_service_charge',
                description: "Verification Charge: {$service->name} (#{$normalizedInput})",
                metadata: [
                    'service_id' => $service->id,
                    'service_slug' => $service->slug,
                    'identifier' => $normalizedInput,
                ]
            );
        });

        // 3. Provider Driver Execution
        $queryPayload = array_merge($extraPayload, [
            'tracking_input' => $normalizedInput,
        ]);

        $result = $driver->verify($service, $queryPayload);

        // If provider fails, refund customer immediately
        if (! $result->isSuccessful) {
            $this->walletService->refund(
                user: $user,
                amount: $unitPrice,
                description: "Automatic Reversal: Provider Failure for {$service->name}",
                metadata: [
                    'debit_transaction_id' => $debitTransaction->id,
                    'error_code' => $result->errorCode,
                ]
            );

            $msg = $result->statusMessage ?? 'Verification provider failed to resolve identity record.';
            if (! str_contains(strtolower($msg), 'refund')) {
                $msg .= ' (Your wallet balance has been refunded).';
            }

            throw new RuntimeException($msg);
        }

        // 4. Record Completed Service Request with Result Payload
        $reference = ServiceRequest::generateReference();

        return ServiceRequest::create([
            'user_id' => $user->id,
            'service_id' => $service->id,
            'batch_id' => null,
            'reference' => $reference,
            'tracking_input' => $normalizedInput,
            'input_payload' => array_merge($queryPayload, ['provider_driver' => $driverId]),
            'amount_charged' => $unitPrice,
            'status' => 'completed',
            'assigned_to' => null,
            'processed_by' => null,
            'result_payload' => $result->toArray(),
            'admin_notes' => 'Instant verification executed via ' . $driver->getProviderId(),
            'completed_at' => now(),
        ]);
    }
}
