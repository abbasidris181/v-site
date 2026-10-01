<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\User;
use App\Models\Wallet;
use App\Models\WalletTransaction;
use App\Services\WalletService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class FinanceController extends Controller
{
    public function __construct(
        protected WalletService $walletService
    ) {}

    /**
     * Display the Fund User form.
     */
    public function fundUserForm(Request $request): View
    {
        $selectedUser = null;
        if ($userId = $request->query('user_id')) {
            $selectedUser = User::with('wallet')->find($userId);
        }

        $search = $request->query('search');
        $userResults = collect();
        if ($search) {
            $userResults = User::with('wallet')
                ->where('first_name', 'like', "%{$search}%")
                ->orWhere('surname', 'like', "%{$search}%")
                ->orWhere('email', 'like', "%{$search}%")
                ->orWhere('phone_number', 'like', "%{$search}%")
                ->take(10)
                ->get();
        }

        $allUsers = User::with('wallet')->orderBy('first_name')->take(100)->get();

        $recentOperations = WalletTransaction::with('user')
            ->whereIn('category', ['deposit', 'admin_debit', 'transfer_in', 'transfer_out'])
            ->latest()
            ->take(10)
            ->get();

        // Kept for backwards compatibility
        $recentFundings = $recentOperations;

        return view('admin.finance.fund', compact('selectedUser', 'search', 'userResults', 'recentFundings', 'recentOperations', 'allUsers'));
    }

    /**
     * Process wallet funding (credit or debit) for a user.
     */
    public function fundUserSubmit(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'user_id' => ['required', 'exists:users,id'],
            'action_type' => ['nullable', 'in:credit,debit'],
            'amount' => ['required', 'numeric', 'min:1', 'max:10000000'],
            'funding_type' => ['nullable', 'in:direct_credit,admin_transfer'],
            'description' => ['nullable', 'string', 'max:191'],
        ]);

        $admin = $request->user();
        $recipient = User::findOrFail($validated['user_id']);
        $amount = (float) $validated['amount'];
        $actionType = $validated['action_type'] ?? 'credit';

        // -------------------------------------------------------------
        // DEBIT USER WALLET FLOW
        // -------------------------------------------------------------
        if ($actionType === 'debit') {
            $description = $validated['description'] ?: 'Administrative Wallet Debit';

            try {
                $tx = $this->walletService->charge(
                    user: $recipient,
                    amount: $amount,
                    category: 'admin_debit',
                    description: $description,
                    metadata: [
                        'authorized_by_id' => $admin->id,
                        'authorized_by_name' => $admin->full_name,
                        'channel' => 'admin_manual_debit',
                    ]
                );

                return redirect()->route('admin.finance.fund', ['user_id' => $recipient->id])
                    ->with('success', sprintf(
                        'Successfully debited ₦%s from %s (%s). New balance: ₦%s. Reference: %s',
                        number_format($amount, 2),
                        $recipient->full_name,
                        $recipient->email,
                        number_format($recipient->wallet->fresh()->balance, 2),
                        $tx->reference
                    ));
            } catch (\App\Exceptions\InsufficientWalletBalanceException $e) {
                return back()->withErrors([
                    'amount' => sprintf(
                        'Cannot debit ₦%s: User only has ₦%s in their wallet.',
                        number_format($amount, 2),
                        number_format($recipient->wallet?->balance ?? 0, 2)
                    ),
                ])->withInput();
            }
        }

        // -------------------------------------------------------------
        // CREDIT USER WALLET FLOW
        // -------------------------------------------------------------
        $fundingType = $validated['funding_type'] ?? 'direct_credit';
        $description = $validated['description'] ?: 'Administrative Wallet Credit';

        if ($fundingType === 'admin_transfer') {
            if ($recipient->id === $admin->id) {
                return back()->withErrors(['user_id' => 'You cannot transfer funds to your own wallet.'])->withInput();
            }

            $adminWallet = $this->walletService->getOrCreateWallet($admin);
            if ((float) $adminWallet->balance < $amount) {
                return back()->withErrors([
                    'amount' => sprintf('Insufficient admin wallet balance (Available: ₦%s). Use "Direct Platform Credit" or deposit to your wallet first.', number_format($adminWallet->balance, 2)),
                ])->withInput();
            }

            $result = $this->walletService->transfer(
                sender: $admin,
                recipient: $recipient,
                amount: $amount,
                description: $description
            );

            $ref = ($result['debit_transaction'] ?? $result['debit'])->reference;
        } else {
            // Direct platform credit into user's wallet
            $tx = $this->walletService->deposit(
                user: $recipient,
                amount: $amount,
                description: $description,
                metadata: [
                    'authorized_by_id' => $admin->id,
                    'authorized_by_name' => $admin->full_name,
                    'channel' => 'admin_direct_credit',
                ]
            );

            $ref = $tx->reference;
        }

        return redirect()->route('admin.finance.fund', ['user_id' => $recipient->id])
            ->with('success', sprintf(
                'Successfully credited ₦%s to %s (%s). New balance: ₦%s. Reference: %s',
                number_format($amount, 2),
                $recipient->full_name,
                $recipient->email,
                number_format($recipient->wallet->fresh()->balance, 2),
                $ref
            ));
    }

    /**
     * Display all wallet transactions across the platform.
     */
    public function transactions(Request $request): View
    {
        $query = WalletTransaction::with(['user', 'counterpartUser'])->latest();

        if ($category = $request->query('category')) {
            if ($category !== 'all') {
                $query->where('category', $category);
            }
        }

        if ($type = $request->query('type')) {
            if (in_array($type, ['credit', 'debit'])) {
                $query->where('type', $type);
            }
        }

        if ($search = $request->query('search')) {
            $query->where(function ($q) use ($search) {
                $q->where('reference', 'like', "%{$search}%")
                  ->orWhere('description', 'like', "%{$search}%")
                  ->orWhereHas('user', function ($uq) use ($search) {
                      $uq->where('first_name', 'like', "%{$search}%")
                        ->orWhere('surname', 'like', "%{$search}%")
                        ->orWhere('email', 'like', "%{$search}%");
                  });
            });
        }

        $transactions = $query->paginate(20)->withQueryString();

        $totalPlatformBalance = (float) Wallet::sum('balance');
        $totalCredits = (float) WalletTransaction::where('type', 'credit')->sum('amount');
        $totalDebits = (float) WalletTransaction::where('type', 'debit')->sum('amount');
        $totalTransactionsCount = WalletTransaction::count();

        return view('admin.finance.transactions', compact(
            'transactions',
            'totalPlatformBalance',
            'totalCredits',
            'totalDebits',
            'totalTransactionsCount'
        ));
    }

    /**
     * Display the funding history across all users.
     */
    public function fundingHistory(Request $request): View
    {
        $query = WalletTransaction::with(['user', 'counterpartUser'])
            ->where(function ($q) {
                $q->where('category', 'deposit')
                  ->orWhere('category', 'transfer_in')
                  ->orWhere('category', 'manual_refund');
            })
            ->latest();

        if ($channel = $request->query('channel')) {
            if ($channel === 'deposit') {
                $query->where('category', 'deposit');
            } elseif ($channel === 'transfer') {
                $query->where('category', 'transfer_in');
            } elseif ($channel === 'refund') {
                $query->where('category', 'manual_refund');
            }
        }

        if ($search = $request->query('search')) {
            $query->where(function ($q) use ($search) {
                $q->where('reference', 'like', "%{$search}%")
                  ->orWhere('description', 'like', "%{$search}%")
                  ->orWhereHas('user', function ($uq) use ($search) {
                      $uq->where('first_name', 'like', "%{$search}%")
                        ->orWhere('surname', 'like', "%{$search}%")
                        ->orWhere('email', 'like', "%{$search}%");
                  });
            });
        }

        $fundingRecords = $query->paginate(20)->withQueryString();

        $totalFundedAmount = (float) WalletTransaction::whereIn('category', ['deposit', 'transfer_in', 'manual_refund'])->sum('amount');
        $totalDepositsCount = WalletTransaction::where('category', 'deposit')->count();
        $totalTransfersCount = WalletTransaction::where('category', 'transfer_in')->count();

        return view('admin.finance.history', compact(
            'fundingRecords',
            'totalFundedAmount',
            'totalDepositsCount',
            'totalTransfersCount'
        ));
    }
}
