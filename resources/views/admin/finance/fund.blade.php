@extends('layouts.admin')

@section('content')
<div class="space-y-6 max-w-6xl mx-auto">

    <!-- ==========================================
         PAGE HEADER
         ========================================== -->
    <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-4">
        <div>
            <div class="flex items-center gap-2">
                <span class="text-xs font-bold uppercase tracking-widest text-indigo-700 dark:text-indigo-400 bg-indigo-50 dark:bg-indigo-500/10 px-3 py-1 rounded-full border border-indigo-200 dark:border-indigo-500/30">
                    Wallet &amp; Finance
                </span>
                <span class="text-xs font-semibold text-slate-500 dark:text-slate-400">
                    &bull; User Capital Management
                </span>
            </div>
            <h1 class="mt-2 text-2xl sm:text-3xl font-extrabold text-slate-900 dark:text-white tracking-tight">
                Credit &amp; Debit User
            </h1>
            <p class="mt-1 text-xs sm:text-sm text-slate-500 dark:text-slate-400">
                Adjust customer balances by crediting (depositing) or debiting (deducting) funds directly with real-time audit ledger logging.
            </p>
        </div>

        <div class="flex items-center gap-2">
            <a href="{{ route('admin.finance.transactions') }}"
               class="inline-flex items-center gap-1.5 px-3.5 py-2 rounded-xl text-xs font-semibold text-slate-700 dark:text-slate-200 bg-white dark:bg-slate-900 border border-slate-200/80 dark:border-slate-800 hover:bg-slate-50 dark:hover:bg-slate-800 transition shadow-xs">
                <svg class="w-4 h-4 text-slate-400" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2" />
                </svg>
                <span>Wallet Ledger</span>
            </a>
            <a href="{{ route('admin.finance.history') }}"
               class="inline-flex items-center gap-1.5 px-3.5 py-2 rounded-xl text-xs font-semibold text-slate-700 dark:text-slate-200 bg-white dark:bg-slate-900 border border-slate-200/80 dark:border-slate-800 hover:bg-slate-50 dark:hover:bg-slate-800 transition shadow-xs">
                <svg class="w-4 h-4 text-slate-400" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z" />
                </svg>
                <span>Funding History</span>
            </a>
        </div>
    </div>

    <!-- ==========================================
         VALIDATION ERRORS & FLASH ALERTS
         ========================================== -->
    @if ($errors->any())
        <div class="p-4 rounded-2xl bg-red-50 dark:bg-red-500/10 border border-red-200 dark:border-red-500/30 text-sm text-red-800 dark:text-red-300 shadow-xs">
            <div class="font-bold flex items-center gap-2 mb-1">
                <svg class="w-5 h-5 text-red-500 flex-shrink-0" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4m0 4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
                </svg>
                <span>Operation could not be processed:</span>
            </div>
            <ul class="list-disc pl-7 space-y-1 text-xs">
                @foreach ($errors->all() as $error)
                    <li>{{ $error }}</li>
                @endforeach
            </ul>
        </div>
    @endif

    @if (session('success'))
        <div class="p-4 rounded-2xl bg-emerald-50 dark:bg-emerald-500/10 border border-emerald-200 dark:border-emerald-500/30 text-sm text-emerald-800 dark:text-emerald-300 shadow-xs flex items-center gap-3">
            <svg class="w-5 h-5 text-emerald-500 flex-shrink-0" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7" />
            </svg>
            <div class="font-medium text-xs sm:text-sm">
                {{ session('success') }}
            </div>
        </div>
    @endif

    <div class="grid grid-cols-1 lg:grid-cols-3 gap-6 items-start">

        <!-- ==========================================
             LEFT COLUMN: CREDIT / DEBIT FORM (2 COLS)
             ========================================== -->
        <div class="lg:col-span-2 space-y-6">
            <div class="rounded-2xl bg-white dark:bg-slate-900 border border-slate-200/80 dark:border-slate-800 p-5 sm:p-6 shadow-sm">

                <!-- Mode Switcher: Credit vs Debit -->
                <div class="flex items-center justify-between gap-4 pb-5 border-b border-slate-100 dark:border-slate-800">
                    <div>
                        <h2 class="text-base font-bold text-slate-900 dark:text-white flex items-center gap-2">
                            <span id="header-icon-credit" class="w-2.5 h-2.5 rounded-full bg-emerald-500"></span>
                            <span id="header-icon-debit" class="w-2.5 h-2.5 rounded-full bg-rose-500 hidden"></span>
                            <span id="form-heading-text">Credit User Wallet</span>
                        </h2>
                        <p id="form-subtitle-text" class="text-xs text-slate-500 dark:text-slate-400 mt-0.5">
                            Deposit funds directly to a user's wallet with ledger audit records.
                        </p>
                    </div>

                    <!-- Operation Selector Buttons -->
                    <div class="inline-flex p-1 rounded-xl bg-slate-100 dark:bg-slate-800/80 border border-slate-200/80 dark:border-slate-700/60">
                        <button type="button"
                                id="btn-mode-credit"
                                onclick="setOperationMode('credit')"
                                class="inline-flex items-center gap-1.5 px-3.5 py-1.5 rounded-lg text-xs font-bold transition-all shadow-xs bg-white dark:bg-slate-900 text-emerald-700 dark:text-emerald-400 border border-slate-200/60 dark:border-slate-700 cursor-pointer">
                            <svg class="w-3.5 h-3.5 text-emerald-600" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M12 6v12m6-6H6" />
                            </svg>
                            <span>Credit User</span>
                        </button>
                        <button type="button"
                                id="btn-mode-debit"
                                onclick="setOperationMode('debit')"
                                class="inline-flex items-center gap-1.5 px-3.5 py-1.5 rounded-lg text-xs font-bold transition-all text-slate-600 dark:text-slate-400 hover:text-slate-900 dark:hover:text-white cursor-pointer">
                            <svg class="w-3.5 h-3.5 text-rose-600" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M18 12H6" />
                            </svg>
                            <span>Debit User</span>
                        </button>
                    </div>
                </div>

                @if($selectedUser)
                    <!-- Selected Recipient Profile Banner -->
                    <div class="mt-5 p-4 rounded-xl bg-indigo-50/50 dark:bg-indigo-950/20 border border-indigo-200/70 dark:border-indigo-500/30 flex items-center justify-between">
                        <div class="flex items-center gap-3">
                            <div class="w-11 h-11 rounded-xl bg-gradient-to-tr from-indigo-600 to-violet-500 text-white flex items-center justify-center font-bold text-sm shadow-xs">
                                {{ strtoupper(substr($selectedUser->first_name, 0, 1) . substr($selectedUser->surname, 0, 1)) }}
                            </div>
                            <div>
                                <div class="text-sm font-bold text-slate-900 dark:text-white">
                                    {{ $selectedUser->full_name }}
                                </div>
                                <div class="text-xs text-slate-500 dark:text-slate-400">
                                    {{ $selectedUser->email }} &bull; {{ $selectedUser->phone_number ?? 'No phone' }}
                                </div>
                            </div>
                        </div>
                        <div class="text-right">
                            <span class="text-[10px] text-slate-400 uppercase tracking-wider block">Current Balance</span>
                            <span class="text-base font-extrabold text-emerald-600 dark:text-emerald-400">
                                {{ $selectedUser->wallet?->formatted_balance ?? '₦0.00' }}
                            </span>
                        </div>
                    </div>
                @endif

                <form method="POST" action="{{ route('admin.finance.fund.submit') }}" class="mt-5 space-y-5">
                    @csrf

                    <!-- Hidden Input for Operation Mode: credit or debit -->
                    <input type="hidden" name="action_type" id="action-type-input" value="{{ old('action_type', 'credit') }}">

                    @if($selectedUser)
                        <input type="hidden" name="user_id" value="{{ $selectedUser->id }}">
                    @else
                        <!-- Recipient Selection Dropdown -->
                        <div>
                            <label class="block text-xs font-bold text-slate-700 dark:text-slate-300 uppercase tracking-wider mb-1.5">
                                Select Recipient <span class="text-red-500">*</span>
                            </label>
                            <select name="user_id" required
                                    class="w-full px-3.5 py-2.5 text-xs rounded-xl bg-slate-50 dark:bg-slate-950 border border-slate-300 dark:border-slate-700 text-slate-900 dark:text-white focus:ring-2 focus:ring-indigo-500">
                                <option value="">-- Choose User by Name / Email --</option>
                                @foreach($allUsers as $u)
                                    <option value="{{ $u->id }}" {{ old('user_id') == $u->id ? 'selected' : '' }}>
                                        {{ $u->full_name }} ({{ $u->email }}) — Balance: {{ $u->wallet?->formatted_balance ?? '₦0.00' }}
                                    </option>
                                @endforeach
                            </select>
                        </div>
                    @endif

                    <!-- Credit Mode Channels (hidden when debit mode is selected) -->
                    <div id="credit-channels-wrapper">
                        <label class="block text-xs font-bold text-slate-700 dark:text-slate-300 uppercase tracking-wider mb-2">
                            Credit Funding Channel <span class="text-red-500">*</span>
                        </label>
                        <div class="grid grid-cols-1 sm:grid-cols-2 gap-3">
                            <label class="flex items-start gap-3 p-3.5 rounded-xl border border-indigo-200 dark:border-indigo-500/30 bg-indigo-50/30 dark:bg-indigo-950/10 cursor-pointer">
                                <input type="radio" name="funding_type" value="direct_credit" checked class="mt-0.5 text-indigo-600">
                                <div>
                                    <span class="block text-xs font-bold text-slate-900 dark:text-white">Direct Platform Credit</span>
                                    <span class="block text-[11px] text-slate-500 dark:text-slate-400 mt-0.5">Mints fresh balance directly into recipient's wallet without debiting admin balance.</span>
                                </div>
                            </label>
                            <label class="flex items-start gap-3 p-3.5 rounded-xl border border-slate-200 dark:border-slate-800 bg-slate-50/50 dark:bg-slate-950/50 cursor-pointer">
                                <input type="radio" name="funding_type" value="admin_transfer" class="mt-0.5 text-indigo-600">
                                <div>
                                    <span class="block text-xs font-bold text-slate-900 dark:text-white">Debit from My Admin Wallet</span>
                                    <span class="block text-[11px] text-slate-500 dark:text-slate-400 mt-0.5">Transfers funds from your administrative wallet (Available: {{ auth()->user()->wallet?->formatted_balance ?? '₦0.00' }}).</span>
                                </div>
                            </label>
                        </div>
                    </div>

                    <!-- Debit Mode Notice (hidden when credit mode is selected) -->
                    <div id="debit-notice-wrapper" class="hidden p-4 rounded-xl bg-rose-50/60 dark:bg-rose-950/20 border border-rose-200 dark:border-rose-500/30">
                        <div class="flex items-center gap-2 text-xs font-bold text-rose-800 dark:text-rose-300">
                            <svg class="w-4 h-4 text-rose-600" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z" />
                            </svg>
                            <span>Direct Balance Deduction</span>
                        </div>
                        <p class="text-[11px] text-rose-700 dark:text-rose-300/80 mt-1">
                            The specified amount will be immediately deducted from the user's available wallet balance. Overdrafts are protected: if the user balance is lower than the debit amount, the operation will be rejected.
                        </p>
                    </div>

                    <!-- Amount Input -->
                    <div>
                        <label class="block text-xs font-bold text-slate-700 dark:text-slate-300 uppercase tracking-wider mb-1.5">
                            <span id="amount-label-text">Amount (₦)</span> <span class="text-red-500">*</span>
                        </label>
                        <div class="relative">
                            <span class="absolute inset-y-0 left-0 pl-3.5 flex items-center font-bold text-slate-400 text-sm">₦</span>
                            <input type="number" step="0.01" min="1" max="10000000" name="amount" id="fund-amount" required
                                   value="{{ old('amount', '1000.00') }}"
                                   placeholder="0.00"
                                   class="w-full pl-8 pr-3.5 py-2.5 text-sm font-bold rounded-xl bg-slate-50 dark:bg-slate-950 border border-slate-300 dark:border-slate-700 text-slate-900 dark:text-white focus:ring-2 focus:ring-indigo-500">
                        </div>

                        <!-- Quick Presets -->
                        <div class="flex items-center gap-1.5 mt-2 overflow-x-auto text-[11px]">
                            <span class="text-slate-400">Presets:</span>
                            @foreach([500, 1000, 2000, 5000, 10000, 20000, 50000] as $preset)
                                <button type="button" onclick="document.getElementById('fund-amount').value = '{{ $preset }}.00'"
                                        class="px-2 py-0.5 rounded-lg bg-slate-100 dark:bg-slate-800 text-slate-700 dark:text-slate-300 hover:bg-indigo-100 hover:text-indigo-700 dark:hover:bg-indigo-500/20 dark:hover:text-indigo-300 font-semibold transition cursor-pointer">
                                    ₦{{ number_format($preset) }}
                                </button>
                            @endforeach
                        </div>
                    </div>

                    <!-- Description / Audit Remarks -->
                    <div>
                        <label class="block text-xs font-bold text-slate-700 dark:text-slate-300 uppercase tracking-wider mb-1.5">
                            Memo / Audit Remarks
                        </label>
                        <input type="text" name="description" id="description-input" maxlength="191"
                               value="{{ old('description') }}"
                               placeholder="e.g. Agent liquidity top-up, bonus, correction, penalty..."
                               class="w-full px-3.5 py-2.5 text-xs rounded-xl bg-slate-50 dark:bg-slate-950 border border-slate-300 dark:border-slate-700 text-slate-900 dark:text-white focus:ring-2 focus:ring-indigo-500">
                    </div>

                    <!-- Submit Button -->
                    <div class="pt-2">
                        <button type="submit"
                                id="submit-btn"
                                onclick="return confirmOperation()"
                                class="w-full py-3 px-4 rounded-xl text-white text-xs font-bold shadow-md transition cursor-pointer flex items-center justify-center gap-2 bg-gradient-to-r from-indigo-600 via-indigo-600 to-violet-600 hover:from-indigo-500 hover:to-violet-500 shadow-indigo-600/20">
                            <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 10V3L4 14h7v7l9-11h-7z" />
                            </svg>
                            <span id="submit-btn-text">Execute Credit Operation Now</span>
                        </button>
                    </div>
                </form>
            </div>
        </div>

        <!-- ==========================================
             RIGHT COLUMN: USER SEARCH & RECENT AUDIT
             ========================================== -->
        <div class="space-y-6">

            <!-- Search / Switch User Card -->
            <div class="rounded-2xl bg-white dark:bg-slate-900 border border-slate-200/80 dark:border-slate-800 p-5 shadow-sm">
                <h3 class="text-sm font-bold text-slate-900 dark:text-white mb-1">Find &amp; Switch User</h3>
                <p class="text-xs text-slate-500 dark:text-slate-400 mb-3">Search users by name, email, or phone to load wallet details.</p>
                <form method="GET" action="{{ route('admin.finance.fund') }}" class="space-y-2">
                    <input type="text" name="search" value="{{ $search }}" placeholder="Search name, email, phone..."
                           class="w-full px-3 py-2 text-xs rounded-xl bg-slate-50 dark:bg-slate-950 border border-slate-300 dark:border-slate-700 text-slate-900 dark:text-white">
                    <button type="submit"
                            class="w-full py-2 px-3 rounded-xl bg-slate-100 hover:bg-slate-200 dark:bg-slate-800 dark:hover:bg-slate-700 text-xs font-bold text-slate-800 dark:text-white transition cursor-pointer">
                        Search User
                    </button>
                </form>

                @if($userResults->isNotEmpty())
                    <div class="mt-4 space-y-1.5 pt-3 border-t border-slate-100 dark:border-slate-800">
                        <span class="text-[10px] uppercase font-bold text-slate-400 tracking-wider">Search Results:</span>
                        @foreach($userResults as $res)
                            <a href="{{ route('admin.finance.fund', ['user_id' => $res->id]) }}"
                               class="flex items-center justify-between p-2 rounded-lg hover:bg-slate-100 dark:hover:bg-slate-800 text-xs transition">
                                <div>
                                    <div class="font-semibold text-slate-900 dark:text-white">{{ $res->full_name }}</div>
                                    <div class="text-[10px] text-slate-400">{{ $res->email }}</div>
                                </div>
                                <span class="font-bold text-emerald-600 dark:text-emerald-400 text-xs">
                                    {{ $res->wallet?->formatted_balance ?? '₦0.00' }}
                                </span>
                            </a>
                        @endforeach
                    </div>
                @endif
            </div>

            <!-- Recent Operations Audit Card -->
            <div class="rounded-2xl bg-white dark:bg-slate-900 border border-slate-200/80 dark:border-slate-800 p-5 shadow-sm">
                <div class="flex items-center justify-between mb-3">
                    <h3 class="text-sm font-bold text-slate-900 dark:text-white">Recent Operations</h3>
                    <span class="text-[11px] text-slate-400">Live Audit</span>
                </div>
                <div class="space-y-2.5">
                    @forelse($recentOperations as $rec)
                        @php
                            $isCredit = ($rec->type === 'credit');
                        @endphp
                        <div class="flex items-center justify-between text-xs py-2 border-b border-slate-100 dark:border-slate-800/80 last:border-b-0">
                            <div>
                                <div class="font-semibold text-slate-800 dark:text-slate-200">
                                    {{ $rec->user->full_name ?? 'System User' }}
                                </div>
                                <div class="text-[10px] text-slate-400">
                                    {{ $rec->created_at->format('M d, H:i') }} &bull; {{ ucfirst(str_replace('_', ' ', $rec->category)) }}
                                </div>
                            </div>
                            <span class="font-bold text-xs px-2 py-0.5 rounded-md {{ $isCredit ? 'bg-emerald-50 text-emerald-700 dark:bg-emerald-500/10 dark:text-emerald-400 border border-emerald-200/60 dark:border-emerald-500/20' : 'bg-rose-50 text-rose-700 dark:bg-rose-500/10 dark:text-rose-400 border border-rose-200/60 dark:border-rose-500/20' }}">
                                {{ $isCredit ? '+' : '-' }}₦{{ number_format($rec->amount, 2) }}
                            </span>
                        </div>
                    @empty
                        <p class="text-xs text-slate-400">No recent credit or debit activity.</p>
                    @endforelse
                </div>
            </div>

        </div>
    </div>
