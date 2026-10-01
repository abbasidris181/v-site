@extends('layouts.admin')

@section('content')
<div class="space-y-6">
    <!-- Header -->
    <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-4">
        <div>
            <div class="flex items-center gap-2">
                <span class="text-xs font-bold uppercase tracking-widest text-indigo-700 dark:text-indigo-400 bg-indigo-50 dark:bg-indigo-500/10 px-3 py-1 rounded-full border border-indigo-200 dark:border-indigo-500/30">
                    Wallet & Finance
                </span>
                <span class="text-xs font-medium text-slate-500 dark:text-slate-400">
                    &bull; Master Ledger Audit
                </span>
            </div>
            <h1 class="mt-2 text-2xl sm:text-3xl font-extrabold text-slate-900 dark:text-white tracking-tight">
                Wallet Transactions Ledger
            </h1>
            <p class="text-xs sm:text-sm text-slate-500 dark:text-slate-400 mt-1">
                Platform-wide double-entry financial ledger recording all credits, debits, service deductions, and transfers.
            </p>
        </div>

        <div>
            <a href="{{ route('admin.finance.fund') }}"
               class="inline-flex items-center gap-2 px-4 py-2.5 rounded-xl bg-indigo-600 hover:bg-indigo-500 text-white text-xs font-bold shadow-md shadow-indigo-600/20 transition cursor-pointer">
                <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4" />
                </svg>
                <span>Fund User Wallet</span>
            </a>
        </div>
    </div>

    <!-- Platform Financial Metrics -->
    <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-4">
        <div class="rounded-2xl bg-white dark:bg-slate-900 border border-slate-200/80 dark:border-slate-800 p-5 shadow-xs">
            <span class="text-xs font-semibold text-slate-500 uppercase tracking-wider block">Total Platform Reserve</span>
            <div class="mt-2 text-2xl font-extrabold text-slate-900 dark:text-white">
                ₦{{ number_format($totalPlatformBalance, 2) }}
            </div>
            <span class="text-[11px] text-slate-400 mt-1 block">Sum of all active wallet balances</span>
        </div>

        <div class="rounded-2xl bg-white dark:bg-slate-900 border border-slate-200/80 dark:border-slate-800 p-5 shadow-xs">
            <span class="text-xs font-semibold text-emerald-600 dark:text-emerald-400 uppercase tracking-wider block">Total Inflow Volume</span>
            <div class="mt-2 text-2xl font-extrabold text-emerald-600 dark:text-emerald-400">
                +₦{{ number_format($totalCredits, 2) }}
            </div>
            <span class="text-[11px] text-slate-400 mt-1 block">Total credits & deposits</span>
        </div>

        <div class="rounded-2xl bg-white dark:bg-slate-900 border border-slate-200/80 dark:border-slate-800 p-5 shadow-xs">
            <span class="text-xs font-semibold text-red-600 dark:text-red-400 uppercase tracking-wider block">Total Outflow Volume</span>
            <div class="mt-2 text-2xl font-extrabold text-red-600 dark:text-red-400">
                -₦{{ number_format($totalDebits, 2) }}
            </div>
            <span class="text-[11px] text-slate-400 mt-1 block">Total service debits & charges</span>
        </div>

        <div class="rounded-2xl bg-white dark:bg-slate-900 border border-slate-200/80 dark:border-slate-800 p-5 shadow-xs">
            <span class="text-xs font-semibold text-indigo-600 dark:text-indigo-400 uppercase tracking-wider block">Total Ledger Entries</span>
            <div class="mt-2 text-2xl font-extrabold text-indigo-600 dark:text-indigo-400">
                {{ number_format($totalTransactionsCount) }}
            </div>
            <span class="text-[11px] text-slate-400 mt-1 block">Immutable double-entry records</span>
        </div>
    </div>

    <!-- Filters & Search -->
    <div class="rounded-2xl bg-white dark:bg-slate-900 border border-slate-200/80 dark:border-slate-800 p-4 shadow-xs">
        <form method="GET" action="{{ route('admin.finance.transactions') }}" class="flex flex-col sm:flex-row items-center justify-between gap-3">
            <!-- Category Tabs -->
            <div class="flex items-center gap-1.5 overflow-x-auto w-full sm:w-auto text-xs">
                @php
                    $activeCat = request('category', 'all');
                @endphp
                <a href="{{ route('admin.finance.transactions', array_merge(request()->except('category', 'page'), ['category' => 'all'])) }}"
                   class="px-3 py-1.5 rounded-lg font-semibold whitespace-nowrap {{ $activeCat === 'all' ? 'bg-indigo-600 text-white shadow-xs' : 'bg-slate-100 dark:bg-slate-800 text-slate-600 dark:text-slate-300 hover:bg-slate-200' }}">
                    All Categories
                </a>
                <a href="{{ route('admin.finance.transactions', array_merge(request()->except('category', 'page'), ['category' => 'deposit'])) }}"
                   class="px-3 py-1.5 rounded-lg font-semibold whitespace-nowrap {{ $activeCat === 'deposit' ? 'bg-indigo-600 text-white shadow-xs' : 'bg-slate-100 dark:bg-slate-800 text-slate-600 dark:text-slate-300 hover:bg-slate-200' }}">
                    Deposits
                </a>
                <a href="{{ route('admin.finance.transactions', array_merge(request()->except('category', 'page'), ['category' => 'service_charge'])) }}"
                   class="px-3 py-1.5 rounded-lg font-semibold whitespace-nowrap {{ $activeCat === 'service_charge' ? 'bg-indigo-600 text-white shadow-xs' : 'bg-slate-100 dark:bg-slate-800 text-slate-600 dark:text-slate-300 hover:bg-slate-200' }}">
                    Service Charges
                </a>
                <a href="{{ route('admin.finance.transactions', array_merge(request()->except('category', 'page'), ['category' => 'transfer_in'])) }}"
                   class="px-3 py-1.5 rounded-lg font-semibold whitespace-nowrap {{ $activeCat === 'transfer_in' ? 'bg-indigo-600 text-white shadow-xs' : 'bg-slate-100 dark:bg-slate-800 text-slate-600 dark:text-slate-300 hover:bg-slate-200' }}">
                    Transfers
                </a>
                <a href="{{ route('admin.finance.transactions', array_merge(request()->except('category', 'page'), ['category' => 'manual_refund'])) }}"
                   class="px-3 py-1.5 rounded-lg font-semibold whitespace-nowrap {{ $activeCat === 'manual_refund' ? 'bg-indigo-600 text-white shadow-xs' : 'bg-slate-100 dark:bg-slate-800 text-slate-600 dark:text-slate-300 hover:bg-slate-200' }}">
                    Refunds
                </a>
            </div>

            <!-- Search input -->
            <div class="flex items-center gap-2 w-full sm:w-auto">
                <input type="text" name="search" value="{{ request('search') }}"
                       placeholder="Search reference, email, user..."
                       class="w-full sm:w-64 px-3.5 py-1.5 text-xs rounded-xl bg-slate-50 dark:bg-slate-950 border border-slate-300 dark:border-slate-700 text-slate-900 dark:text-white">
                <button type="submit"
                        class="px-3.5 py-1.5 rounded-xl bg-slate-100 hover:bg-slate-200 dark:bg-slate-800 text-xs font-bold text-slate-800 dark:text-white transition">
                    Filter
                </button>
            </div>
        </form>
    </div>

    <!-- Transactions Table -->
    <div class="rounded-2xl bg-white dark:bg-slate-900 border border-slate-200/80 dark:border-slate-800 shadow-sm overflow-hidden">
        <div class="overflow-x-auto">
            <table class="min-w-full divide-y divide-slate-200 dark:divide-slate-800 text-left text-xs">
                <thead class="bg-slate-50 dark:bg-slate-950/60 text-slate-500 dark:text-slate-400 uppercase font-semibold">
                    <tr>
                        <th class="px-5 py-3.5">Reference</th>
                        <th class="px-5 py-3.5">Date & Time</th>
                        <th class="px-5 py-3.5">User</th>
                        <th class="px-5 py-3.5">Category</th>
                        <th class="px-5 py-3.5">Type</th>
                        <th class="px-5 py-3.5">Amount</th>
                        <th class="px-5 py-3.5">Balance Trajectory</th>
                        <th class="px-5 py-3.5">Description</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-100 dark:divide-slate-800/80">
                    @forelse($transactions as $tx)
                        <tr class="hover:bg-slate-50/80 dark:hover:bg-slate-800/40 transition-colors">
                            <td class="px-5 py-4 font-semibold text-slate-900 dark:text-white whitespace-nowrap">
                                {{ $tx->reference }}
                            </td>
                            <td class="px-5 py-4 text-slate-500 dark:text-slate-400 whitespace-nowrap">
                                <div>{{ $tx->created_at->format('M d, Y') }}</div>
                                <div class="text-[10px] text-slate-400">{{ $tx->created_at->format('H:i:s') }}</div>
                            </td>
                            <td class="px-5 py-4">
                                <a href="{{ route('admin.users.show', $tx->user->id) }}" class="font-semibold text-slate-900 dark:text-white hover:text-indigo-600 transition">
                                    {{ $tx->user->full_name }}
                                </a>
                                <div class="text-[11px] text-slate-400">{{ $tx->user->email }}</div>
                            </td>
                            <td class="px-5 py-4 whitespace-nowrap">
                                <span class="px-2 py-0.5 rounded-md text-[10px] font-bold uppercase tracking-wider bg-slate-100 dark:bg-slate-800 text-slate-700 dark:text-slate-300">
                                    {{ str_replace('_', ' ', $tx->category) }}
                                </span>
                            </td>
                            <td class="px-5 py-4 whitespace-nowrap">
                                @if($tx->type === 'credit')
                                    <span class="inline-flex items-center px-2 py-0.5 rounded-full text-[11px] font-bold bg-emerald-50 dark:bg-emerald-500/10 text-emerald-700 dark:text-emerald-400 border border-emerald-200 dark:border-emerald-500/20">
                                        + CREDIT
                                    </span>
                                @else
                                    <span class="inline-flex items-center px-2 py-0.5 rounded-full text-[11px] font-bold bg-red-50 dark:bg-red-500/10 text-red-700 dark:text-red-400 border border-red-200 dark:border-red-500/20">
                                        - DEBIT
                                    </span>
                                @endif
                            </td>
                            <td class="px-5 py-4 font-bold whitespace-nowrap {{ $tx->type === 'credit' ? 'text-emerald-600 dark:text-emerald-400' : 'text-slate-900 dark:text-white' }}">
                                {{ $tx->type === 'credit' ? '+' : '-' }}₦{{ number_format($tx->amount, 2) }}
                            </td>
                            <td class="px-5 py-4 text-[11px] text-slate-500 whitespace-nowrap">
                                <span>₦{{ number_format($tx->balance_before, 2) }}</span>
                                <span class="text-slate-400">&rarr;</span>
                                <span class="font-bold text-slate-800 dark:text-slate-200">₦{{ number_format($tx->balance_after, 2) }}</span>
                            </td>
                            <td class="px-5 py-4 text-slate-600 dark:text-slate-300 max-w-xs truncate">
                                {{ $tx->description }}
                                @if($tx->counterpartUser)
                                    <span class="text-[10px] text-indigo-600 dark:text-indigo-400 block font-sans">
                                        Counterpart: {{ $tx->counterpartUser->full_name }}
                                    </span>
                                @endif
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="8" class="px-6 py-12 text-center text-slate-400 dark:text-slate-500">
                                No wallet transactions found matching criteria.
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        @if($transactions->hasPages())
            <div class="p-4 border-t border-slate-100 dark:border-slate-800">
                {{ $transactions->links() }}
            </div>
        @endif
    </div>
</div>
@endsection
