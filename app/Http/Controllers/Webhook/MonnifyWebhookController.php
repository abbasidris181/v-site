<?php

namespace App\Http\Controllers\Webhook;

use App\Http\Controllers\Controller;
use App\Models\MonnifyVirtualAccount;
use App\Models\PaymentTransaction;
use App\Models\User;
use App\Models\WalletTransaction;
use App\Services\Payment\MonnifyService;
use App\Services\WalletService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Log;

class MonnifyWebhookController extends Controller
{
    public function __construct(
        protected MonnifyService $monnifyService,
        protected WalletService $walletService
    ) {}

    /**
     * Handle incoming asynchronous Monnify webhook event notifications.
     * Guaranteed strictly idempotent: duplicate or repeated webhooks for the same
     * transaction will only credit the user's wallet ledger exactly once.
     */
    public function handle(Request $request): JsonResponse
    {
        $rawPayload = $request->getContent();
        $signature = $request->header('monnify-signature');

        // Verify webhook signature authenticity
        if (! $this->monnifyService->verifyWebhookSignature($rawPayload, $signature)) {
            Log::warning('Rejected Monnify webhook: Invalid signature', [
                'ip' => $request->ip(),
                'signature' => $signature,
            ]);

            return response()->json([
                'status' => 'error',
                'message' => 'Invalid webhook signature.',
            ], 400);
        }

        $payload = $request->json()->all();
        $eventType = $payload['eventType'] ?? '';
        $eventData = $payload['eventData'] ?? [];

        Log::info("Received Monnify webhook event: {$eventType}", ['payment_reference' => $eventData['paymentReference'] ?? null]);

        $paymentStatus = strtoupper($eventData['paymentStatus'] ?? '');
        $paymentReference = $eventData['paymentReference'] ?? $eventData['transactionReference'] ?? null;
        $transactionReference = $eventData['transactionReference'] ?? null;
        $amountPaid = (float) ($eventData['amountPaid'] ?? 0);
        $customerEmail = $eventData['customer']['email'] ?? null;

        $destinationAccountNumber = $eventData['destinationAccountInformation']['accountNumber'] ?? null;
        $destinationBankName = $eventData['destinationAccountInformation']['bankName'] ?? 'Bank Transfer';
        $productReference = $eventData['product']['reference'] ?? null;

        if (! $paymentReference) {
            return response()->json([
                'status' => 'ignored',
                'message' => 'No payment reference present in payload.',
            ], 200);
        }

        // Process successful payments
        if ($paymentStatus === 'PAID' || $eventType === 'SUCCESSFUL_TRANSACTION') {
            // Fast Pre-Check 1: If PaymentTransaction is already marked as paid, return 200 OK immediately
            $existingPayment = PaymentTransaction::where('payment_reference', $paymentReference)
                ->when($transactionReference, function ($q) use ($transactionReference) {
                    $q->orWhere('transaction_reference', $transactionReference);
                })
                ->first();

            if ($existingPayment && $existingPayment->status === 'paid') {
                Log::info("Monnify duplicate webhook skipped: Payment [{$paymentReference}] is already paid.");

                return response()->json([
                    'status' => 'success',
                    'message' => 'Payment has already been processed and wallet credited.',
                ], 200);
            }

            // Fast Pre-Check 2: If WalletTransaction already exists with this idempotency key, skip funding
            $alreadyCredited = WalletTransaction::where(function ($q) use ($paymentReference, $transactionReference) {
                $q->where('idempotency_key', 'monnify_' . $paymentReference);
                if ($transactionReference) {
                    $q->orWhere('idempotency_key', 'monnify_' . $transactionReference);
                }
            })->exists();

            if ($alreadyCredited) {
                Log::info("Monnify duplicate webhook skipped: Wallet ledger record exists for [{$paymentReference}].");

                return response()->json([
                    'status' => 'success',
                    'message' => 'Payment has already been processed and wallet credited.',
                ], 200);
            }

            // Concurrency Lock: Prevent multiple webhooks arriving simultaneously from racing
            $lockKey = 'lock_monnify_webhook_' . md5($paymentReference . '_' . ($transactionReference ?? ''));
            $lock = Cache::lock($lockKey, 20);

            return $lock->block(10, function () use (
                $payload,
                $eventData,
                $paymentReference,
                $transactionReference,
                $amountPaid,
                $customerEmail,
                $destinationAccountNumber,
                $destinationBankName,
                $productReference
            ) {
                // Inside lock: Re-check PaymentTransaction to ensure another process didn't just complete
                $payment = PaymentTransaction::where('payment_reference', $paymentReference)
                    ->when($transactionReference, function ($q) use ($transactionReference) {
                        $q->orWhere('transaction_reference', $transactionReference);
                    })
                    ->first();

                if ($payment && $payment->status === 'paid') {
                    return response()->json([
                        'status' => 'success',
                        'message' => 'Payment has already been processed and wallet credited.',
                    ], 200);
                }

                // Inside lock: Re-check WalletTransaction
                $alreadyCredited = WalletTransaction::where(function ($q) use ($paymentReference, $transactionReference) {
                    $q->where('idempotency_key', 'monnify_' . $paymentReference);
                    if ($transactionReference) {
                        $q->orWhere('idempotency_key', 'monnify_' . $transactionReference);
                    }
                })->exists();

                if ($alreadyCredited) {
                    return response()->json([
                        'status' => 'success',
                        'message' => 'Payment has already been processed and wallet credited.',
                    ], 200);
                }

                $user = null;
                if ($payment) {
                    $user = $payment->user;
                } elseif ($destinationAccountNumber) {
                    $user = User::findByVirtualAccount($destinationAccountNumber);
                }

                if (! $user && $productReference) {
                    $vAccount = MonnifyVirtualAccount::where('account_reference', $productReference)->first();
                    if ($vAccount) {
                        $user = $vAccount->user;
                    }
                }

                if (! $user && $customerEmail) {
                    $user = User::where('email', $customerEmail)->first();
                }

                if ($user && $amountPaid > 0) {
                    $idempotencyKey = 'monnify_' . $paymentReference;
                    $paymentMethod = $eventData['paymentMethod'] ?? ($destinationAccountNumber ? 'ACCOUNT_TRANSFER' : 'CARD');

                    $description = $destinationAccountNumber
                        ? "Monnify Dedicated Transfer ({$destinationBankName} - {$destinationAccountNumber}): {$paymentReference}"
                        : "Monnify Deposit: {$paymentReference}";

                    // Idempotent atomic deposit to wallet ledger
                    $this->walletService->deposit(
                        user: $user,
                        amount: $amountPaid,
                        description: $description,
                        idempotencyKey: $idempotencyKey,
                        metadata: [
                            'gateway' => 'monnify',
                            'payment_reference' => $paymentReference,
                            'transaction_reference' => $transactionReference,
                            'payment_method' => $paymentMethod,
                            'destination_account' => $destinationAccountNumber,
                            'bank_name' => $destinationBankName,
                            'product_reference' => $productReference,
                            'webhook' => true,
                        ]
                    );

                    PaymentTransaction::updateOrCreate(
                        ['payment_reference' => $paymentReference],
                        [
                            'user_id' => $user->id,
                            'transaction_reference' => $transactionReference,
                            'amount' => $amountPaid,
                            'currency' => $eventData['currency'] ?? 'NGN',
                            'status' => 'paid',
                            'payment_method' => $paymentMethod,
                            'gateway' => 'monnify',
                            'raw_response' => $payload,
                            'paid_at' => now(),
                        ]
                    );

                    return response()->json([
                        'status' => 'success',
                        'message' => 'Payment processed and wallet credited successfully.',
                    ], 200);
                }

                return response()->json([
                    'status' => 'received',
                    'message' => 'Event processed.',
                ], 200);
            });
        }

        return response()->json([
            'status' => 'received',
            'message' => 'Event processed.',
        ], 200);
    }
}