</div>

<script>
    function setOperationMode(mode) {
        const actionTypeInput = document.getElementById('action-type-input');
        const creditChannels = document.getElementById('credit-channels-wrapper');
        const debitNotice = document.getElementById('debit-notice-wrapper');
        const btnCredit = document.getElementById('btn-mode-credit');
        const btnDebit = document.getElementById('btn-mode-debit');
        const iconCredit = document.getElementById('header-icon-credit');
        const iconDebit = document.getElementById('header-icon-debit');
        const headingText = document.getElementById('form-heading-text');
        const subtitleText = document.getElementById('form-subtitle-text');
        const submitBtn = document.getElementById('submit-btn');
        const submitBtnText = document.getElementById('submit-btn-text');
        const descriptionInput = document.getElementById('description-input');

        actionTypeInput.value = mode;

        if (mode === 'debit') {
            // Debit Active
            btnDebit.className = 'inline-flex items-center gap-1.5 px-3.5 py-1.5 rounded-lg text-xs font-bold transition-all shadow-xs bg-white dark:bg-slate-900 text-rose-700 dark:text-rose-400 border border-slate-200/60 dark:border-slate-700 cursor-pointer';
            btnCredit.className = 'inline-flex items-center gap-1.5 px-3.5 py-1.5 rounded-lg text-xs font-bold transition-all text-slate-600 dark:text-slate-400 hover:text-slate-900 dark:hover:text-white cursor-pointer';

            creditChannels.classList.add('hidden');
            debitNotice.classList.remove('hidden');

            iconCredit.classList.add('hidden');
            iconDebit.classList.remove('hidden');

            headingText.textContent = 'Debit User Wallet';
            subtitleText.textContent = 'Deduct funds directly from a customer wallet balance with audit trails.';

            submitBtn.className = 'w-full py-3 px-4 rounded-xl text-white text-xs font-bold shadow-md transition cursor-pointer flex items-center justify-center gap-2 bg-gradient-to-r from-rose-600 via-rose-600 to-red-600 hover:from-rose-500 hover:to-red-500 shadow-rose-600/20';
            submitBtnText.textContent = 'Execute Debit Operation Now';

            if (!descriptionInput.value || descriptionInput.value === 'Administrative Wallet Credit') {
                descriptionInput.value = 'Administrative Wallet Debit';
            }
        } else {
            // Credit Active
            btnCredit.className = 'inline-flex items-center gap-1.5 px-3.5 py-1.5 rounded-lg text-xs font-bold transition-all shadow-xs bg-white dark:bg-slate-900 text-emerald-700 dark:text-emerald-400 border border-slate-200/60 dark:border-slate-700 cursor-pointer';
            btnDebit.className = 'inline-flex items-center gap-1.5 px-3.5 py-1.5 rounded-lg text-xs font-bold transition-all text-slate-600 dark:text-slate-400 hover:text-slate-900 dark:hover:text-white cursor-pointer';

            creditChannels.classList.remove('hidden');
            debitNotice.classList.add('hidden');

            iconCredit.classList.remove('hidden');
            iconDebit.classList.add('hidden');

            headingText.textContent = 'Credit User Wallet';
            subtitleText.textContent = 'Deposit funds directly to a user\'s wallet with ledger audit records.';

            submitBtn.className = 'w-full py-3 px-4 rounded-xl text-white text-xs font-bold shadow-md transition cursor-pointer flex items-center justify-center gap-2 bg-gradient-to-r from-indigo-600 via-indigo-600 to-violet-600 hover:from-indigo-500 hover:to-violet-500 shadow-indigo-600/20';
            submitBtnText.textContent = 'Execute Credit Operation Now';

            if (!descriptionInput.value || descriptionInput.value === 'Administrative Wallet Debit') {
                descriptionInput.value = 'Administrative Wallet Credit';
            }
        }
    }

    function confirmOperation() {
        const mode = document.getElementById('action-type-input').value;
        const amount = document.getElementById('fund-amount').value || '0.00';
        const actionLabel = (mode === 'debit') ? 'DEBIT (deduct)' : 'CREDIT (deposit)';
        return confirm(`Authorize ${actionLabel} of ₦${amount} now? This operation is atomic and generates an immutable ledger record.`);
    }

    // Initialize mode if previously submitted with errors
    document.addEventListener('DOMContentLoaded', function () {
        const initialMode = document.getElementById('action-type-input').value || 'credit';
        setOperationMode(initialMode);
    });
</script>
@endsection
