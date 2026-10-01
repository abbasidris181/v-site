<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}" class="h-full">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">

    <title>{{ config('app.name', 'Verification Portal') }} - Administrative Backend</title>

    <!-- Google Fonts: Roboto & Dynamic Typography -->
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Roboto:ital,wght@0,300;0,400;0,500;0,700;0,900;1,400;1,700&display=swap" rel="stylesheet">
    @if(!empty($siteFontGoogleUrl) && !str_contains($siteFontGoogleUrl, 'family=Roboto'))
        <link href="{{ $siteFontGoogleUrl }}" rel="stylesheet">
    @endif

    <!-- Dynamic Site Font Configuration -->
    <style>
        :root {
            --site-font-family: {!! $siteFontFamily ?? "'Roboto', Arial, sans-serif" !!};
            --font-mono: {!! $siteFontFamily ?? "'Roboto', Arial, sans-serif" !!};
        }
        body, html, button, input, select, textarea, [class*="font-sans"], [class*="font-mono"], .font-mono, code, kbd, samp, pre {
            font-family: {!! $siteFontFamily ?? "'Roboto', Arial, sans-serif" !!}, ui-sans-serif, system-ui, -apple-system, BlinkMacSystemFont, "Segoe UI", Roboto, "Helvetica Neue", Arial, sans-serif !important;
        }
    </style>

    <!-- Production Vite Compiled Assets -->
    @vite(['resources/css/app.css', 'resources/js/app.js'])

    <!-- Immediate Theme Initialization to Prevent Flash of Wrong Theme (Supports Light, Dark, System) -->
    <script>
        (function() {
            const savedTheme = localStorage.getItem('theme');
            if (savedTheme === 'dark' || ((!savedTheme || savedTheme === 'system') && window.matchMedia('(prefers-color-scheme: dark)').matches)) {
                document.documentElement.classList.add('dark');
            } else {
                document.documentElement.classList.remove('dark');
            }
        })();
    </script>
