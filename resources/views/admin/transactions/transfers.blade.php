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
                    &bull; Internal Capital Flow
                </span>
            </div>
            <h1 class="mt-2 text-2xl sm:text-3xl font-extrabold text-slate-900 dark:text-white tracking-tight">
                Wallet-to-Wallet Transactions
            </h1>
            <p class="text-xs sm:text-sm text-slate-500 dark:text-slate-400 mt-1">
                Audit ledger of all peer-to-peer and agent-to-user capital transfers executed across the platform.
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
            <span class="text-xs font-semibold text-indigo-600 dark:text-indigo-400 uppercase tracking-wider block">Total Transfer Volume</span>
            <div class="mt-2 text-2xl font-extrabold text-indigo-600 dark:text-indigo-400">
                ₦{{ number_format($totalTransfersVolume, 2) }}
            </div>
            <span class="text-[11px] text-slate-400 mt-1 block">Circulating peer capital</span>
        </div>

        <div class="rounded-2xl bg-white dark:bg-slate-900 border border-slate-200/80 dark:border-slate-800 p-5 shadow-xs">
            <span class="text-xs font-semibold text-slate-500 uppercase tracking-wider block">Total Peer Transfers</span>
            <div class="mt-2 text-2xl font-extrabold text-slate-900 dark:text-white">
                {{ number_format($totalTransfersCount) }}
            </div>
            <span class="text-[11px] text-slate-400 mt-1 block">Completed transfer events</span>
        </div>
    </div>

    <!-- Search -->
    <div class="rounded-2xl bg-white dark:bg-slate-900 border border-slate-200/80 dark:border-slate-800 p-4 shadow-xs">
        <form method="GET" action="{{ route('admin.transactions.transfers') }}" class="flex items-center justify-between gap-3">
            <div class="text-xs font-bold text-slate-700 dark:text-slate-300">
                <span>Filter Transfers</span>
            </div>
            <div class="flex items-center gap-2">
                <input type="text" name="search" value="{{ request('search') }}"
                       placeholder="Search reference, sender, recipient..."
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
                        <th class="px-5 py-3.5">Sender</th>
                        <th class="px-5 py-3.5">Recipient</th>
                        <th class="px-5 py-3.5">Transfer Amount</th>
                        <th class="px-5 py-3.5">Status</th>
                        <th class="px-5 py-3.5">Memo</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-100 dark:divide-slate-800/80">
                    @forelse($transfers as $tx)
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
                            <td class="px-5 py-4">
                                @if($tx->counterpartUser)
                                    <a href="{{ route('admin.users.show', $tx->counterpartUser->id) }}" class="font-semibold text-indigo-600 dark:text-indigo-400 hover:underline">
                                        {{ $tx->counterpartUser->full_name }}
                                    </a>
                                    <div class="text-[11px] text-slate-400">{{ $tx->counterpartUser->email }}</div>
                                @else
                                    <span class="text-slate-400">—</span>
                                @endif
                            </td>
                            <td class="px-5 py-4 font-bold text-indigo-600 dark:text-indigo-400 whitespace-nowrap">
                                ₦{{ number_format($tx->amount, 2) }}
                            </td>
                            <td class="px-5 py-4 whitespace-nowrap">
                                <span class="inline-flex items-center px-2 py-0.5 rounded-full text-[11px] font-bold bg-emerald-50 dark:bg-emerald-500/10 text-emerald-700 dark:text-emerald-400 border border-emerald-200 dark:border-emerald-500/20">
                                    Completed
                                </span>
                            </td>
                            <td class="px-5 py-4 text-slate-600 dark:text-slate-300 max-w-xs truncate">
                                {{ $tx->description }}
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="7" class="px-6 py-12 text-center text-slate-400 dark:text-slate-500">
                                No wallet-to-wallet transfers recorded.
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        @if($transfers->hasPages())
            <div class="p-4 border-t border-slate-100 dark:border-slate-800">
                {{ $transfers->links() }}
            </div>
        @endif
    </div>
</div>
@endsection
