@extends('layouts.guest')

@section('title', 'Create Account')

@section('content')
<div class="space-y-6">
    <div>
        <h3 class="text-xl font-bold text-slate-900 dark:text-white tracking-tight">Create your account</h3>
        <p class="mt-1 text-xs text-slate-500 dark:text-slate-400">Complete the form below to register on the verification portal.</p>
    </div>

    <form method="POST" action="{{ route('register') }}" class="space-y-4">
        @csrf

        <!-- 1. First Name & 2. Surname -->
        <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
            <div>
                <label for="first_name" class="block text-xs font-medium uppercase tracking-wider text-slate-700 dark:text-slate-300">
                    First Name <span class="text-emerald-600 dark:text-emerald-400">*</span>
                </label>
                <div class="mt-1">
                    <input id="first_name" name="first_name" type="text" required autofocus
                        value="{{ old('first_name') }}"
                        class="block w-full rounded-xl border border-slate-300 dark:border-slate-700 bg-white dark:bg-slate-800/80 px-3.5 py-2.5 text-sm text-slate-900 dark:text-white placeholder-slate-400 dark:placeholder-slate-500 focus:border-emerald-500 focus:outline-none focus:ring-2 focus:ring-emerald-500/20 transition-all duration-150"
                        placeholder="e.g. Ibrahim">
                </div>
            </div>

            <div>
                <label for="surname" class="block text-xs font-medium uppercase tracking-wider text-slate-700 dark:text-slate-300">
                    Surname <span class="text-emerald-600 dark:text-emerald-400">*</span>
                </label>
                <div class="mt-1">
                    <input id="surname" name="surname" type="text" required
                        value="{{ old('surname') }}"
                        class="block w-full rounded-xl border border-slate-300 dark:border-slate-700 bg-white dark:bg-slate-800/80 px-3.5 py-2.5 text-sm text-slate-900 dark:text-white placeholder-slate-400 dark:placeholder-slate-500 focus:border-emerald-500 focus:outline-none focus:ring-2 focus:ring-emerald-500/20 transition-all duration-150"
                        placeholder="e.g. Adeleke">
                </div>
            </div>
        </div>

        <!-- 3. Middle Name (Optional) & 8. Business -->
        <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
            <div>
                <label for="middle_name" class="block text-xs font-medium uppercase tracking-wider text-slate-700 dark:text-slate-300">
                    Middle Name <span class="text-slate-400 dark:text-slate-500 text-[10px] lowercase">(optional)</span>
                </label>
                <div class="mt-1">
                    <input id="middle_name" name="middle_name" type="text"
                        value="{{ old('middle_name') }}"
                        class="block w-full rounded-xl border border-slate-300 dark:border-slate-700 bg-white dark:bg-slate-800/80 px-3.5 py-2.5 text-sm text-slate-900 dark:text-white placeholder-slate-400 dark:placeholder-slate-500 focus:border-emerald-500 focus:outline-none focus:ring-2 focus:ring-emerald-500/20 transition-all duration-150"
                        placeholder="e.g. Chinedu">
                </div>
            </div>

            <div>
                <label for="business" class="block text-xs font-medium uppercase tracking-wider text-slate-700 dark:text-slate-300">
                    Business Name <span class="text-emerald-600 dark:text-emerald-400">*</span>
                </label>
                <div class="mt-1">
                    <input id="business" name="business" type="text" required
                        value="{{ old('business') }}"
                        class="block w-full rounded-xl border border-slate-300 dark:border-slate-700 bg-white dark:bg-slate-800/80 px-3.5 py-2.5 text-sm text-slate-900 dark:text-white placeholder-slate-400 dark:placeholder-slate-500 focus:border-emerald-500 focus:outline-none focus:ring-2 focus:ring-emerald-500/20 transition-all duration-150"
                        placeholder="e.g. Adeleke Ventures">
                </div>
            </div>
        </div>

        <!-- 4. Email Address & 5. Phone Number -->
        <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
            <div>
                <label for="email" class="block text-xs font-medium uppercase tracking-wider text-slate-700 dark:text-slate-300">
                    Email Address <span class="text-emerald-600 dark:text-emerald-400">*</span>
                </label>
                <div class="mt-1">
                    <input id="email" name="email" type="email" required
                        value="{{ old('email') }}"
                        class="block w-full rounded-xl border border-slate-300 dark:border-slate-700 bg-white dark:bg-slate-800/80 px-3.5 py-2.5 text-sm text-slate-900 dark:text-white placeholder-slate-400 dark:placeholder-slate-500 focus:border-emerald-500 focus:outline-none focus:ring-2 focus:ring-emerald-500/20 transition-all duration-150"
                        placeholder="you@domain.ng">
                </div>
            </div>

            <div>
                <label for="phone_number" class="block text-xs font-medium uppercase tracking-wider text-slate-700 dark:text-slate-300">
                    Phone Number <span class="text-emerald-600 dark:text-emerald-400">*</span>
                </label>
                <div class="mt-1">
                    <input id="phone_number" name="phone_number" type="tel" required
                        value="{{ old('phone_number') }}"
                        class="block w-full rounded-xl border border-slate-300 dark:border-slate-700 bg-white dark:bg-slate-800/80 px-3.5 py-2.5 text-sm text-slate-900 dark:text-white placeholder-slate-400 dark:placeholder-slate-500 focus:border-emerald-500 focus:outline-none focus:ring-2 focus:ring-emerald-500/20 transition-all duration-150"
                        placeholder="08012345678">
                </div>
            </div>
        </div>

        <!-- 6. Password & 7. Confirm Password -->
        <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
            <div>
                <label for="password" class="block text-xs font-medium uppercase tracking-wider text-slate-700 dark:text-slate-300">
                    Password <span class="text-emerald-600 dark:text-emerald-400">*</span>
                </label>
                <div class="mt-1">
                    <input id="password" name="password" type="password" required autocomplete="new-password"
                        class="block w-full rounded-xl border border-slate-300 dark:border-slate-700 bg-white dark:bg-slate-800/80 px-3.5 py-2.5 text-sm text-slate-900 dark:text-white placeholder-slate-400 dark:placeholder-slate-500 focus:border-emerald-500 focus:outline-none focus:ring-2 focus:ring-emerald-500/20 transition-all duration-150"
                        placeholder="••••••••">
                </div>
            </div>

            <div>
                <label for="password_confirmation" class="block text-xs font-medium uppercase tracking-wider text-slate-700 dark:text-slate-300">
                    Confirm Password <span class="text-emerald-600 dark:text-emerald-400">*</span>
                </label>
                <div class="mt-1">
                    <input id="password_confirmation" name="password_confirmation" type="password" required autocomplete="new-password"
                        class="block w-full rounded-xl border border-slate-300 dark:border-slate-700 bg-white dark:bg-slate-800/80 px-3.5 py-2.5 text-sm text-slate-900 dark:text-white placeholder-slate-400 dark:placeholder-slate-500 focus:border-emerald-500 focus:outline-none focus:ring-2 focus:ring-emerald-500/20 transition-all duration-150"
                        placeholder="••••••••">
                </div>
            </div>
        </div>

        <div class="pt-2">
            <button type="submit"
                class="w-full flex justify-center py-3.5 px-4 rounded-xl shadow-lg shadow-emerald-600/30 text-sm font-semibold text-white bg-gradient-to-r from-emerald-600 to-teal-600 hover:from-emerald-500 hover:to-teal-500 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-emerald-500 focus:ring-offset-white dark:focus:ring-offset-slate-900 transition-all duration-150 cursor-pointer">
                Create Portal Account
            </button>
        </div>
    </form>

    <div class="pt-4 border-t border-slate-200 dark:border-slate-800 text-center">
        <p class="text-xs text-slate-500 dark:text-slate-400">
            Already have an account?
            <a href="{{ route('login') }}" class="font-semibold text-emerald-600 dark:text-emerald-400 hover:text-emerald-500 dark:hover:text-emerald-300 transition-colors">
                Sign in instead
            </a>
        </p>
    </div>
</div>
@endsection
