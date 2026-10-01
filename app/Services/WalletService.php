<?php

namespace App\Services;

use App\Exceptions\InsufficientWalletBalanceException;
use App\Exceptions\UnauthorizedWalletTransferException;
use App\Models\User;
use App\Models\Wallet;
use App\Models\WalletTransaction;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use InvalidArgumentException;

class WalletService
{
    /**
     * Retrieve the wallet for a user, creating one if it does not yet exist.
     * Optionally applies a pessimistic row lock (lockForUpdate).
     */
    public function getOrCreateWallet(User $user, bool $lock = false): Wallet
    {
        $query = Wallet::where('user_id', $user->id);

        if ($lock) {
            $query->lockForUpdate();
        }

        $wallet = $query->first();

        if (! $wallet) {
            $wallet = Wallet::create([
                'user_id' => $user->id,
                'balance' => 0.00,
                'currency' => 'NGN',
            ]);

            if ($lock) {
                $wallet = Wallet::where('id', $wallet->id)->lockForUpdate()->first();
            }
        }

        return $wallet;
    }

    /**
     * Deposit funds into a user's wallet.
     * Guaranteed atomic with double-entry ledger recording and idempotency protection.
     */
    public function deposit(
        User $user,
        float $amount,
        string $description = 'Wallet Funding',
        ?string $idempotencyKey = null,
        array $metadata = []
    ): WalletTransaction {
        if ($amount <= 0) {
            throw new InvalidArgumentException('Deposit amount must be strictly greater than zero.');
        }

        // Idempotency check
        if ($idempotencyKey) {
            $existing = WalletTransaction::where('idempotency_key', $idempotencyKey)->first();
            if ($existing) {
                return $existing;
            }
        }

        return DB::transaction(function () use ($user, $amount, $description, $idempotencyKey, $metadata) {
            $wallet = $this->getOrCreateWallet($user, lock: true);

            // Double check idempotency under pessimistic lock to prevent concurrent race conditions
            if ($idempotencyKey) {
                $existing = WalletTransaction::where('idempotency_key', $idempotencyKey)->first();
                if ($existing) {
                    return $existing;
                }
            }

            $balanceBefore = (float) $wallet->balance;
            $balanceAfter = round($balanceBefore + $amount, 2);

            $wallet->balance = $balanceAfter;
            $wallet->save();

            $reference = 'WTX_DEP_' . strtoupper(Str::random(12)) . '_' . time();

            try {
                return WalletTransaction::create([
                    'wallet_id' => $wallet->id,
                    'user_id' => $user->id,
                    'type' => 'credit',
                    'amount' => $amount,
                    'balance_before' => $balanceBefore,
                    'balance_after' => $balanceAfter,
                    'reference' => $reference,
                    'idempotency_key' => $idempotencyKey,
                    'category' => 'deposit',
                    'status' => 'successful',
                    'counterpart_user_id' => null,
                    'description' => $description,
                    'metadata' => $metadata,
                ]);
            } catch (\Illuminate\Database\QueryException $e) {
                if ($idempotencyKey) {
                    $existing = WalletTransaction::where('idempotency_key', $idempotencyKey)->first();
                    if ($existing) {
                        return $existing;
                    }
                }
                throw $e;
            }
        });
    }

    /**
     * Debit a user's wallet for a service charge or fee.
     * Uses pessimistic locking to prevent race conditions and overdrafts.
     */
    public function charge(
        User $user,
        float $amount,
        string $category = 'service_charge',
        string $description = 'Service Charge',
        ?string $idempotencyKey = null,
        array $metadata = []
    ): WalletTransaction {
        if ($amount <= 0) {
            throw new InvalidArgumentException('Charge amount must be strictly greater than zero.');
        }

        // Idempotency check
        if ($idempotencyKey) {
            $existing = WalletTransaction::where('idempotency_key', $idempotencyKey)->first();
            if ($existing) {
                return $existing;
            }
        }

        return DB::transaction(function () use ($user, $amount, $category, $description, $idempotencyKey, $metadata) {
            $wallet = $this->getOrCreateWallet($user, lock: true);

            $balanceBefore = (float) $wallet->balance;

            if ($balanceBefore < $amount) {
                throw new InsufficientWalletBalanceException(sprintf(
                    'Insufficient wallet balance. Available: ₦%s, Required: ₦%s.',
                    number_format($balanceBefore, 2),
                    number_format($amount, 2)
                ));
            }

            $balanceAfter = round($balanceBefore - $amount, 2);

            $wallet->balance = $balanceAfter;
            $wallet->save();

            $reference = 'WTX_CHG_' . strtoupper(Str::random(12)) . '_' . time();

            return WalletTransaction::create([
                'wallet_id' => $wallet->id,
                'user_id' => $user->id,
                'type' => 'debit',
                'amount' => $amount,
                'balance_before' => $balanceBefore,
                'balance_after' => $balanceAfter,
                'reference' => $reference,
                'idempotency_key' => $idempotencyKey,
                'category' => $category,
                'status' => 'successful',
                'counterpart_user_id' => null,
                'description' => $description,
                'metadata' => $metadata,
            ]);
        });
    }

