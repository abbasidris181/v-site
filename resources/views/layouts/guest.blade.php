<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}" class="h-full">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">

    <title>{{ config('app.name', 'Verification Portal') }} - @yield('title', 'Secure Access')</title>

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
<body class="min-h-full font-sans antialiased text-slate-900 dark:text-slate-100 bg-slate-50 dark:bg-slate-950 transition-colors duration-200">
    <!-- Top Right Theme Toggle (Light, Dark, System) -->
    <div class="absolute top-4 right-4 z-50">
        <x-theme-toggle />
    </div>

    <div class="min-h-full flex flex-col justify-center py-12 sm:px-6 lg:px-8">
        <div class="sm:mx-auto sm:w-full sm:max-w-md text-center">
            <!-- Brand Logo -->
            @if(!empty($siteLogoUrl))
                <div class="inline-flex items-center justify-center mb-4">
                    <img src="{{ $siteLogoUrl }}" alt="{{ $siteName ?? 'Logo' }}" class="h-16 w-auto max-w-[220px] object-contain rounded-2xl shadow-xl shadow-emerald-500/10 ring-1 ring-white/20">
                </div>
            @else
                <div class="inline-flex items-center justify-center w-16 h-16 rounded-2xl bg-gradient-to-tr from-emerald-600 to-teal-400 shadow-xl shadow-emerald-500/20 mb-4 ring-1 ring-white/20">
                    <svg class="w-8 h-8 text-white" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M9 12.75L11.25 15 15 9.75m-3-7.036A11.959 11.959 0 013.598 6 11.99 11.99 0 003 9.749c0 5.592 3.824 10.29 9 11.623 5.176-1.332 9-6.03 9-11.622 0-1.31-.21-2.571-.598-3.751h-.152c-3.196 0-6.1-1.248-8.25-3.285z" />
                    </svg>
                </div>
            @endif
            <h2 class="text-3xl font-bold tracking-tight text-slate-900 dark:text-white">
                {{ $siteName ?? 'V-PORTAL' }}
            </h2>
            <p class="mt-1 text-sm text-slate-500 dark:text-slate-400 font-medium">
                {{ $siteTagline ?? 'Nigerian Identity Verification & Service Engine' }}
            </p>
        </div>

        <div class="mt-8 sm:mx-auto sm:w-full sm:max-w-xl">
            <div class="bg-white/95 dark:bg-slate-900/80 backdrop-blur-xl py-8 px-6 shadow-2xl shadow-slate-200/50 dark:shadow-black/50 sm:rounded-2xl sm:px-10 border border-slate-200 dark:border-slate-800">
                <!-- Status Notifications -->
                @if (session('status'))
                    <div class="mb-6 rounded-xl bg-emerald-50 dark:bg-emerald-500/10 border border-emerald-200 dark:border-emerald-500/30 p-4 text-sm text-emerald-800 dark:text-emerald-300 flex items-center gap-3">
                        <svg class="w-5 h-5 flex-shrink-0 text-emerald-600 dark:text-emerald-400" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z" />
                        </svg>
                        <span>{{ session('status') }}</span>
                    </div>
                @endif

                @if ($errors->any())
                    <div class="mb-6 rounded-xl bg-red-50 dark:bg-red-500/10 border border-red-200 dark:border-red-500/30 p-4 text-sm text-red-800 dark:text-red-300">
                        <div class="font-semibold mb-1 flex items-center gap-2">
                            <svg class="w-5 h-5 text-red-600 dark:text-red-400 flex-shrink-0" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z" />
                            </svg>
                            <span>Please review the errors below:</span>
                        </div>
                        <ul class="list-disc list-inside space-y-1 text-xs text-red-600 dark:text-red-400 pl-1">
                            @foreach ($errors->all() as $error)
                                <li>{{ $error }}</li>
                            @endforeach
                        </ul>
                    </div>
                @endif

                @yield('content')
            </div>

            <div class="mt-6 text-center text-xs text-slate-400 dark:text-slate-500">
                &copy; {{ date('Y') }} V-Portal. Secure Nigerian Identity Infrastructure.
            </div>
        </div>
    </div>


</body>
</html>
