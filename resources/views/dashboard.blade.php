@extends('layouts.app')

@section('title', 'User Dashboard')

@section('content')
<div class="space-y-8">
    <!-- 10.1 Greeting Banner -->
    <div class="relative overflow-hidden rounded-2xl bg-gradient-to-r from-white via-slate-50 to-emerald-50/60 dark:from-slate-900 dark:via-slate-800 dark:to-emerald-950/40 p-6 sm:p-8 border border-slate-200/80 dark:border-slate-800 shadow-sm dark:shadow-xl transition-colors">
        <div class="absolute -right-10 -bottom-10 w-48 h-48 bg-emerald-500/10 rounded-full blur-3xl pointer-events-none"></div>
        <div class="relative z-10">
            <h1 class="text-2xl sm:text-3xl font-extrabold text-slate-900 dark:text-white tracking-tight">
                Good day! {{ auth()->user()->full_name }}
            </h1>
        </div>
    </div>

    <!-- Quick Info Cards Grid: Wallet (10.2) & Announcement (10.3) -->
    <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
        <!-- 10.2 Wallet Card -->
        <div class="rounded-2xl bg-white dark:bg-slate-900 border border-slate-200/80 dark:border-slate-800 p-6 shadow-sm dark:shadow-xl relative overflow-hidden flex flex-col justify-between transition-colors">
            <div>
                <div class="flex items-center justify-between">
                    <div>
                        <span class="text-xs font-medium uppercase tracking-wider text-slate-500 dark:text-slate-400">Available Wallet Balance</span>
                        <div class="mt-2 text-3xl font-extrabold text-emerald-600 dark:text-emerald-400">
                            {{ $wallet->formatted_balance }}
                        </div>
                    </div>
                    <button type="button"
                            onclick="openFundWalletModal()"
                            class="w-12 h-12 rounded-2xl bg-emerald-50 dark:bg-emerald-500/10 text-emerald-600 dark:text-emerald-400 flex items-center justify-center border border-emerald-200 dark:border-emerald-500/20 hover:bg-emerald-100 dark:hover:bg-emerald-500/20 hover:scale-105 active:scale-95 transition-all cursor-pointer group"
                            title="Fund Wallet"
                            id="fund-wallet-icon-btn">
                        <svg class="w-6 h-6 transition-transform group-hover:scale-110" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 10h18M7 15h1m4 0h1m-7 4h12a3 3 0 003-3V8a3 3 0 00-3-3H6a3 3 0 00-3 3v8a3 3 0 003 3z" />
                        </svg>
                    </button>
                </div>
            </div>

            <div class="mt-6 pt-4 border-t border-slate-100 dark:border-slate-800/80 flex items-center justify-between">
                <button type="button"
                        onclick="openFundWalletModal()"
                        class="text-xs font-semibold text-emerald-600 dark:text-emerald-400 hover:text-emerald-500 transition-colors flex items-center gap-1 cursor-pointer">
                    <span>+ Fund Your Wallet</span>
                    <span>&rarr;</span>
                </button>
            </div>
        </div>

        <!-- 10.3 Announcement Card -->
        <div class="rounded-2xl bg-white dark:bg-slate-900 border border-slate-200/80 dark:border-slate-800 p-6 shadow-sm dark:shadow-xl flex flex-col justify-between transition-colors">
            <div>
                <div class="flex items-center justify-between">
                    <h3 class="text-sm font-bold uppercase tracking-wider text-slate-800 dark:text-slate-200 flex items-center gap-2">
                        <svg class="w-4 h-4 text-emerald-600 dark:text-emerald-400" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5.882V19.24a1.76 1.76 0 01-3.417.592l-2.147-6.15M18 13a3 3 0 100-6M5.436 13.683A4.001 4.001 0 017 6h1.832c4.1 0 7.625-1.234 9.168-3v14c-1.543-1.766-5.067-3-9.168-3H7a3.988 3.988 0 01-1.564-.317z" />
                        </svg>
                        <span>Announcement</span>
                    </h3>
                    <span class="text-[10px] text-slate-400 dark:text-slate-500">Platform Notice</span>
                </div>

                <div class="mt-3 text-xs leading-relaxed text-slate-600 dark:text-slate-300 space-y-2.5">
                    @forelse($announcements as $notice)
                        <div class="border-l-2 {{ $notice->type === 'warning' ? 'border-amber-500 bg-amber-50/40 dark:bg-amber-500/5' : ($notice->type === 'success' ? 'border-emerald-500 bg-emerald-50/40 dark:bg-emerald-500/5' : 'border-indigo-500 bg-indigo-50/40 dark:bg-indigo-500/5') }} pl-3 py-1.5 rounded-r-xl">
                            <div class="flex items-center justify-between gap-2">
                                <strong class="text-slate-900 dark:text-white font-bold text-xs">{{ $notice->title }}</strong>
                                <span class="text-[10px] text-slate-400">{{ $notice->created_at->diffForHumans() }}</span>
                            </div>
                            <span class="text-slate-500 dark:text-slate-400 text-xs block mt-0.5">{{ $notice->content }}</span>
                        </div>
                    @empty
                        <p class="text-slate-400 text-xs">All identity verification and clearing channels are fully operational.</p>
                    @endforelse
                </div>
            </div>

            <div class="mt-4 pt-3 border-t border-slate-100 dark:border-slate-800/80 flex items-center justify-between text-[11px] text-slate-400 dark:text-slate-500">
                <!-- <span>System Updates Active</span> -->
                @if(auth()->user()->hasRole('super_admin') || auth()->user()->hasPermission('announcements.manage'))
                    <a href="{{ route('admin.announcements.index') }}" class="text-emerald-600 dark:text-emerald-400 hover:underline font-semibold">
                        Manage Announcements &rarr;
                    </a>
                @endif
            </div>
        </div>
    </div>

    <!-- 10.4 Dynamic Services Grid (Unified 3-Column Grid) -->
    <div class="space-y-4">
        <div class="border-b border-slate-200 dark:border-slate-800 pb-3">
            <h2 class="text-xl font-bold text-slate-900 dark:text-white tracking-tight">Available Services</h2>
            <!-- <p class="text-xs text-slate-500 dark:text-slate-400 mt-0.5">Select a service to initiate an automated search or submit bulk entries.</p> -->
        </div>

        <div class="grid grid-cols-2 lg:grid-cols-3 gap-3.5 sm:gap-5 lg:gap-6">
            @foreach($categories as $category)
                @foreach($category->activeServices as $service)
                    <a href="{{ route('services.show', $service->slug) }}"
                       class="rounded-2xl sm:rounded-[20px] bg-white dark:bg-slate-900 border border-slate-200/80 dark:border-slate-800 hover:border-emerald-500/50 p-3.5 sm:p-5 lg:p-6 flex flex-col items-start justify-start h-[175px] sm:h-[195px] min-h-[175px] sm:min-h-[195px] max-h-[175px] sm:max-h-[195px] transition-all duration-200 group shadow-[0_2px_8px_rgba(0,0,0,0.04)] hover:shadow-md dark:shadow-none text-left">
                        
                        <!-- 1. Rounded-Square Logo Container -->
                        <x-service-icon :service="$service" size="card" />

                        <!-- 2. Prominent Semi-Bold Service Name -->
                        <span class="mt-2.5 sm:mt-3 text-xs sm:text-sm font-semibold text-slate-800 dark:text-slate-100 group-hover:text-emerald-600 dark:group-hover:text-emerald-400 transition-colors line-clamp-2 leading-tight">
                            {{ $service->name }}
                        </span>

                        <!-- 3. Formatted Price Badge (Secondary Visual Hierarchy) -->
                        <div class="mt-auto pt-2 sm:pt-2.5 flex items-center justify-between w-full">
                            <span class="text-[11px] sm:text-xs font-semibold text-emerald-600 dark:text-emerald-400 bg-emerald-50 dark:bg-emerald-500/10 px-2 py-0.5 rounded-full border border-emerald-200 dark:border-emerald-500/20">
                                {{ $service->formatted_price }}
                            </span>
                            <span class="text-slate-400 group-hover:text-emerald-500 transition-colors">
                                <svg class="w-4 h-4 transform group-hover:translate-x-0.5 transition-transform" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7" />
                                </svg>
                            </span>
                        </div>
                    </a>
                @endforeach
            @endforeach
        </div>
    </div>

