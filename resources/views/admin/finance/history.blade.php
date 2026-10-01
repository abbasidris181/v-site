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
                    &bull; User Capital Inflow Audit
                </span>
            </div>
            <h1 class="mt-2 text-2xl sm:text-3xl font-extrabold text-slate-900 dark:text-white tracking-tight">
                Funding History
            </h1>
            <p class="text-xs sm:text-sm text-slate-500 dark:text-slate-400 mt-1">
                Historical record of all platform deposits, administrative credits, and user funding events.
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
    <div class="grid grid-cols-1 sm:grid-cols-3 gap-4">
        <div class="rounded-2xl bg-white dark:bg-slate-900 border border-slate-200/80 dark:border-slate-800 p-5 shadow-xs">
            <span class="text-xs font-semibold text-emerald-600 dark:text-emerald-400 uppercase tracking-wider block">Total Capital Injected</span>
            <div class="mt-2 text-2xl font-extrabold text-emerald-600 dark:text-emerald-400">
                ₦{{ number_format($totalFundedAmount, 2) }}
            </div>
            <span class="text-[11px] text-slate-400 mt-1 block">Deposits, admin credits & transfers</span>
        </div>

        <div class="rounded-2xl bg-white dark:bg-slate-900 border border-slate-200/80 dark:border-slate-800 p-5 shadow-xs">
            <span class="text-xs font-semibold text-slate-500 uppercase tracking-wider block">Self-Service Deposits</span>
            <div class="mt-2 text-2xl font-extrabold text-slate-900 dark:text-white">
                {{ number_format($totalDepositsCount) }}
            </div>
            <span class="text-[11px] text-slate-400 mt-1 block">Customer web top-ups</span>
        </div>

        <div class="rounded-2xl bg-white dark:bg-slate-900 border border-slate-200/80 dark:border-slate-800 p-5 shadow-xs">
            <span class="text-xs font-semibold text-indigo-600 dark:text-indigo-400 uppercase tracking-wider block">Peer / Admin Transfers</span>
            <div class="mt-2 text-2xl font-extrabold text-indigo-600 dark:text-indigo-400">
                {{ number_format($totalTransfersCount) }}
            </div>
            <span class="text-[11px] text-slate-400 mt-1 block">Internal wallet-to-wallet credits</span>
        </div>
    </div>

    <!-- Filters & Search -->
    <div class="rounded-2xl bg-white dark:bg-slate-900 border border-slate-200/80 dark:border-slate-800 p-4 shadow-xs">
        <form method="GET" action="{{ route('admin.finance.history') }}" class="flex flex-col sm:flex-row items-center justify-between gap-3">
            <div class="flex items-center gap-1.5 overflow-x-auto w-full sm:w-auto text-xs">
                @php
                    $activeChannel = request('channel', 'all');
                @endphp
                <a href="{{ route('admin.finance.history', array_merge(request()->except('channel', 'page'), ['channel' => 'all'])) }}"
                   class="px-3 py-1.5 rounded-lg font-semibold whitespace-nowrap {{ $activeChannel === 'all' ? 'bg-indigo-600 text-white shadow-xs' : 'bg-slate-100 dark:bg-slate-800 text-slate-600 dark:text-slate-300 hover:bg-slate-200' }}">
                    All Inflows
                </a>
                <a href="{{ route('admin.finance.history', array_merge(request()->except('channel', 'page'), ['channel' => 'deposit'])) }}"
                   class="px-3 py-1.5 rounded-lg font-semibold whitespace-nowrap {{ $activeChannel === 'deposit' ? 'bg-indigo-600 text-white shadow-xs' : 'bg-slate-100 dark:bg-slate-800 text-slate-600 dark:text-slate-300 hover:bg-slate-200' }}">
                    Direct Deposits
                </a>
                <a href="{{ route('admin.finance.history', array_merge(request()->except('channel', 'page'), ['channel' => 'transfer'])) }}"
                   class="px-3 py-1.5 rounded-lg font-semibold whitespace-nowrap {{ $activeChannel === 'transfer' ? 'bg-indigo-600 text-white shadow-xs' : 'bg-slate-100 dark:bg-slate-800 text-slate-600 dark:text-slate-300 hover:bg-slate-200' }}">
                    Transfers & Credits
                </a>
                <a href="{{ route('admin.finance.history', array_merge(request()->except('channel', 'page'), ['channel' => 'refund'])) }}"
                   class="px-3 py-1.5 rounded-lg font-semibold whitespace-nowrap {{ $activeChannel === 'refund' ? 'bg-indigo-600 text-white shadow-xs' : 'bg-slate-100 dark:bg-slate-800 text-slate-600 dark:text-slate-300 hover:bg-slate-200' }}">
                    Manual Refunds
                </a>
            </div>

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

    <!-- Table -->
    <div class="rounded-2xl bg-white dark:bg-slate-900 border border-slate-200/80 dark:border-slate-800 shadow-sm overflow-hidden">
        <div class="overflow-x-auto">
            <table class="min-w-full divide-y divide-slate-200 dark:divide-slate-800 text-left text-xs">
                <thead class="bg-slate-50 dark:bg-slate-950/60 text-slate-500 dark:text-slate-400 uppercase font-semibold">
                    <tr>
                        <th class="px-5 py-3.5">Reference</th>
                        <th class="px-5 py-3.5">Date & Time</th>
                        <th class="px-5 py-3.5">Recipient User</th>
                        <th class="px-5 py-3.5">Funding Channel</th>
                        <th class="px-5 py-3.5">Credit Amount</th>
                        <th class="px-5 py-3.5">Initiated By / Sender</th>
                        <th class="px-5 py-3.5">Description</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-100 dark:divide-slate-800/80">
                    @forelse($fundingRecords as $rec)
                        <tr class="hover:bg-slate-50/80 dark:hover:bg-slate-800/40 transition-colors">
                            <td class="px-5 py-4 font-semibold text-slate-900 dark:text-white whitespace-nowrap">
                                {{ $rec->reference }}
                            </td>
                            <td class="px-5 py-4 text-slate-500 dark:text-slate-400 whitespace-nowrap">
                                <div>{{ $rec->created_at->format('M d, Y') }}</div>
                                <div class="text-[10px] text-slate-400">{{ $rec->created_at->format('H:i:s') }}</div>
                            </td>
                            <td class="px-5 py-4">
                                <a href="{{ route('admin.users.show', $rec->user->id) }}" class="font-semibold text-slate-900 dark:text-white hover:text-indigo-600 transition">
                                    {{ $rec->user->full_name }}
                                </a>
                                <div class="text-[11px] text-slate-400">{{ $rec->user->email }}</div>
                            </td>
                            <td class="px-5 py-4 whitespace-nowrap">
                                @if($rec->category === 'deposit')
                                    <span class="px-2.5 py-1 rounded-full text-[10px] font-extrabold uppercase bg-emerald-50 dark:bg-emerald-500/10 text-emerald-700 dark:text-emerald-400 border border-emerald-200 dark:border-emerald-500/20">
                                        Direct Deposit
                                    </span>
                                @elseif($rec->category === 'transfer_in')
                                    <span class="px-2.5 py-1 rounded-full text-[10px] font-extrabold uppercase bg-indigo-50 dark:bg-indigo-500/10 text-indigo-700 dark:text-indigo-400 border border-indigo-200 dark:border-indigo-500/20">
                                        Wallet Transfer
                                    </span>
                                @else
                                    <span class="px-2.5 py-1 rounded-full text-[10px] font-extrabold uppercase bg-amber-50 dark:bg-amber-500/10 text-amber-700 dark:text-amber-400 border border-amber-200 dark:border-amber-500/20">
                                        {{ str_replace('_', ' ', $rec->category) }}
                                    </span>
                                @endif
                            </td>
                            <td class="px-5 py-4 font-bold text-emerald-600 dark:text-emerald-400 whitespace-nowrap">
                                +₦{{ number_format($rec->amount, 2) }}
                            </td>
                            <td class="px-5 py-4 text-slate-600 dark:text-slate-300 whitespace-nowrap">
                                @if($rec->counterpartUser)
                                    <span class="font-medium text-slate-900 dark:text-white">{{ $rec->counterpartUser->full_name }}</span>
                                    <span class="text-[10px] text-slate-400 block">{{ $rec->counterpartUser->email }}</span>
                                @elseif(!empty($rec->metadata['authorized_by_name']))
                                    <span class="font-medium text-slate-900 dark:text-white">{{ $rec->metadata['authorized_by_name'] }}</span>
                                    <span class="text-[10px] text-indigo-600 dark:text-indigo-400 block">Administrator</span>
                                @else
                                    <span class="text-slate-400">Self / Gateway</span>
                                @endif
                            </td>
                            <td class="px-5 py-4 text-slate-600 dark:text-slate-300 max-w-xs truncate">
                                {{ $rec->description }}
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="7" class="px-6 py-12 text-center text-slate-400 dark:text-slate-500">
                                No funding history records found.
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        @if($fundingRecords->hasPages())
            <div class="p-4 border-t border-slate-100 dark:border-slate-800">
                {{ $fundingRecords->links() }}
            </div>
        @endif
    </div>
</div>
@endsection
