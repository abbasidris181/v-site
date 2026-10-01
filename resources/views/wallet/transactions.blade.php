@extends('layouts.app')

@section('title', 'Transactions Ledger')

@section('content')
<div class="space-y-8">

    <!-- ==========================================
         1. PAGE HEADER
         ========================================== -->
    <div>
        <h1 class="text-2xl sm:text-3xl font-extrabold text-slate-900 dark:text-white tracking-tight">
            Transactions
        </h1>
        <p class="mt-1 text-sm text-slate-500 dark:text-slate-400">
            transactions history
        </p>
    </div>

    <!-- ==========================================
         2. TRANSACTION LEDGER TABLE & FILTERS
         ========================================== -->
    <div class="rounded-3xl bg-white dark:bg-slate-900 border border-slate-200/80 dark:border-slate-800 shadow-sm dark:shadow-xl overflow-hidden transition-colors">
        
        <!-- Table Header & Controls Bar -->
        <div class="p-5 sm:p-6 border-b border-slate-100 dark:border-slate-800">
            <!-- Filter & Search Toolbar -->
            <form method="GET" action="{{ route('wallet.transactions') }}" class="flex flex-wrap items-center gap-2.5">
                <!-- Search Input -->
                <div class="relative flex-1 min-w-[220px]">
                    <div class="pointer-events-none absolute inset-y-0 left-0 flex items-center pl-3">
                        <svg class="w-4 h-4 text-slate-400" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z" />
                        </svg>
                    </div>
                    <input type="text"
                           name="search"
                           value="{{ request('search') }}"
                           placeholder="Search reference or description..."
                           class="w-full pl-9 pr-3 py-2 rounded-xl text-xs border border-slate-300 dark:border-slate-700 bg-slate-50 dark:bg-slate-800 text-slate-900 dark:text-white placeholder-slate-400 focus:border-emerald-500 focus:ring-2 focus:ring-emerald-500/20 transition">
                </div>

                <!-- Category Filter Select -->
                <div class="w-full sm:w-48">
                    <select name="category" onchange="this.form.submit()"
                            class="w-full px-3 py-2 rounded-xl text-xs font-medium border border-slate-300 dark:border-slate-700 bg-slate-50 dark:bg-slate-800 text-slate-700 dark:text-slate-200 focus:border-emerald-500 focus:ring-2 focus:ring-emerald-500/20 transition cursor-pointer">
                        <option value="all" {{ request('category') === 'all' || !request('category') ? 'selected' : '' }}>All Categories</option>
                        <option value="deposit" {{ request('category') === 'deposit' ? 'selected' : '' }}>Deposits (Credits)</option>
                        <option value="service_charge" {{ request('category') === 'service_charge' ? 'selected' : '' }}>Service Charges</option>
                        <option value="transfer_in" {{ request('category') === 'transfer_in' ? 'selected' : '' }}>Transfers Received</option>
                        <option value="transfer_out" {{ request('category') === 'transfer_out' ? 'selected' : '' }}>Transfers Sent</option>
                        <option value="manual_refund" {{ request('category') === 'manual_refund' ? 'selected' : '' }}>Refunds</option>
                    </select>
                </div>

                <button type="submit"
                        class="px-4 py-2 rounded-xl text-xs font-bold text-white bg-slate-800 hover:bg-slate-700 dark:bg-slate-700 dark:hover:bg-slate-600 transition cursor-pointer">
                    Filter
                </button>

                @if(request()->hasAny(['search', 'category']) && (request('search') || (request('category') && request('category') !== 'all')))
                    <a href="{{ route('wallet.transactions') }}"
                       class="px-3 py-2 rounded-xl text-xs font-semibold text-slate-500 hover:text-slate-800 dark:text-slate-400 dark:hover:text-white transition">
                        Reset
                    </a>
                @endif
            </form>
        </div>

        <!-- Table View -->
        <div class="overflow-x-auto">
            <table class="w-full text-left text-xs text-slate-600 dark:text-slate-300">
                <thead class="bg-slate-50/80 dark:bg-slate-950/60 text-slate-500 dark:text-slate-400 uppercase tracking-wider text-[10px] border-b border-slate-200/80 dark:border-slate-800">
                    <tr>
                        <th class="py-3.5 px-4 font-bold">Reference & Timestamp</th>
                        <th class="py-3.5 px-4 font-bold text-center">Category</th>
                        <th class="py-3.5 px-4 font-bold">Transaction Details</th>
                        <th class="py-3.5 px-4 font-bold text-right">Amount</th>
                        <th class="py-3.5 px-4 font-bold text-right">Balance After</th>
                        <th class="py-3.5 px-4 font-bold text-center">Status</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-100 dark:divide-slate-800/60 font-medium">
                    @forelse ($transactions as $tx)
                        <tr class="hover:bg-slate-50/70 dark:hover:bg-slate-800/40 transition-colors">
                            
                            <!-- Reference & Date -->
                            <td class="py-4 px-4 whitespace-nowrap">
                                <div class="font-bold text-slate-900 dark:text-white text-xs">
                                    {{ $tx->reference }}
                                </div>
                                <div class="text-[11px] text-slate-400 dark:text-slate-500 mt-0.5">
                                    {{ $tx->created_at->format('M d, Y &bull; h:i A') }}
                                </div>
                            </td>

                            <!-- Category Badge -->
                            <td class="py-4 px-4 text-center whitespace-nowrap">
                                @if($tx->category === 'deposit')
                                    <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-[10px] font-bold uppercase tracking-wider bg-emerald-50 dark:bg-emerald-500/10 text-emerald-700 dark:text-emerald-300 border border-emerald-200 dark:border-emerald-500/30">
                                        Deposit
                                    </span>
                                @elseif($tx->category === 'transfer_in')
                                    <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-[10px] font-bold uppercase tracking-wider bg-teal-50 dark:bg-teal-500/10 text-teal-700 dark:text-teal-300 border border-teal-200 dark:border-teal-500/30">
                                        Transfer In
                                    </span>
                                @elseif($tx->category === 'transfer_out')
                                    <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-[10px] font-bold uppercase tracking-wider bg-indigo-50 dark:bg-indigo-500/10 text-indigo-700 dark:text-indigo-300 border border-indigo-200 dark:border-indigo-500/30">
                                        Transfer Out
                                    </span>
                                @elseif($tx->category === 'service_charge')
                                    <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-[10px] font-bold uppercase tracking-wider bg-amber-50 dark:bg-amber-500/10 text-amber-700 dark:text-amber-400 border border-amber-200 dark:border-amber-500/30">
                                        Service Charge
                                    </span>
                                @elseif($tx->category === 'manual_refund')
                                    <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-[10px] font-bold uppercase tracking-wider bg-blue-50 dark:bg-blue-500/10 text-blue-700 dark:text-blue-400 border border-blue-200 dark:border-blue-500/30">
                                        Refund
                                    </span>
                                @else
                                    <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-[10px] font-bold uppercase tracking-wider bg-slate-100 dark:bg-slate-800 text-slate-700 dark:text-slate-300">
                                        {{ ucfirst(str_replace('_', ' ', $tx->category)) }}
                                    </span>
                                @endif
                            </td>

                            <!-- Description & Counterpart User -->
                            <td class="py-4 px-4 max-w-sm">
                                <div class="text-slate-900 dark:text-slate-100 font-semibold text-xs">
                                    {{ $tx->description }}
                                </div>
                                @if($tx->counterpartUser)
                                    <div class="text-[11px] text-slate-500 dark:text-slate-400 mt-0.5 flex items-center gap-1.5">
                                        <span class="text-indigo-600 dark:text-indigo-400 font-bold">&bull; Counterpart:</span>
                                        <span>{{ $tx->counterpartUser->full_name }} ({{ $tx->counterpartUser->email }})</span>
                                    </div>
                                @endif
                            </td>

                            <!-- Amount -->
                            <td class="py-4 px-4 text-right whitespace-nowrap">
                                <span class="font-bold text-sm {{ $tx->type === 'credit' ? 'text-emerald-600 dark:text-emerald-400' : 'text-slate-900 dark:text-slate-100' }}">
                                    {{ $tx->type === 'credit' ? '+' : '-' }}{{ $tx->formatted_amount }}
                                </span>
                            </td>

                            <!-- Balance After -->
                            <td class="py-4 px-4 text-right text-slate-700 dark:text-slate-300 whitespace-nowrap text-xs">
                                ₦{{ number_format($tx->balance_after, 2) }}
                            </td>

                            <!-- Status -->
                            <td class="py-4 px-4 text-center whitespace-nowrap">
                                <span class="inline-flex items-center gap-1 text-[11px] font-semibold text-emerald-700 dark:text-emerald-400 bg-emerald-50 dark:bg-emerald-500/10 px-2.5 py-0.5 rounded-full border border-emerald-200 dark:border-emerald-500/30">
                                    <span class="w-1.5 h-1.5 rounded-full bg-emerald-500"></span>
                                    Success
                                </span>
                            </td>

                        </tr>
                    @empty
                        <tr>
                            <td colspan="6" class="py-12 text-center text-slate-500 dark:text-slate-400">
                                <div class="max-w-sm mx-auto space-y-3">
                                    <div class="w-12 h-12 rounded-2xl bg-slate-100 dark:bg-slate-800 text-slate-400 mx-auto flex items-center justify-center">
                                        <svg class="w-6 h-6" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M3 10h18M7 15h1m4 0h1m-7 4h12a3 3 0 003-3V8a3 3 0 00-3-3H6a3 3 0 00-3 3v8a3 3 0 003 3z" />
                                        </svg>
                                    </div>
                                    <p class="font-bold text-slate-800 dark:text-slate-200 text-sm">No Transactions Found</p>
                                    <p class="text-xs text-slate-400">
                                        Top up your wallet or use portal services to see transactions recorded here.
                                    </p>
                                    <a href="{{ route('wallet.index') }}"
                                       class="inline-flex items-center gap-1.5 px-4 py-2 rounded-xl text-xs font-bold text-white bg-emerald-600 hover:bg-emerald-500 shadow-sm transition cursor-pointer">
                                        <span>+ Fund Your Wallet</span>
                                    </a>
                                </div>
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
