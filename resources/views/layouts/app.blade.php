<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}" class="h-full">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">

    <title>{{ config('app.name', 'Verification Portal') }} - @yield('title', 'Dashboard')</title>

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
<body class="min-h-full font-sans antialiased bg-slate-50 dark:bg-slate-950 text-slate-900 dark:text-slate-100 flex flex-col transition-colors duration-200">
    <div class="min-h-screen flex flex-col md:flex-row w-full">
        
        <!-- Mobile Sidebar Backdrop Overlay -->
        <div id="sidebar-backdrop"
             onclick="toggleSidebar(false)"
             class="fixed inset-0 z-40 bg-slate-900/60 backdrop-blur-xs transition-opacity duration-300 opacity-0 pointer-events-none md:hidden">
        </div>

        <!-- ==========================================
             OFFICIAL SIDEBAR NAVIGATION (DESKTOP & MOBILE)
             ========================================== -->
        <aside id="sidebar-drawer"
               class="fixed inset-y-0 left-0 z-50 w-72 bg-white dark:bg-slate-900 border-r border-slate-200/80 dark:border-slate-800 flex flex-col justify-between transform -translate-x-full md:translate-x-0 transition-transform duration-300 ease-in-out shadow-xl md:shadow-none">
            
            <!-- Top Content Container -->
            <div class="flex-1 overflow-y-auto px-4 py-5 space-y-5">
                
                <!-- 1. Header: LOGO & BRAND -->
                <div class="flex items-center justify-between px-2">
                    <a href="{{ route('dashboard') }}" class="flex items-center gap-3 group">
                        @if(!empty($siteLogoUrl))
                            <img src="{{ $siteLogoUrl }}" alt="{{ $siteName ?? 'Logo' }}" class="w-10 h-10 rounded-xl object-contain shadow-md shadow-emerald-500/20 ring-1 ring-white/20 group-hover:scale-105 transition-all">
                        @else
                            <div class="w-10 h-10 rounded-xl bg-gradient-to-tr from-emerald-600 via-emerald-500 to-teal-400 flex items-center justify-center text-white shadow-lg shadow-emerald-500/25 ring-1 ring-white/20 group-hover:scale-105 transition-all">
                                <svg class="w-6 h-6" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                                    <path stroke-linecap="round" stroke-linejoin="round" d="M9 12.75L11.25 15 15 9.75m-3-7.036A11.959 11.959 0 013.598 6 11.99 11.99 0 003 9.749c0 5.592 3.824 10.29 9 11.623 5.176-1.332 9-6.03 9-11.622 0-1.31-.21-2.571-.598-3.751h-.152c-3.196 0-6.1-1.248-8.25-3.285z" />
                                </svg>
                            </div>
                        @endif
                        <div>
                            <span class="text-xl font-black text-slate-900 dark:text-white tracking-tight">{{ $siteName ?? 'V-SITE' }}</span>
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

                <!-- 2. Navigation Links List -->
                <nav class="space-y-1 text-sm font-medium">
                    <!-- ▣ Dashboard -->
                    <a href="{{ route('dashboard') }}"
                       class="flex items-center gap-3 px-3.5 py-2.5 rounded-xl transition-all {{ request()->routeIs('dashboard') ? 'bg-emerald-50 dark:bg-emerald-500/10 text-emerald-700 dark:text-emerald-400 font-bold border border-emerald-200/80 dark:border-emerald-500/20 shadow-xs' : 'text-slate-600 dark:text-slate-300 hover:text-slate-900 dark:hover:text-white hover:bg-slate-100 dark:hover:bg-slate-800/60' }}">
                        <svg class="w-5 h-5 flex-shrink-0 {{ request()->routeIs('dashboard') ? 'text-emerald-600 dark:text-emerald-400' : 'text-slate-400 group-hover:text-slate-500' }}" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6a2 2 0 012-2h2a2 2 0 012 2v2a2 2 0 01-2 2H6a2 2 0 01-2-2V6zM14 6a2 2 0 012-2h2a2 2 0 012 2v2a2 2 0 01-2 2h-2a2 2 0 01-2-2V6zM4 16a2 2 0 012-2h2a2 2 0 012 2v2a2 2 0 01-2 2H6a2 2 0 01-2-2v-2zM14 16a2 2 0 012-2h2a2 2 0 012 2v2a2 2 0 01-2 2h-2a2 2 0 01-2-2v-2z" />
                        </svg>
                        <span>Dashboard</span>
                    </a>

                    <!-- ◇ Services (Collapsible Submenu) -->
                    <div>
                        <button type="button"
                                onclick="toggleServicesMenu()"
                                class="w-full flex items-center justify-between px-3.5 py-2.5 rounded-xl transition-all cursor-pointer {{ request()->routeIs('services.*') ? 'text-emerald-700 dark:text-emerald-400 font-bold bg-emerald-50/50 dark:bg-emerald-500/5' : 'text-slate-600 dark:text-slate-300 hover:text-slate-900 dark:hover:text-white hover:bg-slate-100 dark:hover:bg-slate-800/60' }}">
                            <div class="flex items-center gap-3">
                                <svg class="w-5 h-5 flex-shrink-0 {{ request()->routeIs('services.*') ? 'text-emerald-600 dark:text-emerald-400' : 'text-slate-400' }}" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M20 7l-8-4-8 4m16 0l-8 4m8-4v10l-8 4m0-10L4 7m8 4v10M4 7v10l8 4" />
                                </svg>
                                <span>Services</span>
                            </div>
                            <!-- Chevron icon › -->
                            <svg id="services-chevron"
                                 class="w-4 h-4 text-slate-400 transition-transform duration-200 {{ request()->routeIs('services.*') ? 'rotate-90 text-emerald-600 dark:text-emerald-400' : '' }}"
                                 fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7" />
                            </svg>
                        </button>

                        <!-- Services Submenu Items -->
                        <div id="services-submenu" class="pl-7 pr-2 mt-1 space-y-1 {{ request()->routeIs('services.*') ? 'block' : 'hidden' }}">
                            @php
                                $navServices = $sidebarServices ?? (\Illuminate\Support\Facades\Schema::hasTable('services') ? \App\Models\Service::where('is_active', true)->orderBy('sort_order')->orderBy('id')->get() : collect());
                            @endphp
                            @forelse($navServices as $serviceItem)
                                @php
                                    $isCurrentService = request()->is('services/' . $serviceItem->slug . '*');
                                @endphp
                                <a href="{{ route('services.show', $serviceItem->slug) }}"
                                   class="flex items-center gap-2 px-3 py-2 rounded-lg text-xs transition-colors {{ $isCurrentService ? 'bg-emerald-50 dark:bg-emerald-500/10 text-emerald-700 dark:text-emerald-400 font-bold' : 'text-slate-500 dark:text-slate-400 hover:text-slate-900 dark:hover:text-white hover:bg-slate-100 dark:hover:bg-slate-800/50' }}">
                                    <span class="w-1.5 h-1.5 rounded-full {{ $isCurrentService ? 'bg-emerald-500' : 'bg-slate-300 dark:bg-slate-600' }}"></span>
                                    <span>{{ $serviceItem->name }}</span>
                                </a>
                            @empty
                                <span class="block px-3 py-2 text-xs text-slate-400 italic">No active services</span>
                            @endforelse
                        </div>
                    </div>

                    <!-- ↔ Transactions -->
                    <a href="{{ route('wallet.transactions') }}"
                       class="flex items-center gap-3 px-3.5 py-2.5 rounded-xl transition-all {{ request()->routeIs('wallet.transactions') || request()->routeIs('transactions.*') ? 'bg-emerald-50 dark:bg-emerald-500/10 text-emerald-700 dark:text-emerald-400 font-bold border border-emerald-200/80 dark:border-emerald-500/20 shadow-xs' : 'text-slate-600 dark:text-slate-300 hover:text-slate-900 dark:hover:text-white hover:bg-slate-100 dark:hover:bg-slate-800/60' }}">
                        <svg class="w-5 h-5 flex-shrink-0 {{ request()->routeIs('wallet.transactions') || request()->routeIs('transactions.*') ? 'text-emerald-600 dark:text-emerald-400' : 'text-slate-400' }}" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7h12m0 0l-4-4m4 4l-4 4m0 6H4m0 0l4 4m-4-4l4-4" />
                        </svg>
                        <span>Transactions</span>
                    </a>

                    <!-- ↗ Send Fund by Email or Phone -->
                    <a href="{{ route('wallet.send-fund') }}"
                       class="flex items-center gap-3 px-3.5 py-2.5 rounded-xl transition-all {{ request()->routeIs('wallet.send-fund') || request()->is('wallet/transfer*') ? 'bg-emerald-50 dark:bg-emerald-500/10 text-emerald-700 dark:text-emerald-400 font-bold border border-emerald-200/80 dark:border-emerald-500/20 shadow-xs' : 'text-slate-600 dark:text-slate-300 hover:text-slate-900 dark:hover:text-white hover:bg-slate-100 dark:hover:bg-slate-800/60' }}">
                        <svg class="w-5 h-5 flex-shrink-0 {{ request()->routeIs('wallet.send-fund') || request()->is('wallet/transfer*') ? 'text-emerald-600 dark:text-emerald-400' : 'text-slate-400' }}" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 19l9 2-9-18-9 18 9-2zm0 0v-8" />
                        </svg>
                        <span>Send Fund by Email or Phone</span>
                    </a>

                    <!-- ♙ Profile -->
                    <a href="{{ route('profile.index') }}"
                       class="flex items-center gap-3 px-3.5 py-2.5 rounded-xl transition-all {{ request()->routeIs('profile.*') ? 'bg-emerald-50 dark:bg-emerald-500/10 text-emerald-700 dark:text-emerald-400 font-bold border border-emerald-200/80 dark:border-emerald-500/20 shadow-xs' : 'text-slate-600 dark:text-slate-300 hover:text-slate-900 dark:hover:text-white hover:bg-slate-100 dark:hover:bg-slate-800/60' }}">
                        <svg class="w-5 h-5 flex-shrink-0 {{ request()->routeIs('profile.*') ? 'text-emerald-600 dark:text-emerald-400' : 'text-slate-400' }}" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z" />
                        </svg>
                        <span>Profile</span>
                    </a>

                    <!-- ? Support -->
                    <a href="{{ route('support.index') }}"
                       class="flex items-center gap-3 px-3.5 py-2.5 rounded-xl transition-all {{ request()->routeIs('support.*') ? 'bg-emerald-50 dark:bg-emerald-500/10 text-emerald-700 dark:text-emerald-400 font-bold border border-emerald-200/80 dark:border-emerald-500/20 shadow-xs' : 'text-slate-600 dark:text-slate-300 hover:text-slate-900 dark:hover:text-white hover:bg-slate-100 dark:hover:bg-slate-800/60' }}">
                        <svg class="w-5 h-5 flex-shrink-0 {{ request()->routeIs('support.*') ? 'text-emerald-600 dark:text-emerald-400' : 'text-slate-400' }}" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8.228 9c.549-1.165 2.03-2 3.772-2 2.21 0 4 1.343 4 3 0 1.4-1.278 2.575-3.006 2.907-.542.104-.994.54-.994 1.093m0 3h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
                        </svg>
                        <span>Support</span>
                    </a>

                    <!-- 💬 Live Chat & Loudspeaker Calling (WhatsApp Style - Sidebar Only) -->
                    <button type="button"
                            onclick="toggleSupportChat(true)"
                            id="sidebar-live-chat-btn"
                            class="w-full flex items-center justify-between px-3.5 py-2.5 rounded-xl transition-all text-slate-600 dark:text-slate-300 hover:text-slate-900 dark:hover:text-white hover:bg-slate-100 dark:hover:bg-slate-800/60 cursor-pointer text-left group">
                        <div class="flex items-center gap-3">
                            <div class="relative flex-shrink-0">
                                <!-- Call Handset Icon -->
                                <svg id="sidebar-call-icon" class="w-5 h-5 text-emerald-600 dark:text-emerald-400 group-hover:scale-110 transition-transform" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 5a2 2 0 012-2h3.28a1 1 0 01.948.684l1.498 4.493a1 1 0 01-.502 1.21l-2.257 1.13a11.042 11.042 0 005.516 5.516l1.13-2.257a1 1 0 011.21-.502l4.493 1.498a1 1 0 01.684.949V19a2 2 0 01-2 2h-1C9.716 21 3 14.284 3 6V5z" />
                                </svg>
                                <span id="sidebar-chat-status-dot" class="absolute -bottom-0.5 -right-0.5 w-2.5 h-2.5 rounded-full bg-slate-400 ring-2 ring-white dark:ring-slate-900"></span>
                            </div>
                            <span class="font-medium">chat with admin</span>
                        </div>
                        <div class="flex items-center gap-1.5">
                            <!-- Quick Loudspeaker Voice Call Button -->
                            <span onclick="event.stopPropagation(); initiateUserSupportCall();"
                                  id="sidebar-quick-call-btn"
                                  title="Voice Call Admin with Loudspeaker"
                                  class="p-1 rounded-lg text-emerald-600 dark:text-emerald-400 hover:bg-emerald-100 dark:hover:bg-emerald-950/60 transition cursor-pointer">
                                <svg class="w-3.5 h-3.5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 5a2 2 0 012-2h3.28a1 1 0 01.948.684l1.498 4.493a1 1 0 01-.502 1.21l-2.257 1.13a11.042 11.042 0 005.516 5.516l1.13-2.257a1 1 0 011.21-.502l4.493 1.498a1 1 0 01.684.949V19a2 2 0 01-2 2h-1C9.716 21 3 14.284 3 6V5z" />
                                </svg>
                            </span>
                            <span id="sidebar-chat-status-text" class="text-[10px] font-bold px-2 py-0.5 rounded-full bg-slate-100 dark:bg-slate-800 text-slate-500 dark:text-slate-400">
                                offline
                            </span>
                            <span id="sidebar-chat-unread-badge" class="hidden px-1.5 py-0.2 rounded-full text-[10px] font-extrabold bg-emerald-600 text-white">
                                0
                            </span>
                        </div>
                    </button>
                </nav>
            </div>

            <!-- Bottom Content (Divider, Admin Gating & Logout) -->
            <div class="p-4 border-t border-slate-200/80 dark:border-slate-800 space-y-1.5">
                
                <!-- ⚙ Admin: Accessible EXCLUSIVELY to Super Admin, Admin, and Staff (SRS Section 9) -->
                @if(auth()->check() && auth()->user()->hasAdminBackendAccess())
                    <a href="{{ route('admin.dashboard') }}"
                       class="flex items-center gap-3 px-3.5 py-2.5 rounded-xl text-sm font-semibold text-indigo-700 dark:text-indigo-300 bg-indigo-50/80 dark:bg-indigo-500/10 hover:bg-indigo-100 dark:hover:bg-indigo-500/20 border border-indigo-200/80 dark:border-indigo-500/30 transition-all shadow-xs group">
                        <svg class="w-5 h-5 text-indigo-600 dark:text-indigo-400 group-hover:rotate-45 transition-transform" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10.325 4.317c.426-1.756 2.924-1.756 3.35 0a1.724 1.724 0 002.573 1.066c1.543-.94 3.31.826 2.37 2.37a1.724 1.724 0 001.065 2.572c1.756.426 1.756 2.924 0 3.35a1.724 1.724 0 00-1.066 2.573c.94 1.543-.826 3.31-2.37 2.37a1.724 1.724 0 00-2.572 1.065c-.426 1.756-2.924 1.756-3.35 0a1.724 1.724 0 00-2.573-1.066c-1.543.94-3.31-.826-2.37-2.37a1.724 1.724 0 00-1.065-2.572c-1.756-.426-1.756-2.924 0-3.35a1.724 1.724 0 001.066-2.573c-.94-1.543.826-3.31 2.37-2.37.996.608 2.296.07 2.572-1.065z" />
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z" />
                        </svg>
                        <span>Admin Dashboard</span>
                    </a>
                @endif

                <!-- ⇥ Logout -->
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
            <header class="sticky top-0 z-30 bg-white/95 dark:bg-slate-900/90 backdrop-blur-md border-b border-slate-200/80 dark:border-slate-800 transition-colors">
                <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
                    <div class="flex items-center justify-between h-16">
                        
                        <!-- Left: Mobile Hamburger & Page Title -->
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

                            <div class="hidden sm:block">
                                <span class="text-xs font-semibold text-slate-400 uppercase tracking-wider">PORTAL WORKSPACE</span>
                                <div class="text-sm font-bold text-slate-900 dark:text-white">
                                    @yield('title', 'Overview')
                                </div>
                            </div>
                        </div>

                        <!-- Right: Wallet Balance Pill & Theme Toggle -->
                        <div class="flex items-center gap-2.5 sm:gap-3.5">
                            
                            <!-- Theme Toggle (Light, Dark, System) -->
                            <x-theme-toggle />

                            <!-- User Profile Shortcut -->
                            <a href="{{ route('profile.index') }}"
                               class="hidden sm:flex items-center gap-2 pl-2 border-l border-slate-200 dark:border-slate-800">
                                <div class="w-8 h-8 rounded-lg bg-slate-100 dark:bg-slate-800 text-slate-700 dark:text-slate-300 flex items-center justify-center font-bold text-xs">
                                    {{ strtoupper(substr(auth()->user()->first_name, 0, 1)) }}
                                </div>
                            </a>
                        </div>
                    </div>
                </div>
            </header>

            <!-- Global Alerts & Flash Messages -->
            @if (session('error'))
                <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 w-full pt-4">
                    <div class="mb-4 rounded-2xl bg-red-50 dark:bg-red-500/10 border border-red-200 dark:border-red-500/30 p-4 text-sm text-red-800 dark:text-red-300 flex items-center gap-3 shadow-xs">
                        <svg class="w-5 h-5 flex-shrink-0 text-red-600 dark:text-red-400" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4m0 4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
                        </svg>
                        <span>{{ session('error') }}</span>
                    </div>
                </div>
            @endif

            <!-- Main Content Container -->
            <main class="flex-1 max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-6 w-full">
                @yield('content')
            </main>

            <!-- Footer -->
            <footer class="mt-auto border-t border-slate-200 dark:border-slate-800 bg-white dark:bg-slate-950 py-5 text-center text-xs text-slate-500 transition-colors">
                <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 flex flex-col sm:flex-row justify-between items-center gap-2">
                    <span>&copy; {{ date('Y') }} V-SITE. All rights reserved.</span>
                    <span class="text-slate-400 dark:text-slate-600"></span>
                </div>
            </footer>
        </div>
    </div>

    <!-- Client Scripts for Sidebar, Submenu & Theme Engine -->
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

        // Toggle services dropdown menu
        function toggleServicesMenu() {
            const menu = document.getElementById('services-submenu');
            const chevron = document.getElementById('services-chevron');
            if (menu.classList.contains('hidden')) {
                menu.classList.remove('hidden');
                menu.classList.add('block');
                chevron.classList.add('rotate-90', 'text-emerald-600', 'dark:text-emerald-400');
            } else {
                menu.classList.add('hidden');
                menu.classList.remove('block');
                chevron.classList.remove('rotate-90', 'text-emerald-600', 'dark:text-emerald-400');
            }
        }
    </script>

    @auth
        @include('components.user-support-widget')
    @endauth

    @include('components.toast')
</body>
</html>
