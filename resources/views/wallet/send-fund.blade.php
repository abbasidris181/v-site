@extends('layouts.app')

@section('title', 'Send Fund by Email or Phone')

@section('content')
<div class="max-w-4xl mx-auto space-y-8">

    <!-- Header -->
    <div>
        <div class="flex items-center gap-2">
            <a href="{{ route('wallet.transactions') }}" class="text-xs font-semibold text-emerald-600 dark:text-emerald-400 hover:underline flex items-center gap-1">
                &larr; Back to Transactions
            </a>
        </div>
        <h1 class="mt-2 text-2xl sm:text-3xl font-extrabold text-slate-900 dark:text-white tracking-tight">
            Send Fund by Email or Phone
        </h1>
        <p class="mt-1 text-sm text-slate-500 dark:text-slate-400">
            Instantly transfer funds to any registered user's email address or phone number.
        </p>
    </div>

    <!-- Flash Error Notification -->
    @if ($errors->any())
        <div class="p-4 rounded-2xl bg-rose-50 dark:bg-rose-500/10 border border-rose-200 dark:border-rose-500/30 text-sm text-rose-800 dark:text-rose-300 shadow-xs">
            <div class="font-bold flex items-center gap-2 mb-1">
                <svg class="w-5 h-5 text-rose-500 flex-shrink-0" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4m0 4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
                </svg>
                <span>Transfer could not be completed:</span>
            </div>
            <ul class="list-disc pl-7 space-y-1 text-xs">
                @foreach ($errors->all() as $error)
                    <li>{{ $error }}</li>
                @endforeach
            </ul>
        </div>
    @endif

    <!-- Main Card Form -->
    <div class="rounded-3xl bg-white dark:bg-slate-900 border border-slate-200/80 dark:border-slate-800 p-6 sm:p-8 shadow-sm dark:shadow-xl">
        <form method="POST" action="{{ route('wallet.transfer') }}" class="space-y-6">
            @csrf

            <!-- Recipient Identifier -->
            <div>
                <label for="recipient" class="block text-xs font-bold uppercase tracking-wider text-slate-700 dark:text-slate-300 mb-2">
                    Recipient Email or Phone Number <span class="text-rose-500">*</span>
                </label>
                <div class="relative">
                    <span class="absolute inset-y-0 left-0 pl-3.5 flex items-center text-slate-400">
                        <svg class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z" />
                        </svg>
                    </span>
                    <input type="text"
                           id="recipient"
                           name="recipient"
                           value="{{ old('recipient') }}"
                           required
                           placeholder="Enter registered email (e.g. user@vsite.ng) or phone number (e.g. 08012345678)"
                           class="w-full pl-11 pr-4 py-3 rounded-xl border border-slate-300 dark:border-slate-700 bg-white dark:bg-slate-800 text-sm font-medium text-slate-900 dark:text-white placeholder-slate-400 focus:border-emerald-500 focus:ring-2 focus:ring-emerald-500/20 transition">
                </div>
                <p class="mt-1.5 text-xs text-slate-400 dark:text-slate-500">
                    The recipient must be a registered user on this portal.
                </p>
                @error('recipient')
                    <p class="mt-1.5 text-xs text-rose-600 dark:text-rose-400 font-medium">{{ $message }}</p>
                @enderror
            </div>

            <!-- Amount (₦) -->
            <div>
                <div class="flex items-center justify-between mb-2">
                    <label for="amount" class="text-xs font-bold uppercase tracking-wider text-slate-700 dark:text-slate-300">
                        Amount to Send (₦) <span class="text-rose-500">*</span>
                    </label>
                    <span class="text-xs text-slate-400">
                        Available: <strong class="text-emerald-600 dark:text-emerald-400">{{ $wallet->formatted_balance }}</strong>
                    </span>
                </div>
                <div class="relative">
                    <span class="absolute inset-y-0 left-0 pl-4 flex items-center text-slate-400 font-bold text-base">₦</span>
                    <input type="number"
                           step="0.01"
                           min="50"
                           id="amount"
                           name="amount"
                           value="{{ old('amount') }}"
                           required
                           placeholder="0.00"
                           class="w-full pl-9 pr-4 py-3 rounded-xl border border-slate-300 dark:border-slate-700 bg-white dark:bg-slate-800 text-slate-900 dark:text-white font-extrabold text-base placeholder-slate-400 focus:border-emerald-500 focus:ring-2 focus:ring-emerald-500/20 transition">
                </div>
                
                <!-- Quick Preset Amounts -->
                <div class="flex items-center gap-2 mt-3 flex-wrap">
                    <span class="text-xs text-slate-400 mr-1">Quick:</span>
                    @foreach([500, 1000, 2000, 5000, 10000] as $preset)
                        <button type="button"
                                onclick="document.getElementById('amount').value = '{{ $preset }}'"
                                class="px-3 py-1 text-xs font-semibold rounded-lg bg-slate-100 hover:bg-emerald-50 dark:bg-slate-800 dark:hover:bg-emerald-500/10 text-slate-700 dark:text-slate-300 hover:text-emerald-600 dark:hover:text-emerald-400 border border-slate-200/80 dark:border-slate-700/60 transition cursor-pointer">
                            +₦{{ number_format($preset) }}
                        </button>
                    @endforeach
                </div>
                @error('amount')
                    <p class="mt-1.5 text-xs text-rose-600 dark:text-rose-400 font-medium">{{ $message }}</p>
                @enderror
            </div>

            <!-- Transfer Note / Description -->
            <div>
                <label for="description" class="block text-xs font-bold uppercase tracking-wider text-slate-700 dark:text-slate-300 mb-2">
                    Transfer Note / Description <span class="text-slate-400 text-[10px] font-normal lowercase">(optional)</span>
                </label>
                <input type="text"
                       id="description"
                       name="description"
                       value="{{ old('description') }}"
                       maxlength="190"
                       placeholder="e.g. Payment for verification service"
                       class="w-full px-4 py-3 rounded-xl border border-slate-300 dark:border-slate-700 bg-white dark:bg-slate-800 text-sm font-medium text-slate-900 dark:text-white placeholder-slate-400 focus:border-emerald-500 focus:ring-2 focus:ring-emerald-500/20 transition">
            </div>

            <!-- Submit Button -->
            <div>
                <button type="submit"
                        class="w-full sm:w-auto px-8 py-3.5 rounded-xl text-sm font-bold text-white bg-gradient-to-r from-emerald-600 via-teal-600 to-emerald-500 hover:from-emerald-500 hover:to-teal-400 shadow-lg shadow-emerald-600/30 transition-all hover:scale-[1.01] cursor-pointer flex items-center justify-center gap-2">
                    <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 19l9 2-9-18-9 18 9-2zm0 0v-8" />
                    </svg>
                    <span>Send Fund Now</span>
                </button>
            </div>
        </form>
    </div>

    <!-- Recent Sent Transfers Table -->
    @if(isset($recentTransfers) && $recentTransfers->count() > 0)
        <div class="rounded-3xl bg-white dark:bg-slate-900 border border-slate-200/80 dark:border-slate-800 p-6 shadow-sm dark:shadow-xl">
            <h3 class="text-sm font-bold text-slate-900 dark:text-white uppercase tracking-wider mb-4">
                Recent Sent Funds
            </h3>
            <div class="overflow-x-auto">
                <table class="w-full text-xs text-left">
                    <thead class="text-[10px] uppercase text-slate-400 border-b border-slate-100 dark:border-slate-800">
                        <tr>
                            <th class="py-2.5 px-3">Date</th>
                            <th class="py-2.5 px-3">Recipient</th>
                            <th class="py-2.5 px-3">Amount</th>
                            <th class="py-2.5 px-3">Reference</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-slate-100 dark:divide-slate-800 text-slate-600 dark:text-slate-300">
                        @foreach($recentTransfers as $tx)
                            <tr>
                                <td class="py-3 px-3">{{ $tx->created_at->format('M d, Y H:i') }}</td>
                                <td class="py-3 px-3 font-semibold text-slate-900 dark:text-white">
                                    {{ $tx->counterpartUser?->full_name ?? 'Portal User' }}
                                    <span class="block text-[10px] text-slate-400 font-normal">{{ $tx->counterpartUser?->email ?? $tx->counterpartUser?->phone_number }}</span>
                                </td>
                                <td class="py-3 px-3 font-bold text-rose-600 dark:text-rose-400">
                                    -{{ $tx->formatted_amount }}
                                </td>
                                <td class="py-3 px-3 font-mono text-[11px] text-slate-400">
                                    {{ $tx->reference }}
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        </div>
    @endif

</div>
@endsection
