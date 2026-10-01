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
                    &bull; Banking Verification Records
                </span>
            </div>
            <h1 class="mt-2 text-2xl sm:text-3xl font-extrabold text-slate-900 dark:text-white tracking-tight">
                BVN Verifications
            </h1>
            <p class="text-xs sm:text-sm text-slate-500 dark:text-slate-400 mt-1">
                Historical query ledger for automated Bank Verification Number confirmations and demographic matching.
            </p>
        </div>

        <div class="flex items-center gap-2">
            <a href="{{ route('admin.transactions.nin') }}"
               class="inline-flex items-center gap-2 px-3.5 py-2 rounded-xl text-xs font-semibold text-slate-700 dark:text-slate-200 bg-white dark:bg-slate-900 border border-slate-200/80 dark:border-slate-800 hover:bg-slate-50 dark:hover:bg-slate-800 transition shadow-xs">
                <span>&larr; NIN Verifications</span>
            </a>
        </div>
    </div>

    <!-- Summary Metrics -->
    <div class="grid grid-cols-1 sm:grid-cols-4 gap-4">
        <div class="rounded-2xl bg-white dark:bg-slate-900 border border-slate-200/80 dark:border-slate-800 p-5 shadow-xs">
            <span class="text-xs font-semibold text-slate-500 uppercase tracking-wider block">Total BVN Queries</span>
            <div class="mt-2 text-2xl font-extrabold text-slate-900 dark:text-white">
                {{ number_format($totalCount) }}
            </div>
            <span class="text-[11px] text-slate-400 mt-1 block">API queries processed</span>
        </div>

        <div class="rounded-2xl bg-white dark:bg-slate-900 border border-slate-200/80 dark:border-slate-800 p-5 shadow-xs">
            <span class="text-xs font-semibold text-emerald-600 dark:text-emerald-400 uppercase tracking-wider block">Verified Successfully</span>
            <div class="mt-2 text-2xl font-extrabold text-emerald-600 dark:text-emerald-400">
                {{ number_format($completedCount) }}
            </div>
            <span class="text-[11px] text-slate-400 mt-1 block">Valid matches returned</span>
        </div>

        <div class="rounded-2xl bg-white dark:bg-slate-900 border border-slate-200/80 dark:border-slate-800 p-5 shadow-xs">
            <span class="text-xs font-semibold text-red-600 dark:text-red-400 uppercase tracking-wider block">Failed / Unmatched</span>
            <div class="mt-2 text-2xl font-extrabold text-red-600 dark:text-red-400">
                {{ number_format($failedCount) }}
            </div>
            <span class="text-[11px] text-slate-400 mt-1 block">Invalid queries or mismatches</span>
        </div>

        <div class="rounded-2xl bg-white dark:bg-slate-900 border border-slate-200/80 dark:border-slate-800 p-5 shadow-xs">
            <span class="text-xs font-semibold text-indigo-600 dark:text-indigo-400 uppercase tracking-wider block">Total Billed Fees</span>
            <div class="mt-2 text-2xl font-extrabold text-indigo-600 dark:text-indigo-400">
                ₦{{ number_format($totalVolume, 2) }}
            </div>
            <span class="text-[11px] text-slate-400 mt-1 block">Wallet debits generated</span>
        </div>
    </div>

    <!-- Filters & Search -->
    <div class="rounded-2xl bg-white dark:bg-slate-900 border border-slate-200/80 dark:border-slate-800 p-4 shadow-xs">
        <form method="GET" action="{{ route('admin.transactions.bvn') }}" class="flex flex-col sm:flex-row items-center justify-between gap-3">
            <div class="flex items-center gap-1.5 overflow-x-auto w-full sm:w-auto text-xs">
                @php
                    $status = request('status', 'all');
                @endphp
                <a href="{{ route('admin.transactions.bvn', array_merge(request()->except('status', 'page'), ['status' => 'all'])) }}"
                   class="px-3 py-1.5 rounded-lg font-semibold whitespace-nowrap {{ $status === 'all' ? 'bg-indigo-600 text-white shadow-xs' : 'bg-slate-100 dark:bg-slate-800 text-slate-600 dark:text-slate-300 hover:bg-slate-200' }}">
                    All Queries
                </a>
                <a href="{{ route('admin.transactions.bvn', array_merge(request()->except('status', 'page'), ['status' => 'completed'])) }}"
                   class="px-3 py-1.5 rounded-lg font-semibold whitespace-nowrap {{ $status === 'completed' ? 'bg-indigo-600 text-white shadow-xs' : 'bg-slate-100 dark:bg-slate-800 text-slate-600 dark:text-slate-300 hover:bg-slate-200' }}">
                    Completed
                </a>
                <a href="{{ route('admin.transactions.bvn', array_merge(request()->except('status', 'page'), ['status' => 'failed'])) }}"
                   class="px-3 py-1.5 rounded-lg font-semibold whitespace-nowrap {{ $status === 'failed' ? 'bg-indigo-600 text-white shadow-xs' : 'bg-slate-100 dark:bg-slate-800 text-slate-600 dark:text-slate-300 hover:bg-slate-200' }}">
                    Failed
                </a>
            </div>

            <div class="flex items-center gap-2 w-full sm:w-auto">
                <input type="text" name="search" value="{{ request('search') }}"
                       placeholder="Search reference, BVN, user..."
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
                        <th class="px-5 py-3.5">Customer</th>
                        <th class="px-5 py-3.5">Input BVN</th>
                        <th class="px-5 py-3.5">Fee Charged</th>
                        <th class="px-5 py-3.5">Status</th>
                        <th class="px-5 py-3.5 text-right">Details</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-100 dark:divide-slate-800/80">
                    @forelse($records as $row)
                        <tr class="hover:bg-slate-50/80 dark:hover:bg-slate-800/40 transition-colors">
                            <td class="px-5 py-4 font-semibold text-slate-900 dark:text-white whitespace-nowrap">
                                #{{ $row->reference }}
                            </td>
                            <td class="px-5 py-4 text-slate-500 dark:text-slate-400 whitespace-nowrap">
                                <div>{{ $row->created_at->format('M d, Y') }}</div>
                                <div class="text-[10px] text-slate-400">{{ $row->created_at->format('H:i:s') }}</div>
                            </td>
                            <td class="px-5 py-4">
                                <div class="font-semibold text-slate-900 dark:text-white">{{ $row->user->full_name }}</div>
                                <div class="text-[11px] text-slate-400">{{ $row->user->email }}</div>
                            </td>
                            <td class="px-5 py-4 text-slate-800 dark:text-slate-200">
                                {{ $row->tracking_input }}
                            </td>
                            <td class="px-5 py-4 font-bold text-slate-900 dark:text-white">
                                ₦{{ number_format($row->amount_charged, 2) }}
                            </td>
                            <td class="px-5 py-4 whitespace-nowrap">
                                @if($row->status === 'completed')
                                    <span class="inline-flex items-center px-2 py-0.5 rounded-full text-[11px] font-bold bg-emerald-50 dark:bg-emerald-500/10 text-emerald-700 dark:text-emerald-400 border border-emerald-200 dark:border-emerald-500/20">
                                        Completed
                                    </span>
                                @elseif($row->status === 'failed')
                                    <span class="inline-flex items-center px-2 py-0.5 rounded-full text-[11px] font-bold bg-red-50 dark:bg-red-500/10 text-red-700 dark:text-red-400 border border-red-200 dark:border-red-500/20">
                                        Failed
                                    </span>
                                @else
                                    <span class="inline-flex items-center px-2 py-0.5 rounded-full text-[11px] font-bold bg-amber-50 dark:bg-amber-500/10 text-amber-700 dark:text-amber-400 border border-amber-200 dark:border-amber-500/20">
                                        {{ ucfirst($row->status) }}
                                    </span>
                                @endif
                            </td>
                            <td class="px-5 py-4 text-right whitespace-nowrap">
                                <button type="button"
                                        onclick="alert('BVN Verification Reference: #{{ $row->reference }}\nTarget BVN: {{ $row->tracking_input }}\nFee: ₦{{ number_format($row->amount_charged, 2) }}\nStatus: {{ strtoupper($row->status) }}')"
                                        class="px-2.5 py-1 rounded-lg bg-slate-100 hover:bg-slate-200 dark:bg-slate-800 text-slate-700 dark:text-slate-300 font-semibold text-xs transition">
                                    Inspect
                                </button>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="7" class="px-6 py-12 text-center text-slate-400 dark:text-slate-500">
                                No BVN verification records found.
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        @if($records->hasPages())
            <div class="p-4 border-t border-slate-100 dark:border-slate-800">
                {{ $records->links() }}
            </div>
        @endif
    </div>
</div>
@endsection
