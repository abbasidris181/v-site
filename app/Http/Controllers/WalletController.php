<?php

namespace App\Http\Controllers;

use App\Exceptions\InsufficientWalletBalanceException;
use App\Exceptions\UnauthorizedWalletTransferException;
use App\Models\User;
use App\Services\WalletService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class WalletController extends Controller
{
    public function __construct(
        protected WalletService $walletService
    ) {}

    /**
     * Display the dedicated "Fund Your Wallet" page.
     */
    public function index(Request $request): View
    {
        $user = $request->user();
        $wallet = $this->walletService->getOrCreateWallet($user);
        $virtualAccounts = $user->getMonnifyVirtualAccounts();

        return view('wallet.index', [
            'user' => $user,
            'wallet' => $wallet,
            'virtualAccounts' => $virtualAccounts,
        ]);
    }

    /**
     * Display the user's complete transactions ledger history.
     */
    public function transactions(Request $request): View
    {
        $user = $request->user();
        $wallet = $this->walletService->getOrCreateWallet($user);

        $query = $user->walletTransactions()
            ->with('counterpartUser')
            ->latest();

        if ($request->filled('category') && $request->query('category') !== 'all') {
            $query->where('category', $request->query('category'));
        }

        if ($request->filled('search')) {
            $search = trim($request->query('search'));
            $query->where(function ($q) use ($search) {
                $q->where('reference', 'like', "%{$search}%")
                  ->orWhere('description', 'like', "%{$search}%");
            });
        }

        $transactions = $query->paginate(15)->withQueryString();

        $totalInflow = (float) $user->walletTransactions()->where('type', 'credit')->sum('amount');
        $totalOutflow = (float) $user->walletTransactions()->where('type', 'debit')->sum('amount');

        return view('wallet.transactions', [
            'user' => $user,
            'wallet' => $wallet,
            'transactions' => $transactions,
            'totalInflow' => $totalInflow,
            'totalOutflow' => $totalOutflow,
        ]);
    }

    /**
     * Display the dedicated "Send Fund by Email or Phone" page.
     */
    public function sendFundPage(Request $request): View
    {
        $user = $request->user();
        $wallet = $this->walletService->getOrCreateWallet($user);

        $recentTransfers = $user->walletTransactions()
            ->with('counterpartUser')
            ->where('category', 'transfer_out')
            ->latest()
            ->take(5)
            ->get();

        return view('wallet.send-fund', [
            'user' => $user,
            'wallet' => $wallet,
            'recentTransfers' => $recentTransfers,
        ]);
    }

    /**
     * Handle self-funding / deposit into user's wallet (simulator & gateway entry point).
     */
    public function deposit(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'amount' => ['required', 'numeric', 'min:100', 'max:5000000'],
            'idempotency_key' => ['nullable', 'string', 'max:100'],
        ]);

        $user = $request->user();
        $amount = (float) $validated['amount'];

        $tx = $this->walletService->deposit(
            user: $user,
            amount: $amount,
            description: 'Direct Wallet Funding',
            idempotencyKey: $validated['idempotency_key'] ?? null,
            metadata: ['channel' => 'web_portal', 'ip' => $request->ip()]
        );

        return back()->with('success', sprintf(
            'Wallet successfully credited with ₦%s! Transaction Ref: %s',
            number_format($amount, 2),
            $tx->reference
        ));
    }

    /**
     * Transfer funds from the logged-in user's wallet to another registered user.
     * Accessible to Admin, Staff (if permitted), and Agents (SRS Sections 5, 6, 7).
     */
    public function transfer(Request $request): RedirectResponse
    {
        $user = $request->user();

        if (! $user->canFundOtherUsers()) {
            abort(403, 'You do not have permission to fund other users from your wallet.');
        }

        $validated = $request->validate([
            'recipient' => ['required', 'string'], // Email or Phone Number
            'amount' => ['required', 'numeric', 'min:50', 'max:2000000'],
            'description' => ['nullable', 'string', 'max:191'],
        ]);

        $identifier = trim($validated['recipient']);
        $amount = (float) $validated['amount'];

        $recipient = User::where('email', $identifier)
            ->orWhere('phone_number', $identifier)
            ->first();

        if (! $recipient) {
            return back()->withErrors([
                'recipient' => 'No user found with the provided email or phone number.',
            ])->withInput();
        }

        if ($recipient->id === $user->id) {
            return back()->withErrors([
                'recipient' => 'You cannot transfer funds to yourself.',
            ])->withInput();
        }

        try {
            $result = $this->walletService->transfer(
                sender: $user,
                recipient: $recipient,
                amount: $amount,
                description: $validated['description'] ?? null
            );

            return back()->with('success', sprintf(
                'Successfully sent ₦%s to %s (%s). Reference: %s',
                number_format($amount, 2),
                $recipient->full_name,
                $recipient->email,
                $result['debit_transaction']->reference
            ));
        } catch (InsufficientWalletBalanceException $e) {
            return back()->withErrors(['amount' => $e->getMessage()])->withInput();
        } catch (UnauthorizedWalletTransferException $e) {
            return back()->withErrors(['amount' => $e->getMessage()])->withInput();
        }
    }
}
