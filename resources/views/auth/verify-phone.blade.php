@extends('layouts.guest')

@section('title', 'Verify Phone Number')

@section('content')
<div class="space-y-6 text-center sm:text-left">
    <div class="flex items-center gap-3">
        <div class="w-10 h-10 rounded-xl bg-emerald-500/10 text-emerald-700 dark:text-emerald-400 flex items-center justify-center font-bold">
            2
        </div>
        <div>
            <h3 class="text-xl font-bold text-slate-900 dark:text-white tracking-tight">Step 2: Verify Phone Number</h3>
            <p class="text-xs text-slate-500 dark:text-slate-400">Final step before accessing your dashboard & wallet.</p>
        </div>
    </div>

    <div class="p-4 rounded-xl bg-slate-50 dark:bg-slate-800/60 border border-slate-200 dark:border-slate-700/60 text-sm text-slate-700 dark:text-slate-300 space-y-2">
        <p>
            Your registered phone number:
            <span class="font-semibold text-emerald-600 dark:text-emerald-400">{{ auth()->user()->phone_number }}</span>
        </p>
        <p class="text-xs text-slate-500 dark:text-slate-400">
            Phone verification is an independent security requirement. Verifying your email does not automatically mark your phone number as verified.
            Click below to confirm and activate phone verification.
        </p>
    </div>

    <form method="POST" action="{{ route('verification.phone.verify') }}">
        @csrf
        <button type="submit"
            class="w-full flex justify-center py-3.5 px-4 rounded-xl shadow-lg shadow-emerald-600/30 text-sm font-semibold text-white bg-gradient-to-r from-emerald-600 to-teal-600 hover:from-emerald-500 hover:to-teal-500 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-emerald-500 focus:ring-offset-white dark:focus:ring-offset-slate-900 transition-all duration-150 cursor-pointer">
            Confirm & Verify Phone Number
        </button>
    </form>

    <div class="pt-4 border-t border-slate-200 dark:border-slate-800 flex items-center justify-between text-xs text-slate-500 dark:text-slate-400">
        <span>Need to sign out?</span>
        <form method="POST" action="{{ route('logout') }}">
            @csrf
            <button type="submit" class="text-red-600 dark:text-red-400 hover:text-red-500 dark:hover:text-red-300 font-medium transition-colors cursor-pointer">
                Sign Out
            </button>
        </form>
    </div>
</div>
@endsection
