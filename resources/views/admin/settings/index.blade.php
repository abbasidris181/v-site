@extends('layouts.admin')

@section('content')
<div class="space-y-6">

    <!-- ==========================================
         PAGE HEADER
         ========================================== -->
    <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-4">
        <div>
            <div class="flex items-center gap-2">
                <span class="text-xs font-bold uppercase tracking-widest text-indigo-700 dark:text-indigo-400 bg-indigo-50 dark:bg-indigo-500/10 px-3 py-1 rounded-full border border-indigo-200 dark:border-indigo-500/30">
                    Platform Administration
                </span>
                <span class="text-xs font-semibold text-slate-500 dark:text-slate-400">
                    &bull; Core Configuration
                </span>
            </div>
            <h1 class="mt-2 text-2xl sm:text-3xl font-extrabold text-slate-900 dark:text-white tracking-tight">
                System Settings
            </h1>
            <p class="mt-1 text-sm text-slate-500 dark:text-slate-400">
                Site Settings &bull; Configure platform identity, custom brand logo, typography, official customer support channels, and operational thresholds.
            </p>
        </div>

        <div class="flex items-center gap-3">
            <a href="{{ route('admin.charges.index') }}"
               class="inline-flex items-center gap-2 px-4 py-2 rounded-xl text-xs font-bold text-indigo-700 dark:text-indigo-300 bg-indigo-50 dark:bg-indigo-500/10 hover:bg-indigo-100 dark:hover:bg-indigo-500/20 border border-indigo-200 dark:border-indigo-500/30 transition shadow-xs">
                <span>Manage Service Charges</span>
                <span>&rarr;</span>
            </a>
        </div>
    </div>

    <!-- ==========================================
         VALIDATION ERRORS
         ========================================== -->
    @if ($errors->any())
        <div class="p-4 rounded-2xl bg-red-50 dark:bg-red-500/10 border border-red-200 dark:border-red-500/30 text-sm text-red-800 dark:text-red-300 shadow-xs">
            <div class="font-bold flex items-center gap-2 mb-1">
                <svg class="w-5 h-5 text-red-500 flex-shrink-0" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4m0 4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
                </svg>
                <span>Please correct the errors before saving:</span>
            </div>
            <ul class="list-disc pl-7 space-y-1 text-xs">
                @foreach ($errors->all() as $error)
                    <li>{{ $error }}</li>
                @endforeach
            </ul>
        </div>
    @endif

    <!-- =========================================================
         SITE CONFIGURATIONS FORM
         ========================================================= -->
    <form method="POST" action="{{ route('admin.settings.configurations') }}" enctype="multipart/form-data" class="space-y-6">
        @csrf
        @method('PUT')

        <!-- Section 1: Platform Branding -->
        <div class="rounded-2xl bg-white dark:bg-slate-900 border border-slate-200/80 dark:border-slate-800 p-6 shadow-sm space-y-5">
            <div class="border-b border-slate-100 dark:border-slate-800 pb-4">
                <h2 class="text-base font-extrabold text-slate-900 dark:text-white flex items-center gap-2">
                    <svg class="w-5 h-5 text-indigo-600 dark:text-indigo-400" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-5m-9 0H3m2 0h5M9 7h1m-1 4h1m4-4h1m-1 4h1m-5 10v-5a1 1 0 011-1h2a1 1 0 011 1v5m-4 0h4" />
                    </svg>
                    <span>Platform Identity &amp; Branding</span>
                </h2>
                <p class="text-xs text-slate-500 dark:text-slate-400 mt-1">
                    Application name, dynamic brand logo, header labels, and public customer-facing titles.
                </p>
            </div>

            <!-- Logo Upload & Realtime Preview Component -->
            <div class="p-4 sm:p-5 rounded-2xl bg-slate-50/80 dark:bg-slate-800/40 border border-slate-200/80 dark:border-slate-700/80 space-y-4">
                <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-2">
                    <div>
                        <label class="block text-xs font-bold text-slate-700 dark:text-slate-300 uppercase tracking-wider">
                            Platform Logo
                        </label>
                        <p class="text-xs text-slate-500 dark:text-slate-400 mt-0.5">
                            Upload a dynamic brand logo image (PNG, JPG, SVG, WebP up to 2MB). Replaces the default badge across all headers and navigation.
                        </p>
                    </div>
                    @if(!empty($settings['site_logo']))
                        <div class="flex items-center gap-2">
                            <label class="inline-flex items-center gap-2 text-xs font-semibold text-red-600 dark:text-red-400 cursor-pointer hover:underline">
                                <input type="checkbox" name="remove_logo" value="1" id="remove_logo_checkbox" onchange="toggleRemoveLogo(this.checked)" class="w-4 h-4 rounded text-red-600 focus:ring-red-500 border-slate-300 dark:border-slate-600">
                                <span>Remove Custom Logo (Reset to Default)</span>
                            </label>
                        </div>
                    @endif
                </div>

                <div class="flex flex-col sm:flex-row items-center sm:items-start gap-6 pt-2">
                    <!-- Current / Live Preview Box -->
                    <div class="flex flex-col items-center gap-2 flex-shrink-0">
                        <div id="logo-preview-container" class="w-24 h-24 sm:w-28 sm:h-28 rounded-2xl bg-white dark:bg-slate-900 border-2 border-dashed border-slate-300 dark:border-slate-700 flex items-center justify-center p-2 overflow-hidden shadow-inner relative group">
                            @if(!empty($settings['site_logo']))
                                <img id="logo-preview-img" src="{{ asset('storage/' . $settings['site_logo']) }}" data-original-src="{{ asset('storage/' . $settings['site_logo']) }}" alt="Site Logo" class="w-full h-full object-contain">
                                <div id="logo-default-placeholder" class="hidden w-12 h-12 rounded-xl bg-gradient-to-tr from-emerald-600 to-teal-400 flex items-center justify-center text-white shadow-md">
                                    <svg class="w-6 h-6" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                                        <path stroke-linecap="round" stroke-linejoin="round" d="M9 12.75L11.25 15 15 9.75m-3-7.036A11.959 11.959 0 013.598 6 11.99 11.99 0 003 9.749c0 5.592 3.824 10.29 9 11.623 5.176-1.332 9-6.03 9-11.622 0-1.31-.21-2.571-.598-3.751h-.152c-3.196 0-6.1-1.248-8.25-3.285z" />
                                    </svg>
                                </div>
                            @else
                                <img id="logo-preview-img" src="" alt="Site Logo Preview" class="hidden w-full h-full object-contain">
                                <div id="logo-default-placeholder" class="w-12 h-12 rounded-xl bg-gradient-to-tr from-emerald-600 to-teal-400 flex items-center justify-center text-white shadow-md">
                                    <svg class="w-6 h-6" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                                        <path stroke-linecap="round" stroke-linejoin="round" d="M9 12.75L11.25 15 15 9.75m-3-7.036A11.959 11.959 0 013.598 6 11.99 11.99 0 003 9.749c0 5.592 3.824 10.29 9 11.623 5.176-1.332 9-6.03 9-11.622 0-1.31-.21-2.571-.598-3.751h-.152c-3.196 0-6.1-1.248-8.25-3.285z" />
                                    </svg>
                                </div>
                            @endif
                        </div>
                        <span id="logo-status-label" class="text-[10px] font-bold text-slate-400 uppercase tracking-wider">
                            {{ !empty($settings['site_logo']) ? 'Active Logo' : 'Default Brand' }}
                        </span>
                    </div>

                    <!-- Upload Input & Helper Details -->
                    <div class="flex-1 w-full space-y-3">
                        <div class="relative">
                            <input type="file"
                                   id="site_logo"
                                   name="site_logo"
                                   accept="image/png,image/jpeg,image/jpg,image/svg+xml,image/webp"
                                   onchange="handleLogoSelect(this)"
                                   class="block w-full text-xs text-slate-500 dark:text-slate-400
                                          file:mr-4 file:py-2.5 file:px-4
                                          file:rounded-xl file:border-0
                                          file:text-xs file:font-bold
                                          file:bg-indigo-50 file:text-indigo-700
                                          dark:file:bg-indigo-500/10 dark:file:text-indigo-300
                                          hover:file:bg-indigo-100 dark:hover:file:bg-indigo-500/20
                                          cursor-pointer file:cursor-pointer transition">
                        </div>
                        <div class="flex flex-wrap items-center gap-4 text-[11px] text-slate-500 dark:text-slate-400">
                            <span class="flex items-center gap-1">
                                <svg class="w-3.5 h-3.5 text-emerald-500" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/></svg>
                                PNG, JPG, SVG, WebP
                            </span>
                            <span class="flex items-center gap-1">
                                <svg class="w-3.5 h-3.5 text-emerald-500" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/></svg>
                                Max file size: 2MB
                            </span>
                            <span class="flex items-center gap-1">
                                <svg class="w-3.5 h-3.5 text-emerald-500" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/></svg>
                                Real-time Instant Preview
                            </span>
                        </div>
                        <div id="logo-file-name" class="text-xs font-semibold text-indigo-600 dark:text-indigo-400 hidden"></div>
                    </div>
                </div>
            </div>

            <div class="grid grid-cols-1 md:grid-cols-2 gap-5">
                <!-- Site Name -->
                <div>
                    <label for="site_name" class="block text-xs font-bold text-slate-700 dark:text-slate-300 uppercase tracking-wider mb-2">
                        Platform Name <span class="text-red-500">*</span>
                    </label>
                    <input type="text"
                           id="site_name"
                           name="site_name"
                           value="{{ old('site_name', $settings['site_name'] ?? 'V-SITE') }}"
                           required
                           placeholder="V-SITE"
                           class="w-full px-4 py-2.5 rounded-xl border border-slate-300 dark:border-slate-700 bg-white dark:bg-slate-800 text-sm font-semibold text-slate-900 dark:text-white focus:border-indigo-500 focus:ring-2 focus:ring-indigo-500/20 transition">
                    <span class="text-[11px] text-slate-400 mt-1 block">Displayed on browser tabs, email headers, and portal navigation.</span>
                </div>

                <!-- Site Tagline -->
                <div>
                    <label for="site_tagline" class="block text-xs font-bold text-slate-700 dark:text-slate-300 uppercase tracking-wider mb-2">
                        Site Tagline / Description
                    </label>
                    <input type="text"
                           id="site_tagline"
                           name="site_tagline"
                           value="{{ old('site_tagline', $settings['site_tagline'] ?? '') }}"
                           placeholder="Enterprise Nigerian Identity Verification Infrastructure"
                           class="w-full px-4 py-2.5 rounded-xl border border-slate-300 dark:border-slate-700 bg-white dark:bg-slate-800 text-sm font-medium text-slate-900 dark:text-white focus:border-indigo-500 focus:ring-2 focus:ring-indigo-500/20 transition">
                    <span class="text-[11px] text-slate-400 mt-1 block">Short explanatory slogan used in hero headers and meta descriptions.</span>
                </div>
            </div>
        </div>

        <!-- Section 2: Typography & Global Fonts -->
        <div class="rounded-2xl bg-white dark:bg-slate-900 border border-slate-200/80 dark:border-slate-800 p-6 shadow-sm space-y-5">
            <div class="border-b border-slate-100 dark:border-slate-800 pb-4 flex items-center justify-between flex-wrap gap-2">
                <div>
                    <h2 class="text-base font-extrabold text-slate-900 dark:text-white flex items-center gap-2">
                        <svg class="w-5 h-5 text-indigo-600 dark:text-indigo-400" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6h16M4 12h8m-8 6h16" />
                        </svg>
                        <span>Typography &amp; Global Font Styling</span>
                    </h2>
                    <p class="text-xs text-slate-500 dark:text-slate-400 mt-1">
                        Configure the default font rendered across all user portal pages, administrative panels, and login screens.
                    </p>
                </div>
                <span class="text-xs font-semibold px-2.5 py-1 rounded-full bg-indigo-50 dark:bg-indigo-500/10 text-indigo-700 dark:text-indigo-300 border border-indigo-200 dark:border-indigo-500/30">
                    Default: Roboto &amp; Arial
                </span>
            </div>

            <div class="grid grid-cols-1 md:grid-cols-2 gap-5">
                <!-- Preset Selector -->
                <div>
                    <label for="font_preset_selector" class="block text-xs font-bold text-slate-700 dark:text-slate-300 uppercase tracking-wider mb-2">
                        Choose Font Preset
                    </label>
                    <select id="font_preset_selector"
                            onchange="applyFontPreset(this.value)"
                            class="w-full px-4 py-2.5 rounded-xl border border-slate-300 dark:border-slate-700 bg-white dark:bg-slate-800 text-sm font-medium text-slate-900 dark:text-white focus:border-indigo-500 focus:ring-2 focus:ring-indigo-500/20 transition cursor-pointer">
                        <option value="Roboto, Arial, sans-serif" {{ ($settings['font_family'] ?? '') === 'Roboto, Arial, sans-serif' ? 'selected' : '' }}>Roboto &amp; Arial (Recommended Default)</option>
                        <option value="Arial, sans-serif" {{ ($settings['font_family'] ?? '') === 'Arial, sans-serif' ? 'selected' : '' }}>Arial (Standard System Web-Safe)</option>
                        <option value="'Inter', Arial, sans-serif" {{ ($settings['font_family'] ?? '') === "'Inter', Arial, sans-serif" ? 'selected' : '' }}>Inter &amp; Arial</option>
                        <option value="'Outfit', Arial, sans-serif" {{ ($settings['font_family'] ?? '') === "'Outfit', Arial, sans-serif" ? 'selected' : '' }}>Outfit &amp; Arial</option>
                        <option value="'Open Sans', Arial, sans-serif" {{ ($settings['font_family'] ?? '') === "'Open Sans', Arial, sans-serif" ? 'selected' : '' }}>Open Sans &amp; Arial</option>
                        <option value="'Plus Jakarta Sans', Arial, sans-serif" {{ ($settings['font_family'] ?? '') === "'Plus Jakarta Sans', Arial, sans-serif" ? 'selected' : '' }}>Plus Jakarta Sans &amp; Arial</option>
                        <option value="'Poppins', Arial, sans-serif" {{ ($settings['font_family'] ?? '') === "'Poppins', Arial, sans-serif" ? 'selected' : '' }}>Poppins &amp; Arial</option>
                        <option value="'Geist', Arial, sans-serif" {{ ($settings['font_family'] ?? '') === "'Geist', Arial, sans-serif" ? 'selected' : '' }}>Geist &amp; Arial</option>
                        <option value="custom" {{ !in_array($settings['font_family'] ?? '', ['Roboto, Arial, sans-serif', 'Arial, sans-serif', "'Inter', Arial, sans-serif", "'Outfit', Arial, sans-serif", "'Open Sans', Arial, sans-serif", "'Plus Jakarta Sans', Arial, sans-serif", "'Poppins', Arial, sans-serif", "'Geist', Arial, sans-serif"]) ? 'selected' : '' }}>Custom Font Stack...</option>
                    </select>
                    <span class="text-[11px] text-slate-400 mt-1 block">Quickly apply curated typography stacks or enter a custom font stack below.</span>
                </div>

                <!-- Custom Font Family Input -->
                <div>
                    <label for="font_family" class="block text-xs font-bold text-slate-700 dark:text-slate-300 uppercase tracking-wider mb-2">
                        Active Font Family CSS Stack <span class="text-red-500">*</span>
                    </label>
                    <input type="text"
                           id="font_family"
                           name="font_family"
                           value="{{ old('font_family', $settings['font_family'] ?? 'Roboto, Arial, sans-serif') }}"
                           required
                           oninput="updateFontPreview(this.value)"
                           placeholder="e.g. 'Inter', Roboto, sans-serif"
                           class="w-full px-4 py-2.5 rounded-xl border border-slate-300 dark:border-slate-700 bg-white dark:bg-slate-800 text-sm text-slate-900 dark:text-white focus:border-indigo-500 focus:ring-2 focus:ring-indigo-500/20 transition">
                    <span class="text-[11px] text-slate-400 mt-1 block">Valid CSS font-family string. Google Fonts (Inter, Outfit, etc.) automatically load dynamically.</span>
                </div>
            </div>

            <!-- Live Font Interactive Preview Box -->
            <div class="mt-4 p-5 rounded-2xl bg-slate-50 dark:bg-slate-800/60 border border-slate-200 dark:border-slate-700/80">
                <span class="text-[11px] font-bold uppercase tracking-wider text-slate-400 block mb-2">Live Typography Preview:</span>
                <div id="font-preview-box" style="font-family: {{ $settings['font_family'] ?? 'Roboto, Arial, sans-serif' }};" class="space-y-2">
                    <h3 class="text-lg font-black text-slate-900 dark:text-white">
                        The quick brown fox jumps over the lazy dog
                    </h3>
                    <p class="text-xs text-slate-600 dark:text-slate-300 leading-relaxed">
                        Enterprise Nigerian Identity Verification Infrastructure &bull; NIN, BVN, and Enrollment Clearing.
                    </p>
                    <div class="flex items-center gap-3 pt-1">
                        <span class="text-sm font-bold text-emerald-600 dark:text-emerald-400">₦250,000.00</span>
                        <span class="px-2 py-0.5 rounded-md text-[10px] font-bold bg-indigo-100 dark:bg-indigo-500/20 text-indigo-700 dark:text-indigo-300">
                            Verified UID #10842
                        </span>
                    </div>
                </div>
            </div>
        </div>

        <!-- Section 3: Support & Contact Channels -->
        <div class="rounded-2xl bg-white dark:bg-slate-900 border border-slate-200/80 dark:border-slate-800 p-6 shadow-sm space-y-5">
            <div class="border-b border-slate-100 dark:border-slate-800 pb-4">
                <h2 class="text-base font-extrabold text-slate-900 dark:text-white flex items-center gap-2">
                    <svg class="w-5 h-5 text-indigo-600 dark:text-indigo-400" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 8l7.89 5.26a2 2 0 002.22 0L21 8M5 19h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z" />
                    </svg>
                    <span>Customer Support &amp; Inquiries</span>
                </h2>
                <p class="text-xs text-slate-500 dark:text-slate-400 mt-1">
                    Primary communication channels provided to agents and users for resolution escalations.
                </p>
            </div>

            <div class="grid grid-cols-1 md:grid-cols-3 gap-5">
                <!-- Support Email -->
                <div>
                    <label for="contact_email" class="block text-xs font-bold text-slate-700 dark:text-slate-300 uppercase tracking-wider mb-2">
                        Support Email <span class="text-red-500">*</span>
                    </label>
                    <input type="email"
                           id="contact_email"
                           name="contact_email"
                           value="{{ old('contact_email', $settings['contact_email'] ?? 'support@vsite.ng') }}"
                           required
                           placeholder="support@vsite.ng"
                           class="w-full px-4 py-2.5 rounded-xl border border-slate-300 dark:border-slate-700 bg-white dark:bg-slate-800 text-sm font-semibold text-slate-900 dark:text-white focus:border-indigo-500 focus:ring-2 focus:ring-indigo-500/20 transition">
                </div>

                <!-- Contact Phone -->
                <div>
                    <label for="contact_phone" class="block text-xs font-bold text-slate-700 dark:text-slate-300 uppercase tracking-wider mb-2">
                        Support Phone Line <span class="text-red-500">*</span>
                    </label>
                    <input type="text"
                           id="contact_phone"
                           name="contact_phone"
                           value="{{ old('contact_phone', $settings['contact_phone'] ?? '+234 800 000 0000') }}"
                           required
                           placeholder="+234 800 000 0000"
                           class="w-full px-4 py-2.5 rounded-xl border border-slate-300 dark:border-slate-700 bg-white dark:bg-slate-800 text-sm font-semibold text-slate-900 dark:text-white focus:border-indigo-500 focus:ring-2 focus:ring-indigo-500/20 transition">
                </div>

                <!-- WhatsApp Contact -->
                <div>
                    <label for="contact_whatsapp" class="block text-xs font-bold text-slate-700 dark:text-slate-300 uppercase tracking-wider mb-2">
                        WhatsApp Business Line
                    </label>
                    <input type="text"
                           id="contact_whatsapp"
                           name="contact_whatsapp"
                           value="{{ old('contact_whatsapp', $settings['contact_whatsapp'] ?? '+234 812 345 6789') }}"
                           placeholder="+234 812 345 6789"
                           class="w-full px-4 py-2.5 rounded-xl border border-slate-300 dark:border-slate-700 bg-white dark:bg-slate-800 text-sm font-semibold text-slate-900 dark:text-white focus:border-indigo-500 focus:ring-2 focus:ring-indigo-500/20 transition">
                </div>
            </div>
        </div>

        <!-- Section 4: Financial & Operations Settings -->
        <div class="rounded-2xl bg-white dark:bg-slate-900 border border-slate-200/80 dark:border-slate-800 p-6 shadow-sm space-y-5">
            <div class="border-b border-slate-100 dark:border-slate-800 pb-4">
                <h2 class="text-base font-extrabold text-slate-900 dark:text-white flex items-center gap-2">
                    <svg class="w-5 h-5 text-indigo-600 dark:text-indigo-400" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8c-1.657 0-3 .895-3 2s1.343 2 3 2 3 .895 3 2-1.343 2-3 2m0-8c1.11 0 2.08.402 2.599 1M12 8V7m0 1v8m0 0v1m0-1c-1.11 0-2.08-.402-2.599-1M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
                    </svg>
                    <span>Financial Thresholds &amp; Operational Mode</span>
                </h2>
                <p class="text-xs text-slate-500 dark:text-slate-400 mt-1">
                    Minimum deposit limits for user wallets and maintenance window controls.
                </p>
            </div>

            <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                <!-- Minimum Wallet Deposit -->
                <div>
                    <label for="min_wallet_deposit" class="block text-xs font-bold text-slate-700 dark:text-slate-300 uppercase tracking-wider mb-2">
                        Minimum Wallet Deposit (₦) <span class="text-red-500">*</span>
                    </label>
                    <div class="relative rounded-xl shadow-xs">
                        <div class="pointer-events-none absolute inset-y-0 left-0 flex items-center pl-3.5">
                            <span class="text-slate-500 dark:text-slate-400 font-bold text-sm">₦</span>
                        </div>
                        <input type="number"
                               id="min_wallet_deposit"
                               name="min_wallet_deposit"
                               step="0.01"
                               min="50"
                               value="{{ old('min_wallet_deposit', $settings['min_wallet_deposit'] ?? '500.00') }}"
                               required
                               class="block w-full rounded-xl border border-slate-300 dark:border-slate-700 bg-white dark:bg-slate-800 py-2.5 pl-8 pr-3 text-sm font-bold text-slate-900 dark:text-white focus:border-indigo-500 focus:ring-2 focus:ring-indigo-500/20 transition">
                    </div>
                    <span class="text-[11px] text-slate-400 mt-1 block">Minimum amount allowed when agents or users initiate wallet top-ups.</span>
                </div>

                <!-- Maintenance Mode Toggle -->
                <div>
                    <span class="block text-xs font-bold text-slate-700 dark:text-slate-300 uppercase tracking-wider mb-2">
                        Maintenance Mode
                    </span>
                    <div class="p-4 rounded-xl bg-slate-50 dark:bg-slate-800/60 border border-slate-200 dark:border-slate-700/80 flex items-center justify-between gap-4">
                        <div>
                            <div class="text-sm font-bold text-slate-900 dark:text-white">
                                System Maintenance Mode
                            </div>
                            <div class="text-xs text-slate-500 dark:text-slate-400 mt-0.5">
                                Restrict user verification actions during planned core infrastructure upgrades.
                            </div>
                        </div>
                        <label class="relative inline-flex items-center cursor-pointer flex-shrink-0">
                            <input type="checkbox"
                                   name="maintenance_mode"
                                   value="1"
                                   class="sr-only peer"
                                   {{ old('maintenance_mode', $settings['maintenance_mode'] ?? '0') == '1' ? 'checked' : '' }}>
                            <div class="w-11 h-6 bg-slate-200 peer-focus:outline-hidden peer-focus:ring-2 peer-focus:ring-amber-500/20 dark:bg-slate-700 peer-checked:after:translate-x-full peer-checked:after:border-white after:content-["'] after:absolute after:top-[2px] after:left-[2px] after:bg-white after:border-slate-300 after:border after:rounded-full after:h-5 after:w-5 after:transition-all dark:border-slate-600 peer-checked:bg-amber-500"></div>
                        </label>
                    </div>
                </div>
            </div>
        </div>

        <!-- Section 5: Monnify Payment Gateway Settings -->
        <div class="rounded-2xl bg-white dark:bg-slate-900 border border-slate-200/80 dark:border-slate-800 p-6 shadow-sm space-y-5">
            <div class="border-b border-slate-100 dark:border-slate-800 pb-4 flex flex-col sm:flex-row sm:items-center justify-between gap-3">
                <div>
                    <h2 class="text-base font-extrabold text-slate-900 dark:text-white flex items-center gap-2">
                        <div class="w-6 h-6 rounded-lg bg-blue-500/10 text-blue-600 dark:text-blue-400 flex items-center justify-center font-black text-xs">
                            M
                        </div>
                        <span>Monnify Payment Gateway Architecture</span>
                    </h2>
                    <p class="text-xs text-slate-500 dark:text-slate-400 mt-1">
                        Configure Monnify merchant credentials, API keys, contract code, and webhook listeners for automated wallet deposits.
                    </p>
                </div>
                <div class="flex items-center gap-2">
                    <span class="inline-flex items-center gap-1.5 px-3 py-1 rounded-full text-xs font-bold {{ ($settings['monnify_enabled'] ?? '1') == '1' ? 'bg-emerald-50 dark:bg-emerald-500/10 text-emerald-700 dark:text-emerald-400 border border-emerald-200 dark:border-emerald-500/30' : 'bg-slate-100 dark:bg-slate-800 text-slate-600 dark:text-slate-400' }}">
                        <span class="w-2 h-2 rounded-full {{ ($settings['monnify_enabled'] ?? '1') == '1' ? 'bg-emerald-500' : 'bg-slate-400' }}"></span>
                        <span>{{ ($settings['monnify_enabled'] ?? '1') == '1' ? 'Gateway Active' : 'Gateway Disabled' }}</span>
                    </span>
                    <span class="inline-flex items-center px-2.5 py-1 rounded-full text-[11px] font-bold uppercase tracking-wider {{ strtoupper($settings['monnify_environment'] ?? 'SANDBOX') === 'LIVE' ? 'bg-purple-50 dark:bg-purple-500/10 text-purple-700 dark:text-purple-400 border border-purple-200 dark:border-purple-500/30' : 'bg-amber-50 dark:bg-amber-500/10 text-amber-700 dark:text-amber-400 border border-amber-200 dark:border-amber-500/30' }}">
                        {{ strtoupper($settings['monnify_environment'] ?? 'SANDBOX') }}
                    </span>
                </div>
            </div>

            <!-- Gateway Toggle & Environment -->
            <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                <!-- Gateway Active Toggle -->
                <div class="p-4 rounded-xl bg-slate-50 dark:bg-slate-800/60 border border-slate-200 dark:border-slate-700/80 flex items-center justify-between gap-4">
                    <div>
                        <div class="text-sm font-bold text-slate-900 dark:text-white">
                            Enable Monnify Gateway
                        </div>
                        <div class="text-xs text-slate-500 dark:text-slate-400 mt-0.5">
                            Allow customers and agents to fund their wallets using Monnify.
                        </div>
                    </div>
                    <label class="relative inline-flex items-center cursor-pointer flex-shrink-0">
                        <input type="checkbox"
                               name="monnify_enabled"
                               value="1"
                               class="sr-only peer"
                               {{ old('monnify_enabled', $settings['monnify_enabled'] ?? '1') == '1' ? 'checked' : '' }}>
                        <div class="w-11 h-6 bg-slate-200 peer-focus:outline-hidden peer-focus:ring-2 peer-focus:ring-emerald-500/20 dark:bg-slate-700 peer-checked:after:translate-x-full peer-checked:after:border-white after:content-[''] after:absolute after:top-[2px] after:left-[2px] after:bg-white after:border-slate-300 after:border after:rounded-full after:h-5 after:w-5 after:transition-all dark:border-slate-600 peer-checked:bg-emerald-500"></div>
                    </label>
                </div>

                <!-- Environment Selector -->
                <div>
                    <label for="monnify_environment" class="block text-xs font-bold text-slate-700 dark:text-slate-300 uppercase tracking-wider mb-2">
                        Gateway Environment <span class="text-red-500">*</span>
                    </label>
                    <select id="monnify_environment"
                            name="monnify_environment"
                            class="w-full px-4 py-2.5 rounded-xl border border-slate-300 dark:border-slate-700 bg-white dark:bg-slate-800 text-sm font-bold text-slate-900 dark:text-white focus:border-indigo-500 focus:ring-2 focus:ring-indigo-500/20 transition">
                        <option value="SANDBOX" {{ old('monnify_environment', $settings['monnify_environment'] ?? 'SANDBOX') === 'SANDBOX' ? 'selected' : '' }}>
                            SANDBOX (Test Mode - https://sandbox.monnify.com)
                        </option>
                        <option value="LIVE" {{ old('monnify_environment', $settings['monnify_environment'] ?? 'SANDBOX') === 'LIVE' ? 'selected' : '' }}>
                            LIVE (Production - https://api.monnify.com)
                        </option>
                    </select>
                    <span class="text-[11px] text-slate-400 mt-1 block">Ensure you switch to LIVE with live merchant keys when deploying to production.</span>
                </div>
            </div>

            <!-- Credentials Grid -->
            <div class="grid grid-cols-1 md:grid-cols-3 gap-6 pt-2">
                <!-- Contract Code -->
                <div>
                    <label for="monnify_contract_code" class="block text-xs font-bold text-slate-700 dark:text-slate-300 uppercase tracking-wider mb-2">
                        Monnify Contract Code
                    </label>
                    <input type="text"
                           id="monnify_contract_code"
                           name="monnify_contract_code"
                           value="{{ old('monnify_contract_code', $settings['monnify_contract_code'] ?? '') }}"
                           placeholder="e.g. 8472910482"
                           class="w-full px-4 py-2.5 rounded-xl border border-slate-300 dark:border-slate-700 bg-white dark:bg-slate-800 text-sm font-mono font-semibold text-slate-900 dark:text-white focus:border-indigo-500 focus:ring-2 focus:ring-indigo-500/20 transition">
                    <span class="text-[11px] text-slate-400 mt-1 block">Assigned contract code from Monnify console.</span>
                </div>

                <!-- API Key -->
                <div>
                    <div class="flex items-center justify-between mb-2">
                        <label for="monnify_api_key" class="block text-xs font-bold text-slate-700 dark:text-slate-300 uppercase tracking-wider">
                            API Key (MK_...)
                        </label>
                        <button type="button" onclick="toggleKeyVisibility('monnify_api_key', this)" class="text-[11px] text-indigo-600 dark:text-indigo-400 hover:underline font-semibold cursor-pointer">
                            Show
                        </button>
                    </div>
                    <input type="password"
                           id="monnify_api_key"
                           name="monnify_api_key"
                           value="{{ old('monnify_api_key', $settings['monnify_api_key'] ?? '') }}"
                           placeholder="MK_TEST_..."
                           class="w-full px-4 py-2.5 rounded-xl border border-slate-300 dark:border-slate-700 bg-white dark:bg-slate-800 text-sm font-mono font-semibold text-slate-900 dark:text-white focus:border-indigo-500 focus:ring-2 focus:ring-indigo-500/20 transition">
                    <span class="text-[11px] text-slate-400 mt-1 block">Public authentication merchant key.</span>
                </div>

                <!-- Secret Key -->
                <div>
                    <div class="flex items-center justify-between mb-2">
                        <label for="monnify_secret_key" class="block text-xs font-bold text-slate-700 dark:text-slate-300 uppercase tracking-wider">
                            Secret Key
                        </label>
                        <button type="button" onclick="toggleKeyVisibility('monnify_secret_key', this)" class="text-[11px] text-indigo-600 dark:text-indigo-400 hover:underline font-semibold cursor-pointer">
                            Show
                        </button>
                    </div>
                    <input type="password"
                           id="monnify_secret_key"
                           name="monnify_secret_key"
                           value="{{ old('monnify_secret_key', $settings['monnify_secret_key'] ?? '') }}"
                           placeholder="Enter Monnify Secret Key"
                           class="w-full px-4 py-2.5 rounded-xl border border-slate-300 dark:border-slate-700 bg-white dark:bg-slate-800 text-sm font-mono font-semibold text-slate-900 dark:text-white focus:border-indigo-500 focus:ring-2 focus:ring-indigo-500/20 transition">
                    <span class="text-[11px] text-slate-400 mt-1 block">Used for OAuth signing &amp; webhook hashing.</span>
                </div>
            </div>

            <!-- Webhook Listener & Live Test Card -->
            <div class="p-4 sm:p-5 rounded-2xl bg-slate-50/80 dark:bg-slate-800/40 border border-slate-200/80 dark:border-slate-700/80 space-y-4">
                <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-4">
                    <div class="flex-1">
                        <span class="block text-xs font-bold text-slate-700 dark:text-slate-300 uppercase tracking-wider mb-1">
                            Webhook Listener Endpoint (Automated Credit)
                        </span>
                        <div class="flex items-center gap-2 mt-1">
                            <input type="text"
                                   id="monnify-webhook-url"
                                   readonly
                                   value="{{ url('/webhooks/monnify') }}"
                                   class="w-full max-w-lg px-3.5 py-2 rounded-xl border border-slate-300 dark:border-slate-700 bg-white dark:bg-slate-900 text-xs font-mono text-slate-700 dark:text-slate-300 select-all">
                            <button type="button"
                                    onclick="copyWebhookUrl()"
                                    id="copy-webhook-btn"
                                    class="inline-flex items-center gap-1 px-3 py-2 rounded-xl text-xs font-bold text-slate-700 dark:text-slate-200 bg-white dark:bg-slate-800 hover:bg-slate-100 dark:hover:bg-slate-700/80 border border-slate-300 dark:border-slate-600 transition shadow-xs cursor-pointer flex-shrink-0">
                                <svg class="w-3.5 h-3.5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 5H6a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2v-1M8 5a2 2 0 002 2h2a2 2 0 002-2M8 5a2 2 0 012-2h2a2 2 0 012 2m0 0h2a2 2 0 012 2v3m2 4H10m0 0l3-3m-3 3l3 3" />
                                </svg>
                                <span id="copy-webhook-text">Copy URL</span>
                            </button>
                        </div>
                        <p class="text-[11px] text-slate-400 mt-1.5">
                            Register this URL in your Monnify Merchant Dashboard under <strong>Settings &gt; Webhooks</strong> to allow Monnify to credit user wallets automatically.
                        </p>
                    </div>

                    <!-- Test Connection Button -->
                    <div class="flex-shrink-0">
                        <button type="button"
                                onclick="testMonnifyApi()"
                                id="test-monnify-btn"
                                class="inline-flex items-center gap-2 px-4 py-2.5 rounded-xl text-xs font-bold text-indigo-700 dark:text-indigo-300 bg-indigo-50 dark:bg-indigo-500/10 hover:bg-indigo-100 dark:hover:bg-indigo-500/20 border border-indigo-200 dark:border-indigo-500/30 transition shadow-xs cursor-pointer">
                            <svg id="test-spinner" class="w-4 h-4 hidden animate-spin text-indigo-600" fill="none" viewBox="0 0 24 24">
                                <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                                <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
                            </svg>
                            <span id="test-btn-text">Test Credentials &amp; Connection</span>
                        </button>
                    </div>
                </div>

                <!-- Live Test Result Banner -->
                <div id="monnify-test-result" class="hidden p-3.5 rounded-xl text-xs font-semibold"></div>
            </div>
        </div>

        <!-- Action Bar -->
        <div class="flex items-center justify-end gap-3 pt-2">
            <button type="submit"
                    class="inline-flex items-center gap-2 px-6 py-3 rounded-xl text-sm font-bold text-white bg-gradient-to-r from-indigo-600 via-indigo-600 to-violet-600 hover:from-indigo-500 hover:to-violet-500 shadow-md shadow-indigo-600/25 transition-all cursor-pointer">
                <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7" />
                </svg>
                <span>Save Configurations</span>
            </button>
        </div>
    </form>

</div>

<!-- Interactive Client-side Script for Font Previews & Logo Management -->
<script>
    function applyFontPreset(val) {
        if (val !== 'custom') {
            const input = document.getElementById('font_family');
            if (input) {
                input.value = val;
                updateFontPreview(val);
            }
        }
    }

    function updateFontPreview(val) {
        const preview = document.getElementById('font-preview-box');
        if (preview && val) {
            preview.style.fontFamily = val;
        }
    }

    let originalLogoSrc = null;

    function handleLogoSelect(input) {
        const previewImg = document.getElementById('logo-preview-img');
        const defaultPlaceholder = document.getElementById('logo-default-placeholder');
        const fileNameEl = document.getElementById('logo-file-name');
        const statusLabel = document.getElementById('logo-status-label');
        const removeCheckbox = document.getElementById('remove_logo_checkbox');

        if (input.files && input.files[0]) {
            const file = input.files[0];

            if (file.size > 2 * 1024 * 1024) {
                alert('File size exceeds 2MB limit. Please choose a smaller image.');
                input.value = '';
                return;
            }

            if (!originalLogoSrc && previewImg && previewImg.src && !previewImg.classList.contains('hidden')) {
                originalLogoSrc = previewImg.src;
            }

            const reader = new FileReader();
            reader.onload = function(e) {
                if (previewImg) {
                    previewImg.src = e.target.result;
                    previewImg.classList.remove('hidden');
                }
                if (defaultPlaceholder) {
                    defaultPlaceholder.classList.add('hidden');
                }
                if (fileNameEl) {
                    fileNameEl.textContent = 'Selected: ' + file.name + ' (' + (file.size / 1024).toFixed(1) + ' KB)';
                    fileNameEl.classList.remove('hidden');
                }
                if (statusLabel) {
                    statusLabel.textContent = 'New Upload Preview';
                }
                if (removeCheckbox) {
                    removeCheckbox.checked = false;
                }
            };
            reader.readAsDataURL(file);
        }
    }

    function toggleRemoveLogo(checked) {
        const previewImg = document.getElementById('logo-preview-img');
        const defaultPlaceholder = document.getElementById('logo-default-placeholder');
        const fileInput = document.getElementById('site_logo');
        const fileNameEl = document.getElementById('logo-file-name');
        const statusLabel = document.getElementById('logo-status-label');

        if (checked) {
            if (fileInput) fileInput.value = '';
            if (fileNameEl) fileNameEl.classList.add('hidden');
            if (previewImg) previewImg.classList.add('hidden');
            if (defaultPlaceholder) defaultPlaceholder.classList.remove('hidden');
            if (statusLabel) statusLabel.textContent = 'Default Brand';
        } else {
            const originalSrc = previewImg ? previewImg.getAttribute('data-original-src') : null;
            if (originalSrc) {
                if (previewImg) {
                    previewImg.src = originalSrc;
                    previewImg.classList.remove('hidden');
                }
                if (defaultPlaceholder) defaultPlaceholder.classList.add('hidden');
    }

    function toggleKeyVisibility(inputId, btn) {
        const input = document.getElementById(inputId);
        if (input) {
            if (input.type === 'password') {
                input.type = 'text';
                btn.textContent = 'Hide';
            } else {
                input.type = 'password';
                btn.textContent = 'Show';
            }
        }
    }

    function copyWebhookUrl() {
        const input = document.getElementById('monnify-webhook-url');
        const textSpan = document.getElementById('copy-webhook-text');
        if (input) {
            navigator.clipboard.writeText(input.value).then(() => {
                if (textSpan) {
                    const original = textSpan.textContent;
                    textSpan.textContent = 'Copied!';
                    setTimeout(() => { textSpan.textContent = original; }, 2000);
                }
            }).catch(() => {
                input.select();
                document.execCommand('copy');
                if (textSpan) textSpan.textContent = 'Copied!';
            });
        }
    }

    function testMonnifyApi() {
        const btn = document.getElementById('test-monnify-btn');
        const spinner = document.getElementById('test-spinner');
        const btnText = document.getElementById('test-btn-text');
        const resultBanner = document.getElementById('monnify-test-result');

        if (btn) btn.disabled = true;
        if (spinner) spinner.classList.remove('hidden');
        if (btnText) btnText.textContent = 'Authenticating with Monnify...';
        if (resultBanner) resultBanner.classList.add('hidden');

        fetch('{{ route('admin.settings.monnify.test') }}', {
            method: 'POST',
            headers: {
                'X-CSRF-TOKEN': '{{ csrf_token() }}',
                'Content-Type': 'application/json',
                'Accept': 'application/json'
            }
        })
        .then(response => response.json())
        .then(data => {
            if (resultBanner) {
                resultBanner.classList.remove('hidden');
                if (data.success) {
                    resultBanner.className = 'p-3.5 rounded-xl text-xs font-semibold bg-emerald-50 dark:bg-emerald-500/10 text-emerald-800 dark:text-emerald-300 border border-emerald-200 dark:border-emerald-500/30';
                    resultBanner.innerHTML = '<strong>Success:</strong> ' + data.message + ' (Environment: ' + (data.environment || 'SANDBOX') + ')';
                } else {
                    resultBanner.className = 'p-3.5 rounded-xl text-xs font-semibold bg-red-50 dark:bg-red-500/10 text-red-800 dark:text-red-300 border border-red-200 dark:border-red-500/30';
                    resultBanner.innerHTML = '<strong>Error:</strong> ' + (data.message || 'Authentication failed. Please check your credentials.');
                }
            }
        })
        .catch(err => {
            if (resultBanner) {
                resultBanner.classList.remove('hidden');
                resultBanner.className = 'p-3.5 rounded-xl text-xs font-semibold bg-red-50 dark:bg-red-500/10 text-red-800 dark:text-red-300 border border-red-200 dark:border-red-500/30';
                resultBanner.innerHTML = '<strong>Error:</strong> Failed to reach test endpoint. ' + err.message;
            }
        })
        .finally(() => {
            if (btn) btn.disabled = false;
            if (spinner) spinner.classList.add('hidden');
            if (btnText) btnText.textContent = 'Test Credentials & Connection';
        });
    }
</script>
@endsection
