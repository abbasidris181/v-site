@extends('layouts.guest')

@section('title', 'Sign In')

@section('content')
<div class="space-y-6">
    <div>
        <h3 class="text-xl font-bold text-slate-900 dark:text-white tracking-tight">Sign in to your account</h3>
        <p class="mt-1 text-xs text-slate-500 dark:text-slate-400">Enter your credentials to access identity services and your wallet.</p>
    </div>

    <form method="POST" action="{{ route('login') }}" class="space-y-5">
        @csrf

        <!-- Email Address -->
        <div>
            <label for="email" class="block text-xs font-medium uppercase tracking-wider text-slate-700 dark:text-slate-300">
                Email Address
            </label>
            <div class="mt-1.5 relative rounded-xl shadow-sm">
                <input id="email" name="email" type="email" autocomplete="email" required autofocus
                    value="{{ old('email') }}"
                    class="block w-full rounded-xl border border-slate-300 dark:border-slate-700 bg-white dark:bg-slate-800/80 px-4 py-3 text-sm text-slate-900 dark:text-white placeholder-slate-400 dark:placeholder-slate-500 focus:border-emerald-500 focus:outline-none focus:ring-2 focus:ring-emerald-500/20 transition-all duration-150"
                    placeholder="name@business.ng">
            </div>
        </div>

        <!-- Password -->
        <div>
            <label for="password" class="block text-xs font-medium uppercase tracking-wider text-slate-700 dark:text-slate-300">
                Password
            </label>
            <div class="mt-1.5 relative rounded-xl shadow-sm">
                <input id="password" name="password" type="password" autocomplete="current-password" required
                    class="block w-full rounded-xl border border-slate-300 dark:border-slate-700 bg-white dark:bg-slate-800/80 px-4 py-3 text-sm text-slate-900 dark:text-white placeholder-slate-400 dark:placeholder-slate-500 focus:border-emerald-500 focus:outline-none focus:ring-2 focus:ring-emerald-500/20 transition-all duration-150"
                    placeholder="••••••••">
            </div>
        </div>

        <!-- Remember Me -->
        <div class="flex items-center justify-between">
            <div class="flex items-center">
                <input id="remember" name="remember" type="checkbox"
                    class="h-4 w-4 rounded border-slate-300 dark:border-slate-700 bg-white dark:bg-slate-800 text-emerald-600 focus:ring-emerald-500 focus:ring-offset-white dark:focus:ring-offset-slate-900">
                <label for="remember" class="ml-2 block text-xs text-slate-600 dark:text-slate-400">
                    Keep me signed in
                </label>
            </div>
        </div>

        <!-- Submit Button -->
        <div>
            <button type="submit"
                class="w-full flex justify-center py-3.5 px-4 rounded-xl shadow-lg shadow-emerald-600/30 text-sm font-semibold text-white bg-gradient-to-r from-emerald-600 to-teal-600 hover:from-emerald-500 hover:to-teal-500 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-emerald-500 focus:ring-offset-white dark:focus:ring-offset-slate-900 transition-all duration-150 cursor-pointer">
                Sign In to Portal
            </button>
        </div>
    </form>

    <div class="pt-4 border-t border-slate-200 dark:border-slate-800 text-center">
        <p class="text-xs text-slate-500 dark:text-slate-400">
            Don't have an account yet?
            <a href="{{ route('register') }}" class="font-semibold text-emerald-600 dark:text-emerald-400 hover:text-emerald-500 dark:hover:text-emerald-300 transition-colors">
                Create an account
            </a>
        </p>
    </div>
</div>
@endsection