</div>

<!-- Fund Wallet Popup Modal (Exact User Provided Design) -->
<div id="fundWalletModal" 
     class="fixed inset-0 z-50 hidden items-center justify-center p-4 bg-slate-900/60 backdrop-blur-xs transition-opacity duration-200"
     role="dialog" 
     aria-modal="true" 
     aria-labelledby="fundWalletModalTitle">
    
    <!-- Modal Backdrop Click Dismissal -->
    <div class="fixed inset-0" onclick="closeFundWalletModal()"></div>

    <!-- Modal Card -->
    <div class="relative z-10 w-full max-w-[490px] bg-white dark:bg-slate-900 rounded-2xl sm:rounded-3xl shadow-2xl border border-slate-100 dark:border-slate-800 overflow-hidden transform transition-all p-5 sm:p-7 text-left"
         onclick="event.stopPropagation()">
        
        <!-- 1. Header: Title & Close Button -->
        <div class="flex items-center justify-between pb-3.5 border-b border-slate-100 dark:border-slate-800">
            <h3 id="fundWalletModalTitle" class="text-lg sm:text-xl font-bold text-slate-900 dark:text-white tracking-tight">
                Fund Wallet
            </h3>
            <button type="button" 
                    onclick="closeFundWalletModal()" 
                    class="text-slate-400 hover:text-slate-600 dark:hover:text-slate-200 p-1.5 rounded-lg hover:bg-slate-100 dark:hover:bg-slate-800 transition-colors cursor-pointer"
                    title="Close"
                    aria-label="Close modal">
                <svg class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
                </svg>
            </button>
        </div>

        <!-- 2. Subtitle -->
        <div class="pt-4 pb-1">
            <p class="text-sm sm:text-[15px] font-bold text-slate-900 dark:text-white leading-snug">
                Fund your wallet instantly by depositing into the virtual account number
            </p>
        </div>

        <!-- 3. Virtual Accounts List (Sterling, Wema, Moniepoint) -->
        @php
            $modalAccounts = collect($virtualAccounts ?? auth()->user()->getMonnifyVirtualAccounts());
            $bankPriority = ['sterling' => 1, 'wema' => 2, 'moniepoint' => 3];
            $sortedAccounts = $modalAccounts->sortBy(function($acc) use ($bankPriority) {
                $slug = $acc['bank_slug'] ?? \App\Models\MonnifyVirtualAccount::resolveBankSlug($acc['bank_code'] ?? null, $acc['bank_name'] ?? '');
                return $bankPriority[$slug] ?? 99;
            })->values();
        @endphp

        <div class="mt-4 space-y-6">
            @forelse($sortedAccounts as $acc)
                @php
                    $bankSlug = $acc['bank_slug'] ?? \App\Models\MonnifyVirtualAccount::resolveBankSlug($acc['bank_code'] ?? null, $acc['bank_name'] ?? '');
                    $displayBankName = match($bankSlug) {
                        'sterling' => 'Sterling bank',
                        'wema' => 'Wema bank',
                        'moniepoint' => 'Moniepoint Microfinance Bank',
                        default => $acc['bank_name']
                    };
                @endphp
                <div class="flex items-center justify-between gap-3 py-0.5">
                    <!-- Bank Logo Column -->
                    <div class="w-16 sm:w-20 flex-shrink-0 flex items-center justify-start">
                        @if($bankSlug === 'sterling')
                            <img src="{{ asset('images/banks/sterling.png') }}" alt="Sterling bank" class="w-12 sm:w-14 h-auto max-h-16 object-contain">
                        @elseif($bankSlug === 'wema')
                            <img src="{{ asset('images/banks/wema.png') }}" alt="Wema bank" class="w-14 sm:w-16 h-auto max-h-16 object-contain">
                        @elseif($bankSlug === 'moniepoint')
                            <img src="{{ asset('images/banks/moniepoint.png') }}" alt="Moniepoint Microfinance Bank" class="w-18 sm:w-20 h-auto max-h-12 object-contain">
                        @else
                            <img src="{{ $acc['logo'] }}" alt="{{ $acc['bank_name'] }}" class="w-12 h-auto object-contain">
                        @endif
                    </div>

                    <!-- Account Details Column -->
                    <div class="flex-1 min-w-0 pr-2">
                        <div class="text-xs sm:text-sm font-medium text-slate-800 dark:text-slate-200 truncate">
                            {{ $acc['account_name'] }}
                        </div>
                        <div class="text-lg sm:text-xl font-bold text-slate-900 dark:text-white tracking-wide my-0.5">
                            {{ $acc['account_number'] }}
                        </div>
                        <div class="text-xs text-slate-500 dark:text-slate-400">
                            {{ $displayBankName }}
                        </div>
                    </div>

                    <!-- Blue Copy Button -->
                    <div class="flex-shrink-0">
                        <button type="button" 
                                onclick="copyModalAccount('{{ $acc['account_number'] }}', this)"
                                class="px-4 sm:px-5 py-1.5 rounded-lg bg-[#007bff] hover:bg-[#0069d9] text-white text-xs sm:text-sm font-semibold transition active:scale-95 shadow-xs cursor-pointer min-w-[70px] text-center">
                            Copy
                        </button>
                    </div>
                </div>
            @empty
                <div class="p-4 text-center text-xs text-slate-500">
                    Generating your dedicated virtual accounts... Please refresh in a moment.
                </div>
            @endforelse
        </div>

        <!-- 4. Divider Line -->
        <div class="border-t border-slate-100 dark:border-slate-800 my-5"></div>

        <!-- 5. Support Alert in Red with Headphone Icon -->
        <div class="text-center text-xs sm:text-[13px] font-medium text-[#dc2626] dark:text-rose-400 flex items-center justify-center gap-1.5">
            <span>If your funds is not received within 30mins. Please <a href="{{ route('support.index') }}" class="hover:underline font-semibold">Contact Support</a></span>
            <svg class="w-4 h-4 inline-block text-[#dc2626] dark:text-rose-400 flex-shrink-0 fill-current" viewBox="0 0 24 24">
                <path d="M12 2C6.48 2 2 6.48 2 12v7c0 1.66 1.34 3 3 3h3v-8H4v-2c0-4.41 3.59-8 8-8s8 3.59 8 8v2h-4v8h3c1.66 0 3-1.34 3-3v-7c0-5.52-4.48-10-10-10z"/>
            </svg>
        </div>

        <!-- 6. Centered Red 'Go to wallet' Link with Wallet Icon -->
        <div class="mt-4 text-center">
            <a href="{{ route('wallet.index') }}" 
               class="inline-flex items-center justify-center gap-1.5 text-xl sm:text-2xl font-bold text-[#dc2626] dark:text-rose-500 hover:text-red-700 dark:hover:text-rose-400 transition-colors group">
                <span>Go to wallet</span>
                <svg class="w-7 h-7 text-[#dc2626] dark:text-rose-500 group-hover:scale-110 transition-transform stroke-current" viewBox="0 0 24 24" fill="none" stroke-width="2.3" stroke-linecap="round" stroke-linejoin="round">
                    <rect x="3" y="5" width="18" height="15" rx="3"/>
                    <path d="M15 12a1.5 1.5 0 0 1 1.5-1.5H21v3h-4.5A1.5 1.5 0 0 1 15 12z"/>
                    <circle cx="18" cy="12" r="0.75" fill="currentColor"/>
                </svg>
            </a>
        </div>

    </div>
