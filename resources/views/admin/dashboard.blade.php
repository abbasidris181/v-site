@extends('layouts.admin')

@section('content')
<div class="space-y-6">

    <!-- ==========================================
         DASHBOARD HEADER & GREETING
         ========================================== -->
    <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-4">
        <div>
            <div class="flex items-center gap-2">
                <span class="text-xs font-bold uppercase tracking-widest text-indigo-700 dark:text-indigo-400 bg-indigo-50 dark:bg-indigo-500/10 px-3 py-1 rounded-full border border-indigo-200 dark:border-indigo-500/30">
                    Administrative Workspace
                </span>
                <span class="text-xs font-semibold text-slate-400 dark:text-slate-500">
                    &bull; {{ now()->format('l, F j, Y') }}
                </span>
            </div>
            <h1 class="mt-2 text-2xl sm:text-3xl font-extrabold text-slate-900 dark:text-white tracking-tight">
                Dashboard
            </h1>
            <p class="text-xs sm:text-sm text-slate-500 dark:text-slate-400 mt-1">
                Welcome, {{ auth()->user()->full_name }} &bull; Today's real-time operational overview and activity logs.
            </p>
        </div>

        <div class="flex items-center gap-2.5">
            <div class="px-3.5 py-2 rounded-2xl bg-white dark:bg-slate-900 border border-slate-200/80 dark:border-slate-800 shadow-xs flex items-center gap-2">
                <span class="w-2.5 h-2.5 rounded-full bg-emerald-500 animate-pulse"></span>
                <span class="text-xs font-bold text-slate-700 dark:text-slate-300">Live Ingestion</span>
            </div>
        </div>
    </div>

    <!-- ==========================================
         TOP ROW: 5 KEY PERFORMANCE METRIC CARDS
         ========================================== -->
    <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 xl:grid-cols-5 gap-3.5 sm:gap-4 lg:gap-5">
        
        <!-- 1. TOTAL USERS BALANCE -->
        <div class="rounded-2xl bg-white dark:bg-slate-900 border border-slate-200/80 dark:border-slate-800 p-4 sm:p-5 shadow-sm hover:border-amber-500/40 dark:hover:border-amber-500/30 transition-all group flex flex-col justify-between min-w-0 overflow-hidden">
            <div class="min-w-0">
                <div class="flex items-center justify-between gap-2 min-w-0">
                    <span class="text-[11px] sm:text-xs font-bold uppercase tracking-wider text-slate-500 dark:text-slate-400 truncate" title="Total Users Balance">
                        TOTAL USERS BALANCE
                    </span>
                    <div class="w-8 h-8 sm:w-9 sm:h-9 rounded-xl bg-amber-50 dark:bg-amber-500/10 text-amber-600 dark:text-amber-400 flex items-center justify-center border border-amber-200/80 dark:border-amber-500/20 group-hover:scale-105 transition-transform flex-shrink-0">
                        <svg class="w-4 h-4 sm:w-4.5 sm:h-4.5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 10h18M7 15h1m4 0h1m-7 4h12a3 3 0 003-3V8a3 3 0 00-3-3H6a3 3 0 00-3 3v8a3 3 0 003 3z" />
                        </svg>
                    </div>
                </div>

                <div class="mt-3 sm:mt-4 text-lg sm:text-xl xl:text-lg 2xl:text-2xl font-black text-slate-900 dark:text-white tracking-tight truncate tabular-nums" title="₦{{ number_format($totalUserBalance, 2) }}">
                    ₦{{ number_format($totalUserBalance, 2) }}
                </div>
            </div>

            <div class="mt-3 sm:mt-4 pt-3 border-t border-slate-100 dark:border-slate-800/80 flex items-center justify-between gap-1 text-[11px] text-slate-400 dark:text-slate-500 min-w-0">
                <span class="truncate">All User Wallets</span>
                <a href="{{ route('admin.finance.transactions') }}" class="text-amber-600 dark:text-amber-400 font-semibold hover:underline flex items-center gap-0.5 flex-shrink-0">
                    <span>Ledger</span>
                    <svg class="w-3 h-3" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7" /></svg>
                </a>
            </div>
        </div>

        <!-- 2. TODAY'S TOTAL DEPOSIT -->
        <div class="rounded-2xl bg-white dark:bg-slate-900 border border-slate-200/80 dark:border-slate-800 p-4 sm:p-5 shadow-sm hover:border-emerald-500/40 dark:hover:border-emerald-500/30 transition-all group flex flex-col justify-between min-w-0 overflow-hidden">
            <div class="min-w-0">
                <div class="flex items-center justify-between gap-2 min-w-0">
                    <span class="text-[11px] sm:text-xs font-bold uppercase tracking-wider text-slate-500 dark:text-slate-400 truncate" title="Today's Total Deposit">
                        TODAY&#039;S TOTAL DEPOSIT
                    </span>
                    <div class="w-8 h-8 sm:w-9 sm:h-9 rounded-xl bg-emerald-50 dark:bg-emerald-500/10 text-emerald-600 dark:text-emerald-400 flex items-center justify-center border border-emerald-200/80 dark:border-emerald-500/20 group-hover:scale-105 transition-transform flex-shrink-0">
                        <svg class="w-4 h-4 sm:w-4.5 sm:h-4.5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8c-1.657 0-3 .895-3 2s1.343 2 3 2 3 .895 3 2-1.343 2-3 2m0-8c1.11 0 2.08.402 2.599 1M12 8V7m0 1v8m0 0v1m0-1c-1.11 0-2.08-.402-2.599-1M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
                        </svg>
                    </div>
                </div>

                <div class="mt-3 sm:mt-4 text-lg sm:text-xl xl:text-lg 2xl:text-2xl font-black text-slate-900 dark:text-white tracking-tight truncate tabular-nums" title="₦{{ number_format($todayDeposits) }}">
                    ₦{{ number_format($todayDeposits) }}
                </div>
            </div>

            <div class="mt-3 sm:mt-4 pt-3 border-t border-slate-100 dark:border-slate-800/80 flex items-center justify-between gap-1 text-[11px] text-slate-400 dark:text-slate-500 min-w-0">
                <span class="truncate">Inbound Inflows</span>
                <span class="text-emerald-600 dark:text-emerald-400 font-semibold flex-shrink-0">&bull; Direct & Gateway</span>
            </div>
        </div>

        <!-- 3. TODAY'S TOTAL USERS -->
        <div class="rounded-2xl bg-white dark:bg-slate-900 border border-slate-200/80 dark:border-slate-800 p-4 sm:p-5 shadow-sm hover:border-indigo-500/40 dark:hover:border-indigo-500/30 transition-all group flex flex-col justify-between min-w-0 overflow-hidden">
            <div class="min-w-0">
                <div class="flex items-center justify-between gap-2 min-w-0">
                    <span class="text-[11px] sm:text-xs font-bold uppercase tracking-wider text-slate-500 dark:text-slate-400 truncate" title="Today's Total Users">
                        TODAY&#039;S TOTAL USERS
                    </span>
                    <div class="w-8 h-8 sm:w-9 sm:h-9 rounded-xl bg-indigo-50 dark:bg-indigo-500/10 text-indigo-600 dark:text-indigo-400 flex items-center justify-center border border-indigo-200/80 dark:border-indigo-500/20 group-hover:scale-105 transition-transform flex-shrink-0">
                        <svg class="w-4 h-4 sm:w-4.5 sm:h-4.5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0zm6 3a2 2 0 11-4 0 2 2 0 014 0zM7 10a2 2 0 11-4 0 2 2 0 014 0z" />
                        </svg>
                    </div>
                </div>

                <div class="mt-3 sm:mt-4 text-lg sm:text-xl xl:text-lg 2xl:text-2xl font-black text-slate-900 dark:text-white tracking-tight truncate tabular-nums" title="{{ number_format($todayUsers) }}">
                    {{ number_format($todayUsers) }}
                </div>
            </div>

            <div class="mt-3 sm:mt-4 pt-3 border-t border-slate-100 dark:border-slate-800/80 flex items-center justify-between gap-1 text-[11px] text-slate-400 dark:text-slate-500 min-w-0">
                <span class="truncate">New Registrations</span>
                <span class="text-indigo-600 dark:text-indigo-400 font-semibold flex-shrink-0">{{ number_format($totalUsers) }} Active</span>
            </div>
        </div>

        <!-- 4. TODAY'S NIN VERIFICATIONS -->
        <div class="rounded-2xl bg-white dark:bg-slate-900 border border-slate-200/80 dark:border-slate-800 p-4 sm:p-5 shadow-sm hover:border-blue-500/40 dark:hover:border-blue-500/30 transition-all group flex flex-col justify-between min-w-0 overflow-hidden">
            <div class="min-w-0">
                <div class="flex items-center justify-between gap-2 min-w-0">
                    <span class="text-[11px] sm:text-xs font-bold uppercase tracking-wider text-slate-500 dark:text-slate-400 truncate" title="Today's NIN Verifications">
                        TODAY&#039;S NIN VERIFICATIONS
                    </span>
                    <div class="w-8 h-8 sm:w-9 sm:h-9 rounded-xl bg-blue-50 dark:bg-blue-500/10 text-blue-600 dark:text-blue-400 flex items-center justify-center border border-blue-200/80 dark:border-blue-500/20 group-hover:scale-105 transition-transform flex-shrink-0">
                        <svg class="w-4 h-4 sm:w-4.5 sm:h-4.5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m5.618-4.016A11.955 11.955 0 0112 2.944a11.955 11.955 0 01-8.618 3.04A12.02 12.02 0 003 9c0 5.591 3.824 10.29 9 11.622 5.176-1.332 9-6.03 9-11.622 0-1.042-.133-2.052-.382-3.016z" />
                        </svg>
                    </div>
                </div>

                <div class="mt-3 sm:mt-4 text-lg sm:text-xl xl:text-lg 2xl:text-2xl font-black text-slate-900 dark:text-white tracking-tight truncate tabular-nums" title="{{ number_format($todayNinVerifications) }}">
                    {{ number_format($todayNinVerifications) }}
                </div>
            </div>

            <div class="mt-3 sm:mt-4 pt-3 border-t border-slate-100 dark:border-slate-800/80 flex items-center justify-between gap-1 text-[11px] text-slate-400 dark:text-slate-500 min-w-0">
                <span class="truncate">Automated Calls</span>
                <span class="text-blue-600 dark:text-blue-400 font-semibold flex-shrink-0">&bull; NIMC Validated</span>
            </div>
        </div>

        <!-- 5. TODAY'S BVN VERIFICATIONS -->
        <div class="rounded-2xl bg-white dark:bg-slate-900 border border-slate-200/80 dark:border-slate-800 p-4 sm:p-5 shadow-sm hover:border-violet-500/40 dark:hover:border-violet-500/30 transition-all group flex flex-col justify-between min-w-0 overflow-hidden">
            <div class="min-w-0">
                <div class="flex items-center justify-between gap-2 min-w-0">
                    <span class="text-[11px] sm:text-xs font-bold uppercase tracking-wider text-slate-500 dark:text-slate-400 truncate" title="Today's BVN Verifications">
                        TODAY&#039;S BVN VERIFICATIONS
                    </span>
                    <div class="w-8 h-8 sm:w-9 sm:h-9 rounded-xl bg-violet-50 dark:bg-violet-500/10 text-violet-600 dark:text-violet-400 flex items-center justify-center border border-violet-200/80 dark:border-violet-500/20 group-hover:scale-105 transition-transform flex-shrink-0">
                        <svg class="w-4 h-4 sm:w-4.5 sm:h-4.5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 11c0 3.517-1.009 6.799-2.753 9.571m-3.44-2.04l.054-.09A13.916 13.916 0 008 11a4 4 0 118 0c0 1.017-.07 2.019-.203 3m-2.118 6.844A21.88 21.88 0 0015.171 17m3.839 1.132c.645-2.266.99-4.659.99-7.132A8 8 0 008 4.07M3 15.364c.64-1.319 1-2.8 1-4.364 0-1.457.39-2.823 1.07-4" />
                        </svg>
                    </div>
                </div>

                <div class="mt-3 sm:mt-4 text-lg sm:text-xl xl:text-lg 2xl:text-2xl font-black text-slate-900 dark:text-white tracking-tight truncate tabular-nums" title="{{ number_format($todayBvnVerifications) }}">
                    {{ number_format($todayBvnVerifications) }}
                </div>
            </div>

            <div class="mt-3 sm:mt-4 pt-3 border-t border-slate-100 dark:border-slate-800/80 flex items-center justify-between gap-1 text-[11px] text-slate-400 dark:text-slate-500 min-w-0">
                <span class="truncate">Banking API</span>
                <span class="text-violet-600 dark:text-violet-400 font-semibold flex-shrink-0">&bull; NIBSS Validated</span>
            </div>
        </div>

    </div>

    <!-- ==========================================
         BOTTOM ROW: FINANCIAL OVERVIEW & MANUAL SERVICES
         ========================================== -->
    <div class="grid grid-cols-1 lg:grid-cols-2 gap-6">

        <!-- LEFT CARD: TODAY'S FINANCIAL OVERVIEW -->
        <div class="rounded-2xl bg-white dark:bg-slate-900 border border-slate-200/80 dark:border-slate-800 p-6 shadow-sm flex flex-col justify-between">
            <div>
                <!-- Card Header -->
                <div class="flex items-center justify-between">
                    <div>
                        <h3 class="text-sm sm:text-base font-extrabold uppercase tracking-wide text-slate-900 dark:text-white">
                            TODAY&#039;S FINANCIAL OVERVIEW
                        </h3>
                        <p class="text-[11px] text-slate-400 dark:text-slate-500 mt-0.5">
                            Platform capital inflow and service expenditure comparison
                        </p>
                    </div>
                    <span class="px-2.5 py-1 rounded-full text-[10px] font-extrabold uppercase tracking-wider bg-indigo-50 dark:bg-indigo-500/10 text-indigo-700 dark:text-indigo-300 border border-indigo-200/80 dark:border-indigo-500/20">
                        DEPOSIT VS EXPENSE
                    </span>
                </div>

                <!-- Donut Chart & Visual Ratio Section -->
                <div class="my-6 flex flex-col sm:flex-row items-center justify-center gap-6 sm:gap-10">
                    
                    <!-- SVG Circular Donut Chart -->
                    <div class="relative w-40 h-40 flex items-center justify-center flex-shrink-0">
                        @php
                            $circumference = 376.99; // 2 * pi * 60
                            $depositDash = round(($depositPercentage / 100) * $circumference, 2);
                            $expenseDash = round(($expensePercentage / 100) * $circumference, 2);
                        @endphp
                        <svg class="w-full h-full transform -rotate-90" viewBox="0 0 140 140">
                            <!-- Background Track -->
                            <circle cx="70" cy="70" r="60"
                                    fill="transparent"
                                    stroke="currentColor"
                                    stroke-width="14"
                                    class="text-slate-100 dark:text-slate-800" />
                            <!-- Deposit Arc (Emerald) -->
                            <circle cx="70" cy="70" r="60"
                                    fill="transparent"
                                    stroke="#10b981"
                                    stroke-width="14"
                                    stroke-dasharray="{{ $depositDash }} {{ $circumference }}"
                                    stroke-linecap="round"
                                    class="transition-all duration-1000 ease-out" />
                            <!-- Expense Arc (Rose) -->
                            @if($todayExpenses > 0)
                                <circle cx="70" cy="70" r="60"
                                        fill="transparent"
                                        stroke="#f43f5e"
                                        stroke-width="14"
                                        stroke-dasharray="{{ $expenseDash }} {{ $circumference }}"
                                        stroke-dashoffset="-{{ $depositDash }}"
                                        stroke-linecap="round"
                                        class="transition-all duration-1000 ease-out" />
                            @endif
                        </svg>

                        <!-- Center Metrics in Donut Hole -->
                        <div class="absolute inset-0 flex flex-col items-center justify-center text-center pointer-events-none">
                            <span class="text-[10px] font-bold uppercase tracking-wider text-slate-400 dark:text-slate-500">
                                Inflow Ratio
                            </span>
                            <span class="text-xl font-black text-slate-900 dark:text-white">
                                {{ $depositPercentage }}%
                            </span>
                            <span class="text-[9px] font-semibold text-emerald-600 dark:text-emerald-400">
                                Deposits
                            </span>
                        </div>
                    </div>

                    <!-- Side Financial Highlights -->
                    <div class="space-y-3.5 w-full max-w-xs">
                        <!-- Deposit Box -->
                        <div class="p-3 rounded-xl bg-emerald-50/60 dark:bg-emerald-950/20 border border-emerald-100 dark:border-emerald-500/20 flex items-center justify-between">
                            <div class="flex items-center gap-2.5">
                                <span class="w-2.5 h-2.5 rounded-full bg-emerald-500"></span>
                                <div>
                                    <div class="text-xs font-bold text-slate-900 dark:text-white">Deposit</div>
                                    <div class="text-[10px] text-slate-400">Total credited today</div>
                                </div>
                            </div>
                            <div class="text-right">
                                <div class="text-xs sm:text-sm font-black text-emerald-600 dark:text-emerald-400">
                                    ₦{{ number_format($todayDeposits) }}
                                </div>
                                <span class="text-[10px] font-bold text-emerald-700 dark:text-emerald-300">{{ $depositPercentage }}%</span>
                            </div>
                        </div>

                        <!-- Expense Box -->
                        <div class="p-3 rounded-xl bg-rose-50/60 dark:bg-rose-950/20 border border-rose-100 dark:border-rose-500/20 flex items-center justify-between">
                            <div class="flex items-center gap-2.5">
                                <span class="w-2.5 h-2.5 rounded-full bg-rose-500"></span>
                                <div>
                                    <div class="text-xs font-bold text-slate-900 dark:text-white">Expense</div>
                                    <div class="text-[10px] text-slate-400">Debits & API billing</div>
                                </div>
                            </div>
                            <div class="text-right">
                                <div class="text-xs sm:text-sm font-black text-rose-600 dark:text-rose-400">
                                    ₦{{ number_format($todayExpenses) }}
                                </div>
                                <span class="text-[10px] font-bold text-rose-700 dark:text-rose-300">{{ $expensePercentage }}%</span>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Bottom Margin Split -->
            <div class="pt-4 border-t border-slate-100 dark:border-slate-800 flex items-center justify-between text-xs">
                <span class="text-slate-500 dark:text-slate-400 font-medium">Net Platform Differential:</span>
                <span class="font-bold text-indigo-600 dark:text-indigo-400">
                    {{ $todayDeposits >= $todayExpenses ? '+' : '-' }}₦{{ number_format(abs($todayDeposits - $todayExpenses)) }}
                </span>
            </div>
        </div>

        <!-- RIGHT CARD: MANUAL SERVICES — TODAY -->
        <div class="rounded-2xl bg-white dark:bg-slate-900 border border-slate-200/80 dark:border-slate-800 p-6 shadow-sm flex flex-col justify-between">
            <div>
                <!-- Card Header -->
                <div class="flex items-center justify-between">
                    <div>
                        <h3 class="text-sm sm:text-base font-extrabold uppercase tracking-wide text-slate-900 dark:text-white">
                            MANUAL SERVICES — TODAY
                        </h3>
                        <p class="text-[11px] text-slate-400 dark:text-slate-500 mt-0.5">
                            Volume of individual accepted requests submitted today
                        </p>
                    </div>
                    <span class="px-2.5 py-1 rounded-full text-[10px] font-extrabold uppercase bg-slate-100 dark:bg-slate-800 text-slate-700 dark:text-slate-300 border border-slate-200 dark:border-slate-700">
                        {{ $totalManualToday }} Requests
                    </span>
                </div>

                <!-- Stylized Vertical Bar Chart (█) -->
                <div class="mt-6">
                    <div class="h-44 flex items-end justify-between gap-3 sm:gap-6 px-2">
                        @foreach($manualServicesChart as $svc)
                            <div class="flex-1 flex flex-col items-center justify-end h-full group cursor-default">
                                
                                <!-- Count Above Bar -->
                                <span class="text-[11px] font-extrabold text-slate-700 dark:text-slate-300 mb-1.5 group-hover:scale-110 transition-transform">
                                    {{ $svc['count'] }}
                                </span>

                                <!-- Stylized Column Bar (█) -->
                                <div class="w-full max-w-[44px] rounded-t-xl {{ $svc['bar_color'] }} transition-all duration-500 shadow-xs group-hover:brightness-110"
                                     style="height: {{ $svc['height_percent'] }}%;"
                                     title="{{ $svc['name'] }}: {{ $svc['count'] }} submitted today">
                                </div>
                            </div>
                        @endforeach
                    </div>

                    <!-- Continuous Horizontal Baseline Axis -->
                    <div class="w-full h-0.5 bg-slate-200 dark:bg-slate-700 my-0"></div>

                    <!-- Service Code Labels Below Baseline -->
                    <div class="flex items-center justify-between gap-3 sm:gap-6 px-2 pt-3">
                        @foreach($manualServicesChart as $svc)
                            <div class="flex-1 text-center">
                                <span class="block text-xs font-black tracking-wider text-slate-800 dark:text-slate-200">
                                    {{ $svc['code'] }}
                                </span>
                                <span class="block text-[10px] text-slate-400 dark:text-slate-500 truncate max-w-[64px] mx-auto" title="{{ $svc['name'] }}">
                                    {{ $svc['name'] }}
                                </span>
                            </div>
                        @endforeach
                    </div>
                </div>
            </div>

            <!-- Bottom Subtext from ASCII -->
            <div class="mt-6 pt-4 border-t border-slate-100 dark:border-slate-800 flex flex-col sm:flex-row sm:items-center justify-between gap-2 text-xs">
                <p class="text-slate-500 dark:text-slate-400 text-xs">
                    Volume of individual accepted requests submitted today.
                </p>
                <a href="{{ route('admin.queue.index') }}" class="inline-flex items-center gap-1 font-bold text-indigo-600 dark:text-indigo-400 hover:text-indigo-500 text-xs">
                    <span>Process Queues</span>
                    <span>&rarr;</span>
                </a>
            </div>
        </div>

    </div>

</div>
@endsection
