<?php

namespace App\Http\Controllers;

use App\Models\PaymentTransaction;
use App\Models\Setting;
use App\Services\Payment\MonnifyService;
use App\Services\WalletService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;

class MonnifyController extends Controller
{
    public function __construct(
        protected MonnifyService $monnifyService,
        protected WalletService $walletService
    ) {}

    /**
     * Initialize a Monnify payment checkout session for wallet funding.
     */
    public function initialize(Request $request): RedirectResponse
    {
        $minDeposit = (float) Setting::get('min_wallet_deposit', '500.00');

        $validated = $request->validate([
            'amount' => ['required', 'numeric', "min:{$minDeposit}", 'max:10000000'],
        ], [
            'amount.min' => "The minimum deposit amount is ₦" . number_format($minDeposit, 2) . ".",
        ]);

        if (! $this->monnifyService->isEnabled()) {
            return back()->with('error', 'Monnify payment gateway is currently disabled by administrator.');
        }

        if (! $this->monnifyService->isConfigured()) {
            return back()->with('error', 'Monnify payment gateway is not yet fully configured. Please contact support.');
        }

        $user = $request->user();
        $amount = (float) $validated['amount'];

        $initResult = $this->monnifyService->initializeTransaction($user, $amount);

        if (! ($initResult['success'] ?? false)) {
            return back()->with('error', $initResult['message'] ?? 'Failed to initialize Monnify transaction. Please try again.');
        }

        PaymentTransaction::create([
            'user_id' => $user->id,
            'payment_reference' => $initResult['payment_reference'],
            'transaction_reference' => $initResult['transaction_reference'] ?? null,
            'amount' => $amount,
            'currency' => 'NGN',
            'status' => 'pending',
            'checkout_url' => $initResult['checkout_url'] ?? null,
            'gateway' => 'monnify',
            'raw_response' => $initResult['raw'] ?? null,
        ]);

        if (! empty($initResult['checkout_url'])) {
            return redirect()->away($initResult['checkout_url']);
        }

        return back()->with('error', 'Unable to retrieve checkout URL from Monnify gateway.');
    }

    /**
     * Handle customer redirect back from Monnify hosted checkout.
     */
    public function callback(Request $request): RedirectResponse
    {
        $paymentReference = $request->query('paymentReference');

        if (empty($paymentReference)) {
            return redirect()->route('wallet.index')
                ->with('error', 'No payment reference received from Monnify gateway.');
        }

        $payment = PaymentTransaction::where('payment_reference', $paymentReference)->first();

        // Query Monnify API to verify genuine payment status
        $verification = $this->monnifyService->verifyTransaction($paymentReference);

        if (($verification['success'] ?? false) && ($verification['is_paid'] ?? false)) {
            $user = $payment ? $payment->user : $request->user();
            $amountPaid = (float) ($verification['amount_paid'] ?: ($payment?->amount ?? 0));

            // Atomically and idempotently credit the user's wallet
            if ($user && $amountPaid > 0) {
                $idempotencyKey = 'monnify_' . $paymentReference;

                $this->walletService->deposit(
                    user: $user,
                    amount: $amountPaid,
                    description: "Monnify Deposit: {$paymentReference}",
                    idempotencyKey: $idempotencyKey,
                    metadata: [
                        'gateway' => 'monnify',
                        'payment_reference' => $paymentReference,
                        'transaction_reference' => $verification['transaction_reference'] ?? null,
                        'payment_method' => $verification['payment_method'] ?? 'CARD',
                    ]
                );
            }

            if ($payment) {
                $payment->update([
                    'status' => 'paid',
                    'transaction_reference' => $verification['transaction_reference'] ?? $payment->transaction_reference,
                    'payment_method' => $verification['payment_method'] ?? $payment->payment_method,
                    'paid_at' => now(),
                    'raw_response' => $verification['raw'] ?? $payment->raw_response,
                ]);
            }

            return redirect()->route('wallet.index')->with('success', sprintf(
                'Payment Successful! ₦%s credited to your wallet balance. Ref: %s',
                number_format($amountPaid, 2),
                $paymentReference
            ));
        }

        if ($payment) {
            $payment->update([
                'status' => 'failed',
                'raw_response' => $verification['raw'] ?? $payment->raw_response,
            ]);
        }

        $reason = $verification['message'] ?? 'Payment was not completed or was cancelled.';
        return redirect()->route('wallet.index')->with('error', 'Transaction Unsuccessful: ' . $reason);
    }
}