</div>

<!-- Modal Interactivity JavaScript -->
<script>
    function openFundWalletModal() {
        const modal = document.getElementById('fundWalletModal');
        if (modal) {
            modal.classList.remove('hidden');
            modal.classList.add('flex');
            document.body.style.overflow = 'hidden';
        }
    }

    function closeFundWalletModal() {
        const modal = document.getElementById('fundWalletModal');
        if (modal) {
            modal.classList.add('hidden');
            modal.classList.remove('flex');
            document.body.style.overflow = '';
        }
    }

    document.addEventListener('keydown', function(event) {
        if (event.key === 'Escape') {
            closeFundWalletModal();
        }
    });

    function copyModalAccount(accountNumber, buttonEl) {
        if (!navigator.clipboard) {
            const tempInput = document.createElement('input');
            tempInput.value = accountNumber;
            document.body.appendChild(tempInput);
            tempInput.select();
            document.execCommand('copy');
            document.body.removeChild(tempInput);
        } else {
            navigator.clipboard.writeText(accountNumber);
        }

        const originalText = buttonEl.textContent;
        buttonEl.textContent = 'Copied!';
        buttonEl.classList.remove('bg-[#007bff]', 'hover:bg-[#0069d9]');
        buttonEl.classList.add('bg-emerald-600', 'hover:bg-emerald-700');

        if (window.showToast) {
            window.showToast({ type: 'success', title: 'Account Copied', message: 'Account ' + accountNumber + ' copied to clipboard.' });
        }

        setTimeout(() => {
            buttonEl.textContent = originalText;
            buttonEl.classList.remove('bg-emerald-600', 'hover:bg-emerald-700');
            buttonEl.classList.add('bg-[#007bff]', 'hover:bg-[#0069d9]');
        }, 2000);
    }
</script>
@endsection
