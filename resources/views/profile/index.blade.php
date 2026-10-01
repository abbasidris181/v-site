@extends('layouts.app')

@section('title', 'My Profile & Security')

@section('content')
<div class="max-w-5xl mx-auto space-y-8">
    <!-- Breadcrumb -->
    <nav class="flex text-xs font-medium text-slate-500 dark:text-slate-400 space-x-2">
        <a href="{{ route('dashboard') }}" class="hover:text-slate-900 dark:hover:text-white transition-colors">Dashboard</a>
        <span>&rsaquo;</span>
        <span class="text-slate-900 dark:text-white font-semibold">Profile Settings</span>
    </nav>

    <!-- Profile Header Card -->
    <div class="rounded-3xl bg-white dark:bg-slate-900 border border-slate-200/80 dark:border-slate-800 p-6 sm:p-8 shadow-sm dark:shadow-xl">
        <div class="flex flex-col sm:flex-row items-start sm:items-center justify-between gap-6">
            <div class="flex items-center gap-5">
                <div class="w-20 h-20 rounded-2xl bg-gradient-to-tr from-emerald-600 via-teal-500 to-emerald-400 flex items-center justify-center text-white text-2xl font-black shadow-lg shadow-emerald-500/20 ring-4 ring-white dark:ring-slate-800">
                    {{ strtoupper(substr($user->first_name, 0, 1) . substr($user->surname, 0, 1)) }}
                </div>
                <div>
                    <div class="flex items-center gap-2.5 flex-wrap">
                        <h1 class="text-2xl font-extrabold text-slate-900 dark:text-white tracking-tight">
                            {{ $user->full_name }}
                        </h1>
                        <span class="px-2.5 py-0.5 rounded-full text-xs font-bold uppercase tracking-wider bg-emerald-50 dark:bg-emerald-500/10 text-emerald-700 dark:text-emerald-400 border border-emerald-200 dark:border-emerald-500/20">
                            {{ $user->roles->pluck('name')->first() ?: 'End User' }}
                        </span>
                    </div>
                    {{-- <p class="text-sm text-slate-500 dark:text-slate-400 mt-1">
                        {{ $user->business ?: 'Personal Account' }} &bull; Member since {{ $user->created_at ? \Carbon\Carbon::parse($user->created_at)->format('M Y') : '' }}
                    </p> --}}
                </div>
            </div>
        </div>
    </div>

    <!-- Details Grid -->
    <div class="grid grid-cols-1 lg:grid-cols-3 gap-8">
        <!-- Account Information & Verifications -->
        <div class="lg:col-span-2 space-y-6">
            <!-- Personal Information -->
            <div class="rounded-2xl bg-white dark:bg-slate-900 border border-slate-200/80 dark:border-slate-800 p-6 shadow-sm">
                <h2 class="text-base font-bold text-slate-900 dark:text-white mb-4 flex items-center gap-2">
                    <svg class="w-5 h-5 text-emerald-600 dark:text-emerald-400" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z" />
                    </svg>
                    <span>Personal & Business Information</span>
                </h2>

                <div class="grid grid-cols-1 sm:grid-cols-2 gap-4 text-sm">
                    <div class="p-3.5 rounded-xl bg-slate-50 dark:bg-slate-800/50 border border-slate-100 dark:border-slate-800">
                        <div class="text-xs font-medium text-slate-400">Full Name</div>
                        <div class="font-semibold text-slate-800 dark:text-slate-200 mt-0.5">{{ $user->full_name }}</div>
                    </div>
                    <div class="p-3.5 rounded-xl bg-slate-50 dark:bg-slate-800/50 border border-slate-100 dark:border-slate-800">
                        <div class="text-xs font-medium text-slate-400">Email Address</div>
                        <div class="font-semibold text-slate-800 dark:text-slate-200 mt-0.5">{{ $user->email }}</div>
                    </div>
                    <div class="p-3.5 rounded-xl bg-slate-50 dark:bg-slate-800/50 border border-slate-100 dark:border-slate-800">
                        <div class="text-xs font-medium text-slate-400">Phone Number</div>
                        <div class="font-semibold text-slate-800 dark:text-slate-200 mt-0.5">{{ $user->phone_number }}</div>
                    </div>
                    <div class="p-3.5 rounded-xl bg-slate-50 dark:bg-slate-800/50 border border-slate-100 dark:border-slate-800">
                        <div class="text-xs font-medium text-slate-400">Business / Organization</div>
                        <div class="font-semibold text-slate-800 dark:text-slate-200 mt-0.5">{{ $user->business }}</div>
                    </div>
                </div>
            </div>

            <!-- Dual Verification Status -->
            <div class="rounded-2xl bg-white dark:bg-slate-900 border border-slate-200/80 dark:border-slate-800 p-6 shadow-sm">
                <h2 class="text-base font-bold text-slate-900 dark:text-white mb-4 flex items-center gap-2">
                    <svg class="w-5 h-5 text-emerald-600 dark:text-emerald-400" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m5.618-4.016A11.955 11.955 0 0112 2.944a11.955 11.955 0 01-8.618 3.04A12.02 12.02 0 003 9c0 5.591 3.824 10.29 9 11.622 5.176-1.332 9-6.03 9-11.622 0-1.042-.133-2.052-.382-3.016z" />
                    </svg>
                    <span>Verification Status </span>
                </h2>

                <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                    <!-- Email Verification -->
                    <div class="p-4 rounded-xl border border-emerald-200 dark:border-emerald-500/20 bg-emerald-50/50 dark:bg-emerald-500/5 flex items-start gap-3">
                        <div class="w-8 h-8 rounded-lg bg-emerald-100 dark:bg-emerald-500/20 text-emerald-600 dark:text-emerald-400 flex items-center justify-center flex-shrink-0">
                            <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7" />
                            </svg>
                        </div>
                        <div>
                            <div class="text-xs font-bold text-emerald-800 dark:text-emerald-300">Email Address Verified</div>
                            <div class="text-[11px] text-slate-500 dark:text-slate-400 mt-0.5">
                                {{ $user->email_verified_at ? \Carbon\Carbon::parse($user->email_verified_at)->format('d M Y, h:i A') : 'Pending' }}
                            </div>
                        </div>
                    </div>

                    <!-- Phone Verification -->
                    <div class="p-4 rounded-xl border border-emerald-200 dark:border-emerald-500/20 bg-emerald-50/50 dark:bg-emerald-500/5 flex items-start gap-3">
                        <div class="w-8 h-8 rounded-lg bg-emerald-100 dark:bg-emerald-500/20 text-emerald-600 dark:text-emerald-400 flex items-center justify-center flex-shrink-0">
                            <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7" />
                            </svg>
                        </div>
                        <div>
                            <div class="text-xs font-bold text-emerald-800 dark:text-emerald-300">Phone Number Verified</div>
                            <div class="text-[11px] text-slate-500 dark:text-slate-400 mt-0.5">
                                {{ $user->phone_verified_at ? \Carbon\Carbon::parse($user->phone_verified_at)->format('d M Y, h:i A') : 'Pending' }}
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Security & Password Update -->
        <div class="space-y-6">
            <div class="rounded-2xl bg-white dark:bg-slate-900 border border-slate-200/80 dark:border-slate-800 p-6 shadow-sm">
                <h2 class="text-base font-bold text-slate-900 dark:text-white mb-2 flex items-center gap-2">
                    <svg class="w-5 h-5 text-indigo-600 dark:text-indigo-400" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 15v2m-6 4h12a2 2 0 002-2v-6a2 2 0 00-2-2H6a2 2 0 00-2 2v6a2 2 0 002 2zm10-10V7a4 4 0 00-8 0v4h8z" />
                    </svg>
                    <span>Change Password</span>
                </h2>
                <p class="text-xs text-slate-500 dark:text-slate-400 mb-4">Ensure your account uses a strong, unique password.</p>

                <form method="POST" action="{{ route('profile.password') }}" class="space-y-4">
                    @csrf
                    <div>
                        <label class="block text-xs font-medium text-slate-700 dark:text-slate-300 mb-1">Current Password</label>
                        <input type="password" name="current_password" required
                               class="w-full rounded-xl bg-slate-50 dark:bg-slate-800/80 border border-slate-200 dark:border-slate-700 text-xs px-3.5 py-2.5 focus:ring-2 focus:ring-emerald-500/20 focus:border-emerald-500 outline-hidden">
                        @error('current_password')
                            <span class="text-[11px] text-red-500 mt-1 block">{{ $message }}</span>
                        @enderror
                    </div>

                    <div>
                        <label class="block text-xs font-medium text-slate-700 dark:text-slate-300 mb-1">New Password</label>
                        <input type="password" name="password" required
                               class="w-full rounded-xl bg-slate-50 dark:bg-slate-800/80 border border-slate-200 dark:border-slate-700 text-xs px-3.5 py-2.5 focus:ring-2 focus:ring-emerald-500/20 focus:border-emerald-500 outline-hidden">
                        @error('password')
                            <span class="text-[11px] text-red-500 mt-1 block">{{ $message }}</span>
                        @enderror
                    </div>

                    <div>
                        <label class="block text-xs font-medium text-slate-700 dark:text-slate-300 mb-1">Confirm New Password</label>
                        <input type="password" name="password_confirmation" required
                               class="w-full rounded-xl bg-slate-50 dark:bg-slate-800/80 border border-slate-200 dark:border-slate-700 text-xs px-3.5 py-2.5 focus:ring-2 focus:ring-emerald-500/20 focus:border-emerald-500 outline-hidden">
                    </div>

                    <button type="submit"
                            class="w-full py-2.5 rounded-xl bg-emerald-600 hover:bg-emerald-500 text-white text-xs font-bold shadow-md shadow-emerald-600/20 transition cursor-pointer">
                        Update Password
                    </button>
                </form>
            </div>
        </div>
    </div>
</div>
@endsection