</head>
<body class="min-h-full font-sans antialiased text-slate-900 dark:text-slate-100 bg-slate-50 dark:bg-slate-950 flex flex-col transition-colors duration-200">
    <div class="min-h-screen flex flex-col md:flex-row w-full">
        
        <!-- Mobile Sidebar Backdrop Overlay -->
        <div id="sidebar-backdrop"
             onclick="toggleSidebar(false)"
             class="fixed inset-0 z-40 bg-slate-900/60 backdrop-blur-xs transition-opacity duration-300 opacity-0 pointer-events-none md:hidden">
        </div>

        <!-- ==========================================
             ADMINISTRATIVE SIDEBAR NAVIGATION
             ========================================== -->
        <aside id="sidebar-drawer"
               class="fixed inset-y-0 left-0 z-50 w-72 bg-white dark:bg-slate-900 border-r border-indigo-100 dark:border-indigo-500/20 flex flex-col justify-between transform -translate-x-full md:translate-x-0 transition-transform duration-300 ease-in-out shadow-xl md:shadow-none">
            
            <!-- Top Content Container -->
            <div class="flex-1 overflow-y-auto px-4 py-5 space-y-5">
                
                <!-- 1. Header: LOGO & ADMIN INDICATOR -->
                <div class="flex items-center justify-between px-2">
                    <a href="{{ route('admin.dashboard') }}" class="flex items-center gap-3 group">
                        @if(!empty($siteLogoUrl))
                            <img src="{{ $siteLogoUrl }}" alt="{{ $siteName ?? 'Logo' }}" class="w-10 h-10 rounded-xl object-contain shadow-md shadow-indigo-500/20 ring-1 ring-white/20 group-hover:scale-105 transition-all">
                        @else
                            <div class="w-10 h-10 rounded-xl bg-gradient-to-tr from-indigo-600 via-indigo-500 to-violet-600 flex items-center justify-center text-white shadow-lg shadow-indigo-500/25 ring-1 ring-white/20 group-hover:scale-105 transition-all">
                                <svg class="w-6 h-6" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                                    <path stroke-linecap="round" stroke-linejoin="round" d="M9 12l2 2 4-4m5.618-4.016A11.955 11.955 0 0112 2.944a11.955 11.955 0 01-8.618 3.04A12.02 12.02 0 003 9c0 5.591 3.824 10.29 9 11.622 5.176-1.332 9-6.03 9-11.622 0-1.042-.133-2.052-.382-3.016z" />
                                </svg>
                            </div>
                        @endif
                        <div>
                            <span class="text-xl font-black text-slate-900 dark:text-white tracking-tight">{{ $siteName ?? 'V-SITE' }}</span>
                            <div class="flex items-center gap-1.5">
                                <span class="text-[10px] text-indigo-600 dark:text-indigo-400 font-bold uppercase tracking-wider">ADMIN PANEL</span>
                                <span class="sr-only">ADMIN BACKEND</span>
                                <span class="text-[9px] text-indigo-700 dark:text-indigo-300 font-bold uppercase bg-indigo-50 dark:bg-indigo-500/10 px-1.5 py-0.2 rounded border border-indigo-200 dark:border-indigo-500/30">
                                    {{ auth()->user()->roles->pluck('name')->first() }}
                                </span>
                            </div>
                        </div>
                    </a>

                    <!-- Mobile Close Button (X) -->
                    <button type="button"
                            onclick="toggleSidebar(false)"
                            class="md:hidden p-2 rounded-lg text-slate-400 hover:text-slate-600 dark:hover:text-slate-200 hover:bg-slate-100 dark:hover:bg-slate-800 transition">
                        <svg class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
                        </svg>
                    </button>
                </div>

                <!-- 2. User Identity Block (◉ name, role) -->
                @if(auth()->check())
                    <div class="px-2">
                        <div class="p-3 rounded-2xl bg-indigo-50/50 dark:bg-indigo-950/30 border border-indigo-100 dark:border-indigo-500/20 flex items-center gap-3">
                            <!-- ◉ Avatar / User Indicator -->
                            <div class="relative flex-shrink-0">
                                <div class="w-10 h-10 rounded-xl bg-gradient-to-tr from-indigo-600 to-violet-500 text-white flex items-center justify-center font-bold text-sm shadow-sm">
                                    {{ strtoupper(substr(auth()->user()->first_name, 0, 1) . substr(auth()->user()->surname, 0, 1)) }}
                                </div>
                                <span class="absolute -bottom-0.5 -right-0.5 w-3 h-3 bg-indigo-500 rounded-full ring-2 ring-white dark:ring-slate-900"></span>
                            </div>
                            <div class="min-w-0 flex-1">
                                <div class="text-sm font-bold text-slate-900 dark:text-white truncate">
                                    {{ auth()->user()->full_name }}
                                </div>
                                <div class="text-xs font-semibold text-indigo-700 dark:text-indigo-400 capitalize truncate">
                                    {{ auth()->user()->roles->pluck('name')->first() }}
                                </div>
                            </div>
                        </div>
                    </div>
                @endif

                <!-- 3. Navigation Links List -->
                <nav class="space-y-1.5 text-sm font-medium">
                    <!-- 1. Dashboard -->
                    <a href="{{ route('admin.dashboard') }}"
                       class="flex items-center gap-3 px-3.5 py-2.5 rounded-xl transition-all {{ request()->routeIs('admin.dashboard') ? 'bg-indigo-50 dark:bg-indigo-500/10 text-indigo-700 dark:text-indigo-300 font-bold border border-indigo-200/80 dark:border-indigo-500/20 shadow-xs' : 'text-slate-600 dark:text-slate-300 hover:text-slate-900 dark:hover:text-white hover:bg-slate-100 dark:hover:bg-slate-800/60' }}">
                        <svg class="w-5 h-5 flex-shrink-0 {{ request()->routeIs('admin.dashboard') ? 'text-indigo-600 dark:text-indigo-400' : 'text-slate-400' }}" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6a2 2 0 012-2h2a2 2 0 012 2v2a2 2 0 01-2 2H6a2 2 0 01-2-2V6zM14 6a2 2 0 012-2h2a2 2 0 012 2v2a2 2 0 01-2 2h-2a2 2 0 01-2-2V6zM4 16a2 2 0 012-2h2a2 2 0 012 2v2a2 2 0 01-2 2H6a2 2 0 01-2-2v-2zM14 16a2 2 0 012-2h2a2 2 0 012 2v2a2 2 0 01-2 2h-2a2 2 0 01-2-2v-2z" />
                        </svg>
                        <span>Dashboard</span>
                    </a>

                    <!-- 2. Service Requests -->
                    <div class="space-y-0.5">
                        <button type="button"
                                id="service-requests-dropdown-btn"
                                onclick="toggleServiceRequestsDropdown()"
                                class="w-full flex items-center justify-between px-3.5 py-2.5 rounded-xl transition-all {{ request()->routeIs('admin.queue.*') ? 'bg-indigo-50 dark:bg-indigo-500/10 text-indigo-700 dark:text-indigo-300 font-bold border border-indigo-200/80 dark:border-indigo-500/20 shadow-xs' : 'text-slate-600 dark:text-slate-300 hover:text-slate-900 dark:hover:text-white hover:bg-slate-100 dark:hover:bg-slate-800/60' }} cursor-pointer text-left">
                            <div class="flex items-center gap-3">
                                <svg class="w-5 h-5 flex-shrink-0 {{ request()->routeIs('admin.queue.*') ? 'text-indigo-600 dark:text-indigo-400' : 'text-slate-400' }}" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2" />
                                </svg>
                                <span>Service Requests</span>
                            </div>
                            <div class="flex items-center gap-2">
                                @php
                                    $badgePending = \App\Models\ServiceRequest::where('status', 'pending')->count();
                                @endphp
                                @if($badgePending > 0)
                                    <span class="px-2 py-0.5 rounded-full text-[10px] font-extrabold bg-amber-500 text-white shadow-xs">
                                        {{ $badgePending }}
                                    </span>
                                @endif
                                <svg id="service-requests-chevron" class="w-4 h-4 text-slate-400 transition-transform duration-200 {{ request()->routeIs('admin.queue.*') ? 'rotate-180' : '' }}" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7" />
                                </svg>
                            </div>
                        </button>

                        @php
                            $sidebarManualServices = [
                                ['name' => 'IPE Clearing', 'slug' => 'ipe-clearing'],
                                ['name' => 'NIN Validation', 'slug' => 'nin-validation'],
                                ['name' => 'Modification IPE', 'slug' => 'modification-ipe'],
                                ['name' => 'BVN Retrieval by Phone Number', 'slug' => 'bvn-retrieval'],
                                ['name' => 'Self-Service Delinking', 'slug' => 'self-service-delinking'],
                                ['name' => 'Personalization', 'slug' => 'personalization'],
                            ];
                        @endphp
                        <div id="service-requests-subnav" class="pl-7 pr-2 space-y-0.5 pt-1 pb-1 border-l-2 border-indigo-100 dark:border-indigo-500/20 ml-5 {{ request()->routeIs('admin.queue.*') ? '' : 'hidden' }}">
                            @foreach($sidebarManualServices as $sNav)
                                @php
                                    $svcObj = \App\Models\Service::where('slug', $sNav['slug'])->first();
                                    $sNavPending = $svcObj ? \App\Models\ServiceRequest::where('service_id', $svcObj->id)->where('status', 'pending')->count() : 0;
                                    $isCurrentSvc = (request()->routeIs('admin.queue.*') && (request('service_slug') == $sNav['slug'] || ($svcObj && request('service_id') == $svcObj->id) || (!request('service_slug') && !request('service_id') && $loop->first)));
                                @endphp
                                <a href="{{ route('admin.queue.index', ['service_slug' => $sNav['slug']]) }}"
                                   class="flex items-center justify-between px-2.5 py-1.5 rounded-lg text-xs transition-colors {{ $isCurrentSvc ? 'bg-indigo-100/80 dark:bg-indigo-500/20 text-indigo-700 dark:text-indigo-300 font-bold' : 'text-slate-500 dark:text-slate-400 hover:text-slate-900 dark:hover:text-white hover:bg-slate-100 dark:hover:bg-slate-800/40' }}">
                                    <span class="truncate">{{ $sNav['name'] }}</span>
                                    @if($sNavPending > 0)
                                        <span class="ml-1 text-[10px] px-1.5 py-0.2 rounded-full font-bold bg-amber-100 dark:bg-amber-500/20 text-amber-800 dark:text-amber-300">
                                            {{ $sNavPending }}
                                        </span>
                                    @endif
                                </a>
                            @endforeach
                        </div>
                    </div>

                    <!-- 3. Users -->
                    @if(auth()->user()->hasRole('super_admin') || auth()->user()->hasPermission('users.view'))
                        <div class="space-y-0.5">
                            <button type="button"
                                    id="users-dropdown-btn"
                                    onclick="toggleUsersDropdown()"
                                    class="w-full flex items-center justify-between px-3.5 py-2.5 rounded-xl transition-all {{ request()->routeIs('admin.users.*') ? 'bg-indigo-50 dark:bg-indigo-500/10 text-indigo-700 dark:text-indigo-300 font-bold border border-indigo-200/80 dark:border-indigo-500/20 shadow-xs' : 'text-slate-600 dark:text-slate-300 hover:text-slate-900 dark:hover:text-white hover:bg-slate-100 dark:hover:bg-slate-800/60' }} cursor-pointer text-left">
                                <div class="flex items-center gap-3">
                                    <svg class="w-5 h-5 flex-shrink-0 {{ request()->routeIs('admin.users.*') ? 'text-indigo-600 dark:text-indigo-400' : 'text-slate-400' }}" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4.354a4 4 0 110 5.292M15 21H3v-1a6 6 0 0112 0v1zm0 0h6v-1a6 6 0 00-9-5.197M13 7a4 4 0 11-8 0 4 4 0 018 0z" />
                                    </svg>
                                    <span>Users</span>
                                </div>
                                <div class="flex items-center gap-2">
                                    <svg id="users-chevron" class="w-4 h-4 text-slate-400 transition-transform duration-200 {{ request()->routeIs('admin.users.*') ? 'rotate-180' : '' }}" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7" />
                                    </svg>
                                </div>
                            </button>

                            <div id="users-subnav" class="pl-7 pr-2 space-y-0.5 pt-1 pb-1 border-l-2 border-indigo-100 dark:border-indigo-500/20 ml-5 {{ request()->routeIs('admin.users.*') ? '' : 'hidden' }}">
                                <a href="{{ route('admin.users.index') }}"
                                   class="flex items-center justify-between px-2.5 py-1.5 rounded-lg text-xs transition-colors {{ request()->routeIs('admin.users.*') && !request('role') ? 'bg-indigo-100/80 dark:bg-indigo-500/20 text-indigo-700 dark:text-indigo-300 font-bold' : 'text-slate-500 dark:text-slate-400 hover:text-slate-900 dark:hover:text-white hover:bg-slate-100 dark:hover:bg-slate-800/40' }}">
                                    <span>All Users</span>
                                </a>
                                <a href="{{ route('admin.users.index', ['role' => 'agent']) }}"
                                   class="flex items-center justify-between px-2.5 py-1.5 rounded-lg text-xs transition-colors {{ request('role') == 'agent' ? 'bg-indigo-100/80 dark:bg-indigo-500/20 text-indigo-700 dark:text-indigo-300 font-bold' : 'text-slate-500 dark:text-slate-400 hover:text-slate-900 dark:hover:text-white hover:bg-slate-100 dark:hover:bg-slate-800/40' }}">
                                    <span>Agents</span>
                                </a>
                                <a href="{{ route('admin.users.index', ['role' => 'staff']) }}"
                                   class="flex items-center justify-between px-2.5 py-1.5 rounded-lg text-xs transition-colors {{ request('role') == 'staff' ? 'bg-indigo-100/80 dark:bg-indigo-500/20 text-indigo-700 dark:text-indigo-300 font-bold' : 'text-slate-500 dark:text-slate-400 hover:text-slate-900 dark:hover:text-white hover:bg-slate-100 dark:hover:bg-slate-800/40' }}">
                                    <span>Staff</span>
                                </a>
                                <a href="{{ route('admin.users.index', ['role' => 'admin']) }}"
                                   class="flex items-center justify-between px-2.5 py-1.5 rounded-lg text-xs transition-colors {{ in_array(request('role'), ['admin', 'administrators']) ? 'bg-indigo-100/80 dark:bg-indigo-500/20 text-indigo-700 dark:text-indigo-300 font-bold' : 'text-slate-500 dark:text-slate-400 hover:text-slate-900 dark:hover:text-white hover:bg-slate-100 dark:hover:bg-slate-800/40' }}">
                                    <span>Administrators</span>
                                </a>
                            </div>
                        </div>
                    @endif

                    <!-- 4. Wallet & Finance -->
                    <div class="space-y-0.5">
                        <button type="button"
                                id="finance-dropdown-btn"
                                onclick="toggleFinanceDropdown()"
                                class="w-full flex items-center justify-between px-3.5 py-2.5 rounded-xl transition-all {{ request()->routeIs('admin.finance.*') ? 'bg-indigo-50 dark:bg-indigo-500/10 text-indigo-700 dark:text-indigo-300 font-bold border border-indigo-200/80 dark:border-indigo-500/20 shadow-xs' : 'text-slate-600 dark:text-slate-300 hover:text-slate-900 dark:hover:text-white hover:bg-slate-100 dark:hover:bg-slate-800/60' }} cursor-pointer text-left">
                            <div class="flex items-center gap-3">
                                <svg class="w-5 h-5 flex-shrink-0 {{ request()->routeIs('admin.finance.*') ? 'text-indigo-600 dark:text-indigo-400' : 'text-slate-400' }}" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 10h18M7 15h1m4 0h1m-7 4h12a3 3 0 003-3V8a3 3 0 00-3-3H6a3 3 0 00-3 3v8a3 3 0 003 3z" />
                                </svg>
                                <span>Wallet &amp; Finance</span>
                            </div>
                            <div class="flex items-center gap-2">
                                <svg id="finance-chevron" class="w-4 h-4 text-slate-400 transition-transform duration-200 {{ request()->routeIs('admin.finance.*') ? 'rotate-180' : '' }}" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7" />
                                </svg>
                            </div>
                        </button>

                        <div id="finance-subnav" class="pl-7 pr-2 space-y-0.5 pt-1 pb-1 border-l-2 border-indigo-100 dark:border-indigo-500/20 ml-5 {{ request()->routeIs('admin.finance.*') ? '' : 'hidden' }}">
                            <a href="{{ route('admin.finance.fund') }}"
                               class="flex items-center justify-between px-2.5 py-1.5 rounded-lg text-xs transition-colors {{ request()->routeIs('admin.finance.fund') ? 'bg-indigo-100/80 dark:bg-indigo-500/20 text-indigo-700 dark:text-indigo-300 font-bold' : 'text-slate-500 dark:text-slate-400 hover:text-slate-900 dark:hover:text-white hover:bg-slate-100 dark:hover:bg-slate-800/40' }}">
                                <span>Fund User</span>
                            </a>
                            <a href="{{ route('admin.finance.transactions') }}"
                               class="flex items-center justify-between px-2.5 py-1.5 rounded-lg text-xs transition-colors {{ request()->routeIs('admin.finance.transactions') ? 'bg-indigo-100/80 dark:bg-indigo-500/20 text-indigo-700 dark:text-indigo-300 font-bold' : 'text-slate-500 dark:text-slate-400 hover:text-slate-900 dark:hover:text-white hover:bg-slate-100 dark:hover:bg-slate-800/40' }}">
                                <span>Wallet Transactions</span>
                            </a>
                            <a href="{{ route('admin.finance.history') }}"
                               class="flex items-center justify-between px-2.5 py-1.5 rounded-lg text-xs transition-colors {{ request()->routeIs('admin.finance.history') ? 'bg-indigo-100/80 dark:bg-indigo-500/20 text-indigo-700 dark:text-indigo-300 font-bold' : 'text-slate-500 dark:text-slate-400 hover:text-slate-900 dark:hover:text-white hover:bg-slate-100 dark:hover:bg-slate-800/40' }}">
                                <span>Funding History</span>
                            </a>
                        </div>
                    </div>

                    <!-- 5. Transactions -->
                    <div class="space-y-0.5">
                        <button type="button"
                                id="transactions-dropdown-btn"
                                onclick="toggleTransactionsDropdown()"
                                class="w-full flex items-center justify-between px-3.5 py-2.5 rounded-xl transition-all {{ request()->routeIs('admin.transactions.*') ? 'bg-indigo-50 dark:bg-indigo-500/10 text-indigo-700 dark:text-indigo-300 font-bold border border-indigo-200/80 dark:border-indigo-500/20 shadow-xs' : 'text-slate-600 dark:text-slate-300 hover:text-slate-900 dark:hover:text-white hover:bg-slate-100 dark:hover:bg-slate-800/60' }} cursor-pointer text-left">
                            <div class="flex items-center gap-3">
                                <svg class="w-5 h-5 flex-shrink-0 {{ request()->routeIs('admin.transactions.*') ? 'text-indigo-600 dark:text-indigo-400' : 'text-slate-400' }}" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 19v-6a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2a2 2 0 002-2zm0 0V9a2 2 0 012-2h2a2 2 0 012 2v10m-6 0a2 2 0 002 2h2a2 2 0 002-2m0 0V5a2 2 0 012-2h2a2 2 0 012 2v14a2 2 0 01-2 2h-2a2 2 0 01-2-2z" />
                                </svg>
                                <span>Transactions</span>
                            </div>
                            <div class="flex items-center gap-2">
                                <svg id="transactions-chevron" class="w-4 h-4 text-slate-400 transition-transform duration-200 {{ request()->routeIs('admin.transactions.*') ? 'rotate-180' : '' }}" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7" />
                                </svg>
                            </div>
                        </button>

                        <div id="transactions-subnav" class="pl-7 pr-2 space-y-0.5 pt-1 pb-1 border-l-2 border-indigo-100 dark:border-indigo-500/20 ml-5 {{ request()->routeIs('admin.transactions.*') ? '' : 'hidden' }}">
                            <a href="{{ route('admin.transactions.nin') }}"
                               class="flex items-center justify-between px-2.5 py-1.5 rounded-lg text-xs transition-colors {{ request()->routeIs('admin.transactions.nin') ? 'bg-indigo-100/80 dark:bg-indigo-500/20 text-indigo-700 dark:text-indigo-300 font-bold' : 'text-slate-500 dark:text-slate-400 hover:text-slate-900 dark:hover:text-white hover:bg-slate-100 dark:hover:bg-slate-800/40' }}">
                                <span>NIN Verifications</span>
                            </a>
                            <a href="{{ route('admin.transactions.bvn') }}"
                               class="flex items-center justify-between px-2.5 py-1.5 rounded-lg text-xs transition-colors {{ request()->routeIs('admin.transactions.bvn') ? 'bg-indigo-100/80 dark:bg-indigo-500/20 text-indigo-700 dark:text-indigo-300 font-bold' : 'text-slate-500 dark:text-slate-400 hover:text-slate-900 dark:hover:text-white hover:bg-slate-100 dark:hover:bg-slate-800/40' }}">
                                <span>BVN Verifications</span>
                            </a>
                            <a href="{{ route('admin.transactions.deposits') }}"
                               class="flex items-center justify-between px-2.5 py-1.5 rounded-lg text-xs transition-colors {{ request()->routeIs('admin.transactions.deposits') ? 'bg-indigo-100/80 dark:bg-indigo-500/20 text-indigo-700 dark:text-indigo-300 font-bold' : 'text-slate-500 dark:text-slate-400 hover:text-slate-900 dark:hover:text-white hover:bg-slate-100 dark:hover:bg-slate-800/40' }}">
                                <span>Deposit History</span>
                            </a>
                            <a href="{{ route('admin.transactions.transfers') }}"
                               class="flex items-center justify-between px-2.5 py-1.5 rounded-lg text-xs transition-colors {{ request()->routeIs('admin.transactions.transfers') ? 'bg-indigo-100/80 dark:bg-indigo-500/20 text-indigo-700 dark:text-indigo-300 font-bold' : 'text-slate-500 dark:text-slate-400 hover:text-slate-900 dark:hover:text-white hover:bg-slate-100 dark:hover:bg-slate-800/40' }}">
                                <span>Wallet-to-Wallet Transactions</span>
                            </a>
                        </div>
                    </div>

                    <!-- 6. Charges -->
                    @if(auth()->user()->hasRole('super_admin') || auth()->user()->hasPermission('settings.manage'))
                        <div class="space-y-0.5">
                            <button type="button"
                                    id="charges-dropdown-btn"
                                    onclick="toggleChargesDropdown()"
                                    class="w-full flex items-center justify-between px-3.5 py-2.5 rounded-xl transition-all {{ request()->routeIs('admin.charges.*') ? 'bg-indigo-50 dark:bg-indigo-500/10 text-indigo-700 dark:text-indigo-300 font-bold border border-indigo-200/80 dark:border-indigo-500/20 shadow-xs' : 'text-slate-600 dark:text-slate-300 hover:text-slate-900 dark:hover:text-white hover:bg-slate-100 dark:hover:bg-slate-800/60' }} cursor-pointer text-left">
                                <div class="flex items-center gap-3">
                                    <svg class="w-5 h-5 flex-shrink-0 {{ request()->routeIs('admin.charges.*') ? 'text-indigo-600 dark:text-indigo-400' : 'text-slate-400' }}" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8c-1.657 0-3 .895-3 2s1.343 2 3 2 3 .895 3 2-1.343 2-3 2m0-8c1.11 0 2.08.402 2.599 1M12 8V7m0 1v8m0 0v1m0-1c-1.11 0-2.08-.402-2.599-1M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
                                    </svg>
                                    <span>Charges</span>
                                </div>
                                <div class="flex items-center gap-2">
                                    <svg id="charges-chevron" class="w-4 h-4 text-slate-400 transition-transform duration-200 {{ request()->routeIs('admin.charges.*') ? 'rotate-180' : '' }}" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7" />
                                    </svg>
                                </div>
                            </button>

                            <div id="charges-subnav" class="pl-7 pr-2 space-y-0.5 pt-1 pb-1 border-l-2 border-indigo-100 dark:border-indigo-500/20 ml-5 {{ request()->routeIs('admin.charges.*') ? '' : 'hidden' }}">
                                <a href="{{ route('admin.charges.index') }}"
                                   class="flex items-center justify-between px-2.5 py-1.5 rounded-lg text-xs transition-colors {{ request()->routeIs('admin.charges.*') ? 'bg-indigo-100/80 dark:bg-indigo-500/20 text-indigo-700 dark:text-indigo-300 font-bold' : 'text-slate-500 dark:text-slate-400 hover:text-slate-900 dark:hover:text-white hover:bg-slate-100 dark:hover:bg-slate-800/40' }}">
                                    <span>Pricing</span>
                                </a>
                            </div>
                        </div>
                    @endif

                    <!-- 7. System Settings -->
                    @if(Route::has('admin.settings.index') && (auth()->user()->hasRole('super_admin') || auth()->user()->hasPermission('settings.manage')))
                        <a href="{{ route('admin.settings.index') }}"
                           class="flex items-center gap-3 px-3.5 py-2.5 rounded-xl transition-all {{ request()->routeIs('admin.settings.*') ? 'bg-indigo-50 dark:bg-indigo-500/10 text-indigo-700 dark:text-indigo-300 font-bold border border-indigo-200/80 dark:border-indigo-500/20 shadow-xs' : 'text-slate-600 dark:text-slate-300 hover:text-slate-900 dark:hover:text-white hover:bg-slate-100 dark:hover:bg-slate-800/60' }}">
                            <svg class="w-5 h-5 flex-shrink-0 {{ request()->routeIs('admin.settings.*') ? 'text-indigo-600 dark:text-indigo-400' : 'text-slate-400' }}" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10.325 4.317c.426-1.756 2.924-1.756 3.35 0a1.724 1.724 0 002.573 1.066c1.543-.94 3.31.826 2.37 2.37a1.724 1.724 0 001.065 2.572c1.756.426 1.756 2.924 0 3.35a1.724 1.724 0 00-1.066 2.573c.94 1.543-.826 3.31-2.37 2.37a1.724 1.724 0 00-2.572 1.065c-.426 1.756-2.924 1.756-3.35 0a1.724 1.724 0 00-2.573-1.066c-1.543.94-3.31-.826-2.37-2.37a1.724 1.724 0 00-1.065-2.572c-1.756-.426-1.756-2.924 0-3.35a1.724 1.724 0 001.066-2.573c-.94-1.543.826-3.31 2.37-2.37.996.608 2.296.07 2.572-1.065z" />
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z" />
                            </svg>
                            <span>System Settings</span>
                        </a>
                    @endif

                    @if(Route::has('admin.announcements.index') && (auth()->user()->hasRole('super_admin') || auth()->user()->hasPermission('announcements.manage')))
                        <!-- 📢 Announcements -->
                        <a href="{{ route('admin.announcements.index') }}"
                           class="flex items-center gap-3 px-3.5 py-2.5 rounded-xl transition-all {{ request()->routeIs('admin.announcements.*') ? 'bg-indigo-50 dark:bg-indigo-500/10 text-indigo-700 dark:text-indigo-300 font-bold border border-indigo-200/80 dark:border-indigo-500/20 shadow-xs' : 'text-slate-600 dark:text-slate-300 hover:text-slate-900 dark:hover:text-white hover:bg-slate-100 dark:hover:bg-slate-800/60' }}">
                            <svg class="w-5 h-5 flex-shrink-0 {{ request()->routeIs('admin.announcements.*') ? 'text-indigo-600 dark:text-indigo-400' : 'text-slate-400' }}" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5.882V19.24a1.76 1.76 0 01-3.417.592l-2.147-6.15M18 13a3 3 0 100-6M5.436 13.683A4.001 4.001 0 017 6h1.832c4.1 0 7.625-1.234 9.168-3v14c-1.543-1.766-5.067-3-9.168-3H7a3.988 3.988 0 01-1.564-.317z" />
                            </svg>
                            <span>Announcements</span>
                        </a>
                    @endif

                    @if(Route::has('admin.faqs.index') && (auth()->user()->hasRole('super_admin') || auth()->user()->hasPermission('settings.manage')))
                        <!-- ❓ Support FAQs -->
                        <a href="{{ route('admin.faqs.index') }}"
                           class="flex items-center gap-3 px-3.5 py-2.5 rounded-xl transition-all {{ request()->routeIs('admin.faqs.*') ? 'bg-indigo-50 dark:bg-indigo-500/10 text-indigo-700 dark:text-indigo-300 font-bold border border-indigo-200/80 dark:border-indigo-500/20 shadow-xs' : 'text-slate-600 dark:text-slate-300 hover:text-slate-900 dark:hover:text-white hover:bg-slate-100 dark:hover:bg-slate-800/60' }}">
                            <svg class="w-5 h-5 flex-shrink-0 {{ request()->routeIs('admin.faqs.*') ? 'text-indigo-600 dark:text-indigo-400' : 'text-slate-400' }}" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8.228 9c.549-1.165 2.03-2 3.772-2 2.21 0 4 1.343 4 3 0 1.4-1.278 2.575-3.006 2.907-.542.104-.994.54-.994 1.093m0 3h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
                            </svg>
                            <span>Support FAQs</span>
                        </a>
                    @endif

                    @if(Route::has('admin.roles.index') && auth()->user()->hasRole('super_admin'))
                        <!-- ⚙ Roles & Permissions -->
                        <a href="{{ route('admin.roles.index') }}"
                           class="flex items-center gap-3 px-3.5 py-2.5 rounded-xl transition-all {{ request()->routeIs('admin.roles.*') ? 'bg-indigo-50 dark:bg-indigo-500/10 text-indigo-700 dark:text-indigo-300 font-bold border border-indigo-200/80 dark:border-indigo-500/20 shadow-xs' : 'text-slate-600 dark:text-slate-300 hover:text-slate-900 dark:hover:text-white hover:bg-slate-100 dark:hover:bg-slate-800/60' }}">
                            <svg class="w-5 h-5 flex-shrink-0 text-slate-400" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6V4m0 2a2 2 0 100 4m0-4a2 2 0 110 4m-6 8a2 2 0 100-4m0 4a2 2 0 110-4m0 4v2m0-6V4m6 6v10m6-2a2 2 0 100-4m0 4a2 2 0 110-4m0 4v2m0-6V4" />
                            </svg>
                            <span>Roles & Permissions</span>
                        </a>
                    @endif

                    <!-- 8. Staff Intercom & Internal Chat -->
                    <a href="{{ route('admin.chat.index') }}"
                       class="flex items-center justify-between px-3.5 py-2.5 rounded-xl transition-all {{ request()->routeIs('admin.chat.*') ? 'bg-indigo-50 dark:bg-indigo-500/10 text-indigo-700 dark:text-indigo-300 font-bold border border-indigo-200/80 dark:border-indigo-500/20 shadow-xs' : 'text-slate-600 dark:text-slate-300 hover:text-slate-900 dark:hover:text-white hover:bg-slate-100 dark:hover:bg-slate-800/60' }}">
                        <div class="flex items-center gap-3">
                            <svg class="w-5 h-5 flex-shrink-0 {{ request()->routeIs('admin.chat.*') ? 'text-indigo-600 dark:text-indigo-400' : 'text-slate-400' }}" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 12h.01M12 12h.01M16 12h.01M21 12c0 4.418-4.03 8-9 8a9.863 9.863 0 01-4.255-.949L3 20l1.395-3.72C3.512 15.042 3 13.574 3 12c0-4.418 4.03-8 9-8s9 3.582 9 8z" />
                            </svg>
                            <span>Internal Comms</span>
                        </div>
                        <span id="global-unread-chat-badge" class="hidden px-2 py-0.5 rounded-full text-[10px] font-extrabold bg-indigo-600 text-white shadow-xs">
                            0
                        </span>
                    </a>
                </nav>
            </div>

            <!-- Bottom Content (Back to User Dashboard & Logout) -->
            <div class="p-4 border-t border-indigo-100 dark:border-indigo-500/20 space-y-1.5">
                <!-- Back to User Dashboard -->
                <a href="{{ route('dashboard') }}"
                   class="flex items-center gap-3 px-3.5 py-2.5 rounded-xl text-sm font-semibold text-emerald-700 dark:text-emerald-400 bg-emerald-50/80 dark:bg-emerald-500/10 hover:bg-emerald-100 dark:hover:bg-emerald-500/20 border border-emerald-200/80 dark:border-emerald-500/30 transition-all shadow-xs group">
                    <svg class="w-5 h-5 text-emerald-600 dark:text-emerald-400 group-hover:-translate-x-0.5 transition-transform" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18" />
                    </svg>
                    <span>Back to User Dashboard</span>
                </a>

                <!-- Logout -->
                <form method="POST" action="{{ route('logout') }}" class="w-full">
                    @csrf
                    <button type="submit"
                            class="w-full flex items-center gap-3 px-3.5 py-2.5 rounded-xl text-sm font-medium text-slate-500 dark:text-slate-400 hover:text-red-600 dark:hover:text-red-400 hover:bg-red-50 dark:hover:bg-red-500/10 transition-colors cursor-pointer">
                        <svg class="w-5 h-5 text-slate-400 group-hover:text-red-500" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 16l4-4m0 0l-4-4m4 4H7m6 4v1a3 3 0 01-3 3H6a3 3 0 01-3-3V7a3 3 0 013-3h4a3 3 0 013 3v1" />
                        </svg>
                        <span>Logout</span>
                    </button>
                </form>
            </div>
        </aside>

        <!-- ==========================================
             MAIN CONTENT WRAPPER (RIGHT SIDE)
             ========================================== -->
        <div class="flex-1 flex flex-col min-w-0 md:pl-72 transition-all">
            
            <!-- Sticky Top Header Navbar -->
            <header class="sticky top-0 z-30 bg-white/95 dark:bg-slate-900/90 backdrop-blur-md border-b border-indigo-100 dark:border-indigo-500/20 shadow-xs transition-colors">
                <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
                    <div class="flex items-center justify-between h-16">
                        
                        <!-- Left: Mobile Hamburger & Breadcrumb -->
                        <div class="flex items-center gap-3">
                            <!-- Mobile Hamburger Button -->
                            <button type="button"
                                    onclick="toggleSidebar(true)"
                                    title="Open navigation menu"
                                    class="md:hidden p-2 rounded-xl text-slate-600 dark:text-slate-300 hover:bg-slate-100 dark:hover:bg-slate-800 border border-slate-200 dark:border-slate-700 transition cursor-pointer">
                                <svg class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6h16M4 12h16M4 18h16" />
                                </svg>
                            </button>

                            <div>
                                <span class="text-[10px] font-bold text-indigo-600 dark:text-indigo-400 uppercase tracking-widest">ADMINISTRATIVE WORKSPACE</span>
                                <div class="text-sm font-extrabold text-slate-900 dark:text-white">
                                    ADMIN BACKEND &bull; {{ auth()->user()->roles->pluck('name')->first() }}
                                </div>
                            </div>
                        </div>

                        <!-- Right: Actions, Return to Portal & Theme Toggle -->
                        <div class="flex items-center gap-2.5 sm:gap-3.5">
                            
                            <!-- Theme Toggle (Light, Dark, System) -->
                            <x-theme-toggle />

                            <!-- Return to User Portal Shortcut -->
                            <a href="{{ route('dashboard') }}"
                               class="inline-flex items-center gap-2 px-3 py-1.5 rounded-xl text-xs font-semibold text-slate-700 dark:text-slate-300 hover:text-slate-900 dark:hover:text-white bg-slate-100 dark:bg-slate-800 hover:bg-slate-200 dark:hover:bg-slate-700 border border-slate-200 dark:border-slate-700 transition-colors">
                                <svg class="w-4 h-4 text-emerald-600 dark:text-emerald-400" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18" />
                                </svg>
                                <span class="hidden sm:inline">User Portal</span>
                            </a>
                        </div>
                    </div>
                </div>
            </header>

            <!-- Global Flash Messages -->
            <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 w-full pt-4">
                @if (session('status') || session('success'))
                    <div class="mb-4 rounded-2xl bg-emerald-50 dark:bg-emerald-500/10 border border-emerald-200 dark:border-emerald-500/30 p-4 text-sm text-emerald-800 dark:text-emerald-300 flex items-center gap-3 shadow-xs">
                        <svg class="w-5 h-5 flex-shrink-0 text-emerald-600 dark:text-emerald-400" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z" />
                        </svg>
                        <span>{{ session('status') ?? session('success') }}</span>
                    </div>
                @endif

                @if (session('error'))
                    <div class="mb-4 rounded-2xl bg-red-50 dark:bg-red-500/10 border border-red-200 dark:border-red-500/30 p-4 text-sm text-red-800 dark:text-red-300 flex items-center gap-3 shadow-xs">
                        <svg class="w-5 h-5 flex-shrink-0 text-red-600 dark:text-red-400" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4m0 4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
                        </svg>
                        <span>{{ session('error') }}</span>
                    </div>
                @endif
            </div>

            <!-- Admin Main Body -->
            <main class="flex-1 max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-6 w-full">
                @yield('content')
            </main>

            <!-- Admin Footer -->
            <footer class="mt-auto border-t border-slate-200 dark:border-slate-800 bg-white dark:bg-slate-950 py-4 text-center text-xs text-slate-500">
                Administrative Session Protected &bull; Access Audit Active
            </footer>
        </div>
    </div>

    <!-- Client Scripts for Sidebar & Theme Engine -->
    <script>
        // Toggle mobile drawer
        function toggleSidebar(open) {
            const drawer = document.getElementById('sidebar-drawer');
            const backdrop = document.getElementById('sidebar-backdrop');
            if (open) {
                drawer.classList.remove('-translate-x-full');
                backdrop.classList.remove('opacity-0', 'pointer-events-none');
                backdrop.classList.add('opacity-100');
            } else {
                drawer.classList.add('-translate-x-full');
                backdrop.classList.add('opacity-0', 'pointer-events-none');
                backdrop.classList.remove('opacity-100');
            }
        }

        // Toggle Service Requests dropdown in sidebar
        function toggleServiceRequestsDropdown() {
            const subnav = document.getElementById('service-requests-subnav');
            const chevron = document.getElementById('service-requests-chevron');
            if (subnav) subnav.classList.toggle('hidden');
            if (chevron) chevron.classList.toggle('rotate-180');
        }

        // Toggle Users dropdown in sidebar
        function toggleUsersDropdown() {
            const subnav = document.getElementById('users-subnav');
            const chevron = document.getElementById('users-chevron');
            if (subnav) subnav.classList.toggle('hidden');
            if (chevron) chevron.classList.toggle('rotate-180');
        }

        // Toggle Wallet & Finance dropdown in sidebar
        function toggleFinanceDropdown() {
            const subnav = document.getElementById('finance-subnav');
            const chevron = document.getElementById('finance-chevron');
            if (subnav) subnav.classList.toggle('hidden');
            if (chevron) chevron.classList.toggle('rotate-180');
        }

        // Toggle Transactions dropdown in sidebar
        function toggleTransactionsDropdown() {
            const subnav = document.getElementById('transactions-subnav');
            const chevron = document.getElementById('transactions-chevron');
            if (subnav) subnav.classList.toggle('hidden');
            if (chevron) chevron.classList.toggle('rotate-180');
        }

        // Toggle Charges dropdown in sidebar
        function toggleChargesDropdown() {
            const subnav = document.getElementById('charges-subnav');
            const chevron = document.getElementById('charges-chevron');
            if (subnav) subnav.classList.toggle('hidden');
            if (chevron) chevron.classList.toggle('rotate-180');
        }

        // ==========================================
        // GLOBAL INTERNAL COMMS & CALL LISTENER
        // ==========================================
        let incomingAudioCtx = null;
        let ringInterval = null;
        let currentRingingCallId = null;

        function playRingtone() {
            try {
                if (!incomingAudioCtx) {
                    incomingAudioCtx = new (window.AudioContext || window.webkitAudioContext)();
                }
                if (incomingAudioCtx.state === 'suspended') {
                    incomingAudioCtx.resume();
                }
                const osc = incomingAudioCtx.createOscillator();
                const gain = incomingAudioCtx.createGain();
                osc.type = 'sine';
                osc.frequency.setValueAtTime(440, incomingAudioCtx.currentTime);
                gain.gain.setValueAtTime(0.05, incomingAudioCtx.currentTime);
                gain.gain.exponentialRampToValueAtTime(0.0001, incomingAudioCtx.currentTime + 0.8);
                osc.connect(gain);
                gain.connect(incomingAudioCtx.destination);
                osc.start();
                osc.stop(incomingAudioCtx.currentTime + 0.8);
            } catch (e) {
                // AudioContext autoplay restrictions or disabled audio
            }
        }

        function showIncomingCallModal(call) {
            currentRingingCallId = call.call_id;
            const modal = document.getElementById('global-incoming-call-modal');
            const callerName = document.getElementById('incoming-caller-name');
            const callerRole = document.getElementById('incoming-caller-role');
            const typeBadge = document.getElementById('incoming-call-type-badge');

            if (callerName) callerName.textContent = call.caller.name;
            if (callerRole) callerRole.textContent = call.caller.role;
            if (typeBadge) {
                typeBadge.textContent = call.is_support ? 'INCOMING CUSTOMER SUPPORT CALL' : 'INCOMING INTERNAL CALL';
                typeBadge.className = call.is_support
                    ? 'text-[10px] font-bold uppercase tracking-widest text-emerald-600 dark:text-emerald-400'
                    : 'text-[10px] font-bold uppercase tracking-widest text-indigo-600 dark:text-indigo-400';
            }
            if (modal) modal.classList.remove('hidden');

            if (!ringInterval) {
                playRingtone();
                ringInterval = setInterval(playRingtone, 2500);
            }
        }

        function hideIncomingCallModal() {
            const modal = document.getElementById('global-incoming-call-modal');
            if (modal) modal.classList.add('hidden');
            if (ringInterval) {
                clearInterval(ringInterval);
                ringInterval = null;
            }
            currentRingingCallId = null;
        }

        function acceptIncomingCall() {
            if (!currentRingingCallId) return;
            const callId = currentRingingCallId;
            fetch(`/admin/chat/calls/${callId}/status`, {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content,
                    'Accept': 'application/json'
                },
                body: JSON.stringify({ action: 'accept' })
            }).then(() => {
                hideIncomingCallModal();
                window.location.href = `{{ route('admin.chat.index') }}?active_call=${callId}`;
            });
        }

        function declineIncomingCall() {
            if (!currentRingingCallId) return;
            const callId = currentRingingCallId;
            fetch(`/admin/chat/calls/${callId}/status`, {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content,
                    'Accept': 'application/json'
                },
                body: JSON.stringify({ action: 'decline' })
            }).finally(() => {
                hideIncomingCallModal();
            });
        }

        // Periodic Background Poller (Calls & Unread count)
        function pollCommsStatus() {
            fetch('{{ route('admin.chat.calls.check') }}', {
                headers: { 'Accept': 'application/json' }
            })
            .then(res => res.json())
            .then(data => {
                // 1. Update unread chat counter badge in sidebar
                const badge = document.getElementById('global-unread-chat-badge');
                if (badge) {
                    if (data.total_unread > 0) {
                        badge.textContent = data.total_unread;
                        badge.classList.remove('hidden');
                    } else {
                        badge.classList.add('hidden');
                    }
                }

                // 2. Incoming call notification
                if (data.incoming_call && data.incoming_call.status === 'ringing') {
                    if (currentRingingCallId !== data.incoming_call.call_id) {
                        showIncomingCallModal(data.incoming_call);
                    }
                } else {
                    if (currentRingingCallId) {
                        hideIncomingCallModal();
                    }
                }
            })
            .catch(() => {});
        }

        // Start polling every 3 seconds
        setInterval(pollCommsStatus, 3000);
        document.addEventListener('DOMContentLoaded', pollCommsStatus);
    </script>

    <!-- Global Incoming Call Alert Dialog -->
    <div id="global-incoming-call-modal" class="fixed inset-0 z-50 flex items-center justify-center p-4 bg-slate-900/70 backdrop-blur-xs hidden">
        <div class="w-full max-w-sm rounded-2xl bg-white dark:bg-slate-900 border-2 border-indigo-500/40 p-6 shadow-2xl text-center space-y-5 animate-pulse">
            <div class="relative w-20 h-20 mx-auto">
                <div class="absolute inset-0 rounded-full bg-indigo-500/20 animate-ping"></div>
                <div class="relative w-20 h-20 rounded-full bg-indigo-600 text-white flex items-center justify-center text-2xl font-bold shadow-lg shadow-indigo-600/30">
                    <svg class="w-10 h-10 animate-bounce" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 5a2 2 0 012-2h3.28a1 1 0 01.948.684l1.498 4.493a1 1 0 01-.502 1.21l-2.257 1.13a11.042 11.042 0 005.516 5.516l1.13-2.257a1 1 0 011.21-.502l4.493 1.498a1 1 0 01.684.949V19a2 2 0 01-2 2h-1C9.716 21 3 14.284 3 6V5z" />
                    </svg>
                </div>
            </div>

            <div>
                <span id="incoming-call-type-badge" class="text-[10px] font-bold uppercase tracking-widest text-indigo-600 dark:text-indigo-400">
                    INCOMING INTERNAL CALL
                </span>
                <h3 id="incoming-caller-name" class="text-lg font-extrabold text-slate-900 dark:text-white mt-1">
                    Staff Member
                </h3>
                <p id="incoming-caller-role" class="text-xs font-semibold text-slate-500 dark:text-slate-400">
                    Administrative Staff
                </p>
            </div>

            <div class="flex items-center justify-center gap-3 pt-2">
                <button type="button"
                        onclick="declineIncomingCall()"
                        class="flex-1 px-4 py-2.5 rounded-xl text-xs font-bold text-red-600 dark:text-red-400 bg-red-50 dark:bg-red-500/10 hover:bg-red-100 dark:hover:bg-red-500/20 border border-red-200 dark:border-red-500/30 transition cursor-pointer">
                    Decline
                </button>
                <button type="button"
                        onclick="acceptIncomingCall()"
                        class="flex-1 px-4 py-2.5 rounded-xl text-xs font-bold text-white bg-emerald-600 hover:bg-emerald-500 shadow-md shadow-emerald-600/30 transition cursor-pointer flex items-center justify-center gap-2">
                    <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 5a2 2 0 012-2h3.28a1 1 0 01.948.684l1.498 4.493a1 1 0 01-.502 1.21l-2.257 1.13a11.042 11.042 0 005.516 5.516l1.13-2.257a1 1 0 011.21-.502l4.493 1.498a1 1 0 01.684.949V19a2 2 0 01-2 2h-1C9.716 21 3 14.284 3 6V5z" />
                    </svg>
                    <span>Answer</span>
                </button>
            </div>
        </div>
    </div>

    @include('components.toast')
</body>
</html>
