@extends('layouts.admin')

@section('content')
<div class="space-y-6">
    <!-- Header -->
    <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-4">
        <div>
            <div class="flex items-center gap-2">
                <span class="text-xs font-bold uppercase tracking-widest text-indigo-700 dark:text-indigo-400 bg-indigo-50 dark:bg-indigo-500/10 px-3 py-1 rounded-full border border-indigo-200 dark:border-indigo-500/30">
                    Transactions Audit
                </span>
                <span class="text-xs font-medium text-slate-500 dark:text-slate-400">
                    &bull; Liquidity Top-ups
                </span>
            </div>
            <h1 class="mt-2 text-2xl sm:text-3xl font-extrabold text-slate-900 dark:text-white tracking-tight">
                Deposit History
            </h1>
            <p class="text-xs sm:text-sm text-slate-500 dark:text-slate-400 mt-1">
                Audit record of all inbound deposits credited to user wallets across the platform.
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

    <!-- Metrics -->
    <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
        <div class="rounded-2xl bg-white dark:bg-slate-900 border border-slate-200/80 dark:border-slate-800 p-5 shadow-xs">
            <span class="text-xs font-semibold text-emerald-600 dark:text-emerald-400 uppercase tracking-wider block">Total Deposits Volume</span>
            <div class="mt-2 text-2xl font-extrabold text-emerald-600 dark:text-emerald-400">
                ₦{{ number_format($totalDepositsAmount, 2) }}
            </div>
            <span class="text-[11px] text-slate-400 mt-1 block">Inbound capital deposited</span>
        </div>

        <div class="rounded-2xl bg-white dark:bg-slate-900 border border-slate-200/80 dark:border-slate-800 p-5 shadow-xs">
            <span class="text-xs font-semibold text-indigo-600 dark:text-indigo-400 uppercase tracking-wider block">Total Deposit Transactions</span>
            <div class="mt-2 text-2xl font-extrabold text-indigo-600 dark:text-indigo-400">
                {{ number_format($totalDepositsCount) }}
            </div>
            <span class="text-[11px] text-slate-400 mt-1 block">Completed deposit operations</span>
        </div>
    </div>

    <!-- Search -->
    <div class="rounded-2xl bg-white dark:bg-slate-900 border border-slate-200/80 dark:border-slate-800 p-4 shadow-xs">
        <form method="GET" action="{{ route('admin.transactions.deposits') }}" class="flex items-center justify-between gap-3">
            <div class="text-xs font-bold text-slate-700 dark:text-slate-300">
                <span>Filter Transactions</span>
            </div>
            <div class="flex items-center gap-2">
                <input type="text" name="search" value="{{ request('search') }}"
                       placeholder="Search reference, email, description..."
                       class="w-64 px-3.5 py-1.5 text-xs rounded-xl bg-slate-50 dark:bg-slate-950 border border-slate-300 dark:border-slate-700 text-slate-900 dark:text-white">
                <button type="submit"
                        class="px-3.5 py-1.5 rounded-xl bg-slate-100 hover:bg-slate-200 dark:bg-slate-800 text-xs font-bold text-slate-800 dark:text-white transition">
                    Filter
                </button>
            </div>
        </form>
    </div>

    <!-- Table -->
    <div class="rounded-2xl bg-white dark:bg-slate-900 border border-slate-200/80 dark:border-slate-800 shadow-sm overflow-hidden">
        <div class="overflow-x-auto">
            <table class="min-w-full divide-y divide-slate-200 dark:divide-slate-800 text-left text-xs">
                <thead class="bg-slate-50 dark:bg-slate-950/60 text-slate-500 dark:text-slate-400 uppercase font-semibold">
                    <tr>
                        <th class="px-5 py-3.5">Reference</th>
                        <th class="px-5 py-3.5">Date & Time</th>
                        <th class="px-5 py-3.5">Customer</th>
                        <th class="px-5 py-3.5">Deposit Amount</th>
                        <th class="px-5 py-3.5">Wallet Balance Impact</th>
                        <th class="px-5 py-3.5">Status</th>
                        <th class="px-5 py-3.5">Description</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-100 dark:divide-slate-800/80">
                    @forelse($deposits as $dep)
                        <tr class="hover:bg-slate-50/80 dark:hover:bg-slate-800/40 transition-colors">
                            <td class="px-5 py-4 font-semibold text-slate-900 dark:text-white whitespace-nowrap">
                                {{ $dep->reference }}
                            </td>
                            <td class="px-5 py-4 text-slate-500 dark:text-slate-400 whitespace-nowrap">
                                <div>{{ $dep->created_at->format('M d, Y') }}</div>
                                <div class="text-[10px] text-slate-400">{{ $dep->created_at->format('H:i:s') }}</div>
                            </td>
                            <td class="px-5 py-4">
                                <a href="{{ route('admin.users.show', $dep->user->id) }}" class="font-semibold text-slate-900 dark:text-white hover:text-indigo-600 transition">
                                    {{ $dep->user->full_name }}
                                </a>
                                <div class="text-[11px] text-slate-400">{{ $dep->user->email }}</div>
                            </td>
                            <td class="px-5 py-4 font-bold text-emerald-600 dark:text-emerald-400 whitespace-nowrap">
                                +₦{{ number_format($dep->amount, 2) }}
                            </td>
                            <td class="px-5 py-4 text-[11px] text-slate-500 whitespace-nowrap">
                                <span>₦{{ number_format($dep->balance_before, 2) }}</span>
                                <span class="text-slate-400">&rarr;</span>
                                <span class="font-bold text-slate-800 dark:text-slate-200">₦{{ number_format($dep->balance_after, 2) }}</span>
                            </td>
                            <td class="px-5 py-4 whitespace-nowrap">
                                <span class="inline-flex items-center px-2 py-0.5 rounded-full text-[11px] font-bold bg-emerald-50 dark:bg-emerald-500/10 text-emerald-700 dark:text-emerald-400 border border-emerald-200 dark:border-emerald-500/20">
                                    {{ ucfirst($dep->status) }}
                                </span>
                            </td>
                            <td class="px-5 py-4 text-slate-600 dark:text-slate-300 max-w-xs truncate">
                                {{ $dep->description }}
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="7" class="px-6 py-12 text-center text-slate-400 dark:text-slate-500">
                                No deposit transactions found.
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        @if($deposits->hasPages())
            <div class="p-4 border-t border-slate-100 dark:border-slate-800">
                {{ $deposits->links() }}
            </div>
        @endif
    </div>
</div>
@endsection