    /**
     * Credit a refund back to a user's wallet.
     */
    public function refund(
        User $user,
        float $amount,
        string $description = 'Service Refund',
        ?string $idempotencyKey = null,
        array $metadata = []
    ): WalletTransaction {
        if ($amount <= 0) {
            throw new InvalidArgumentException('Refund amount must be strictly greater than zero.');
        }

        if ($idempotencyKey) {
            $existing = WalletTransaction::where('idempotency_key', $idempotencyKey)->first();
            if ($existing) {
                return $existing;
            }
        }

        return DB::transaction(function () use ($user, $amount, $description, $idempotencyKey, $metadata) {
            $wallet = $this->getOrCreateWallet($user, lock: true);

            $balanceBefore = (float) $wallet->balance;
            $balanceAfter = round($balanceBefore + $amount, 2);

            $wallet->balance = $balanceAfter;
            $wallet->save();

            $reference = 'WTX_REF_' . strtoupper(Str::random(12)) . '_' . time();

            return WalletTransaction::create([
                'wallet_id' => $wallet->id,
                'user_id' => $user->id,
                'type' => 'credit',
                'amount' => $amount,
                'balance_before' => $balanceBefore,
                'balance_after' => $balanceAfter,
                'reference' => $reference,
                'idempotency_key' => $idempotencyKey,
                'category' => 'manual_refund',
                'status' => 'successful',
                'counterpart_user_id' => null,
                'description' => $description,
                'metadata' => $metadata,
            ]);
        });
    }

    /**
     * Transfer funds between two user wallets (Admin-to-User, Staff-to-User, Agent-to-User).
     *
     * Invariants guaranteed:
     * 1. Authorization: Sender must have permission or role to fund users.
     * 2. Self-transfer prevented.
     * 3. Deterministic lock ordering (min ID first) to prevent SQL deadlocks.
     * 4. Atomic execution inside a single database transaction.
     * 5. Double-entry ledger: paired debit on sender and credit on recipient.
     */
    public function transfer(
        User $sender,
        User $recipient,
        float $amount,
        ?string $description = null,
        ?string $idempotencyKey = null
    ): array {
        if ($amount <= 0) {
            throw new InvalidArgumentException('Transfer amount must be strictly greater than zero.');
        }

        if ($sender->id === $recipient->id) {
            throw new InvalidArgumentException('Cannot transfer funds to your own wallet.');
        }

        // Authorization check (SRS Sections 5, 6, 7, 8)
        if (! $sender->canFundOtherUsers()) {
            throw new UnauthorizedWalletTransferException(
                'You do not have permission to fund other users from your wallet.'
            );
        }

        // Idempotency check for sender's transfer
        if ($idempotencyKey) {
            $existingDebit = WalletTransaction::where('idempotency_key', $idempotencyKey)->first();
            if ($existingDebit) {
                $existingCredit = WalletTransaction::where('counterpart_user_id', $sender->id)
                    ->where('user_id', $recipient->id)
                    ->where('created_at', '>=', $existingDebit->created_at->subSeconds(5))
                    ->first();

                return [
                    'debit_transaction' => $existingDebit,
                    'credit_transaction' => $existingCredit,
                ];
            }
        }

        return DB::transaction(function () use ($sender, $recipient, $amount, $description, $idempotencyKey) {
            // Deadlock prevention: Lock in ascending order of user IDs
            $firstUser = $sender->id < $recipient->id ? $sender : $recipient;
            $secondUser = $sender->id < $recipient->id ? $recipient : $sender;

            $firstWallet = $this->getOrCreateWallet($firstUser, lock: true);
            $secondWallet = $this->getOrCreateWallet($secondUser, lock: true);

            $senderWallet = $sender->id === $firstUser->id ? $firstWallet : $secondWallet;
            $recipientWallet = $recipient->id === $firstUser->id ? $firstWallet : $secondWallet;

            $senderBefore = (float) $senderWallet->balance;

            if ($senderBefore < $amount) {
                throw new InsufficientWalletBalanceException(sprintf(
                    'Transfer failed: Insufficient balance. Available: ₦%s, Required: ₦%s.',
                    number_format($senderBefore, 2),
                    number_format($amount, 2)
                ));
            }

            $senderAfter = round($senderBefore - $amount, 2);
            $senderWallet->balance = $senderAfter;
            $senderWallet->save();

            $recipientBefore = (float) $recipientWallet->balance;
            $recipientAfter = round($recipientBefore + $amount, 2);
            $recipientWallet->balance = $recipientAfter;
            $recipientWallet->save();

            $batchToken = strtoupper(Str::random(10));
            $senderRef = 'WTX_TRF_OUT_' . $batchToken . '_' . time();
            $recipientRef = 'WTX_TRF_IN_' . $batchToken . '_' . time();

            $senderDesc = $description ?: "Transfer to {$recipient->full_name} ({$recipient->email})";
            $recipientDesc = $description ?: "Funds received from {$sender->full_name} ({$sender->email})";

            $debitTx = WalletTransaction::create([
                'wallet_id' => $senderWallet->id,
                'user_id' => $sender->id,
                'type' => 'debit',
                'amount' => $amount,
                'balance_before' => $senderBefore,
                'balance_after' => $senderAfter,
                'reference' => $senderRef,
                'idempotency_key' => $idempotencyKey,
                'category' => 'transfer_out',
                'status' => 'successful',
                'counterpart_user_id' => $recipient->id,
                'description' => $senderDesc,
                'metadata' => [
                    'recipient_email' => $recipient->email,
                    'recipient_name' => $recipient->full_name,
                ],
            ]);

            $creditTx = WalletTransaction::create([
                'wallet_id' => $recipientWallet->id,
                'user_id' => $recipient->id,
                'type' => 'credit',
                'amount' => $amount,
                'balance_before' => $recipientBefore,
                'balance_after' => $recipientAfter,
                'reference' => $recipientRef,
                'idempotency_key' => null,
                'category' => 'transfer_in',
                'status' => 'successful',
                'counterpart_user_id' => $sender->id,
                'description' => $recipientDesc,
                'metadata' => [
                    'sender_email' => $sender->email,
                    'sender_name' => $sender->full_name,
                ],
            ]);

            return [
                'debit_transaction' => $debitTx,
                'credit_transaction' => $creditTx,
            ];
        });
    }
}
