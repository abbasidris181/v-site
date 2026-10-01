@extends('layouts.admin')

@section('content')
<div class="space-y-6">
    <!-- Breadcrumb -->
    <nav class="flex text-xs font-medium text-slate-500 dark:text-slate-400 space-x-2">
        <a href="{{ route('admin.dashboard') }}" class="hover:text-slate-900 dark:hover:text-white transition-colors">Admin Dashboard</a>
        <span>&rsaquo;</span>
        <a href="{{ route('admin.users.index') }}" class="hover:text-slate-900 dark:hover:text-white transition-colors">Users Directory</a>
        <span>&rsaquo;</span>
        <span class="text-slate-900 dark:text-white font-semibold">{{ $user->full_name }}</span>
    </nav>

    <!-- User Profile Header Card -->
    <div class="rounded-3xl bg-white dark:bg-slate-900 border border-slate-200/80 dark:border-slate-800 p-6 sm:p-8 shadow-sm">
        <div class="flex flex-col sm:flex-row items-start sm:items-center justify-between gap-6">
            <div class="flex items-center gap-5">
                <div class="w-20 h-20 rounded-2xl bg-gradient-to-tr from-indigo-600 via-indigo-500 to-violet-600 flex items-center justify-center text-white text-2xl font-black shadow-lg shadow-indigo-500/20 ring-4 ring-white dark:ring-slate-800">
                    {{ strtoupper(substr($user->first_name, 0, 1) . substr($user->surname, 0, 1)) }}
                </div>
                <div>
                    <div class="flex items-center gap-2.5 flex-wrap">
                        <h1 class="text-2xl font-extrabold text-slate-900 dark:text-white tracking-tight">
                            {{ $user->full_name }}
                        </h1>
                        @php
                            $primaryRole = $user->roles->first();
                            $roleSlug = $primaryRole ? $primaryRole->slug : 'end_user';
                        @endphp
                        <span class="px-2.5 py-0.5 rounded-full text-xs font-bold uppercase tracking-wider bg-indigo-50 dark:bg-indigo-500/10 text-indigo-700 dark:text-indigo-400 border border-indigo-200 dark:border-indigo-500/20">
                            {{ $primaryRole ? $primaryRole->name : 'End User' }}
                        </span>
                        @if($user->is_active)
                            <span class="px-2.5 py-0.5 rounded-full text-xs font-semibold bg-emerald-50 dark:bg-emerald-500/10 text-emerald-700 dark:text-emerald-400 border border-emerald-200 dark:border-emerald-500/30">
                                Active Account
                            </span>
                        @else
                            <span class="px-2.5 py-0.5 rounded-full text-xs font-semibold bg-red-50 dark:bg-red-500/10 text-red-700 dark:text-red-400 border border-red-200 dark:border-red-500/30">
                                Suspended Account
                            </span>
                        @endif
                    </div>
                    <p class="text-sm text-slate-500 dark:text-slate-400 mt-1">
                        {{ $user->business ?: 'Personal Account' }} &bull; Joined {{ $user->created_at->format('d M Y, h:i A') }}
                    </p>
                </div>
            </div>

            <!-- Wallet Overview Pill -->
            <div class="rounded-2xl bg-slate-50 dark:bg-slate-800/60 border border-slate-200/80 dark:border-slate-700/60 p-4 sm:text-right w-full sm:w-auto">
                <div class="text-xs font-medium text-slate-500 dark:text-slate-400">Available Wallet Balance</div>
                <div class="text-2xl font-black text-emerald-600 dark:text-emerald-400 mt-0.5">
                    {{ $user->wallet?->formatted_balance ?? '₦0.00' }}
                </div>
                <div class="text-[11px] text-slate-400 mt-1">
                    Currency: NGN (Nigerian Naira)
                </div>
            </div>
        </div>
    </div>

    <!-- 2-Column Administrative Workspace -->
    <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
        
        <!-- Left Column: Details & Administrative Controls -->
        <div class="space-y-6">
            <!-- 1. Account Details -->
            <div class="rounded-2xl bg-white dark:bg-slate-900 border border-slate-200/80 dark:border-slate-800 p-6 shadow-sm">
                <h3 class="text-sm font-bold text-slate-900 dark:text-white mb-4 flex items-center gap-2">
                    <svg class="w-4 h-4 text-indigo-600 dark:text-indigo-400" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z" />
                    </svg>
                    <span>Account Profile</span>
                </h3>

                <dl class="space-y-3 text-xs">
                    <div>
                        <dt class="text-slate-400">Email Address</dt>
                        <dd class="font-semibold text-slate-800 dark:text-slate-200 mt-0.5">{{ $user->email }}</dd>
                    </div>
                    <div>
                        <dt class="text-slate-400">Phone Number</dt>
                        <dd class="font-semibold text-slate-800 dark:text-slate-200 mt-0.5">{{ $user->phone_number }}</dd>
                    </div>
                    <div>
                        <dt class="text-slate-400">Business / Organisation</dt>
                        <dd class="font-semibold text-slate-800 dark:text-slate-200 mt-0.5">{{ $user->business ?: 'None provided' }}</dd>
                    </div>
                    <div>
                        <dt class="text-slate-400">Email Verification</dt>
                        <dd class="mt-0.5">
                            @if($user->email_verified_at)
                                <span class="text-emerald-600 dark:text-emerald-400 font-medium">✓ Verified ({{ $user->email_verified_at->format('d M Y') }})</span>
                            @else
                                <span class="text-amber-500 font-medium">⏳ Unverified</span>
                            @endif
                        </dd>
                    </div>
                    <div>
                        <dt class="text-slate-400">Phone Verification</dt>
                        <dd class="mt-0.5">
                            @if($user->phone_verified_at)
                                <span class="text-emerald-600 dark:text-emerald-400 font-medium">✓ Verified ({{ $user->phone_verified_at->format('d M Y') }})</span>
                            @else
                                <span class="text-amber-500 font-medium">⏳ Unverified</span>
                            @endif
                        </dd>
                    </div>
                </dl>
            </div>

            <!-- 2. Account Status Control -->
            @if($user->id !== auth()->id())
                <div class="rounded-2xl bg-white dark:bg-slate-900 border border-slate-200/80 dark:border-slate-800 p-6 shadow-sm">
                    <h3 class="text-sm font-bold text-slate-900 dark:text-white mb-2 flex items-center gap-2">
                        <svg class="w-4 h-4 text-amber-500" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z" />
                        </svg>
                        <span>Access Status</span>
                    </h3>
                    <p class="text-xs text-slate-500 dark:text-slate-400 mb-4">
                        Suspended accounts are immediately locked out from logging in or initiating transactions.
                    </p>

                    <form method="POST" action="{{ route('admin.users.status', $user->id) }}">
                        @csrf
                        <button type="submit"
                                class="w-full py-2.5 rounded-xl font-bold text-xs transition cursor-pointer {{ $user->is_active ? 'bg-red-50 hover:bg-red-100 dark:bg-red-500/10 text-red-700 dark:text-red-400 border border-red-200 dark:border-red-500/30' : 'bg-emerald-600 hover:bg-emerald-500 text-white' }}">
                            {{ $user->is_active ? 'Suspend Account' : 'Activate Account' }}
                        </button>
                    </form>
                </div>
            @endif

            <!-- 3. Password Reset (Admin Capability - SRS Section 5) -->
            <div class="rounded-2xl bg-white dark:bg-slate-900 border border-slate-200/80 dark:border-slate-800 p-6 shadow-sm">
                <h3 class="text-sm font-bold text-slate-900 dark:text-white mb-2 flex items-center gap-2">
                    <svg class="w-4 h-4 text-indigo-600 dark:text-indigo-400" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 7a2 2 0 012 2m4 0a6 6 0 01-7.743 5.743L11 17H9v2H7v2H4a1 1 0 01-1-1v-2.586a1 1 0 01.293-.707l5.964-5.964A6 6 0 1121 9z" />
                    </svg>
                    <span>Reset User Password</span>
                </h3>
                <p class="text-xs text-slate-500 dark:text-slate-400 mb-4">
                    Leave blank to automatically generate a secure temporary password.
                </p>

                <form method="POST" action="{{ route('admin.users.password', $user->id) }}" class="space-y-3">
                    @csrf
                    <div>
                        <label class="block text-xs font-medium text-slate-700 dark:text-slate-300 mb-1">New Password (Optional)</label>
                        <input type="password" name="password" placeholder="Leave empty for auto-generated"
                               class="w-full rounded-xl bg-slate-50 dark:bg-slate-800/80 border border-slate-200 dark:border-slate-700 text-xs px-3.5 py-2.5 focus:ring-2 focus:ring-indigo-500/20 focus:border-indigo-500 outline-hidden">
                    </div>
                    <div>
                        <label class="block text-xs font-medium text-slate-700 dark:text-slate-300 mb-1">Confirm Password</label>
                        <input type="password" name="password_confirmation" placeholder="Confirm password"
                               class="w-full rounded-xl bg-slate-50 dark:bg-slate-800/80 border border-slate-200 dark:border-slate-700 text-xs px-3.5 py-2.5 focus:ring-2 focus:ring-indigo-500/20 focus:border-indigo-500 outline-hidden">
                    </div>

                    <button type="submit"
                            class="w-full py-2.5 rounded-xl bg-indigo-600 hover:bg-indigo-500 text-white text-xs font-bold shadow-xs transition cursor-pointer">
                        Execute Password Reset
                    </button>
                </form>
            </div>

            <!-- 4. Role Reassignment (Super Admin only) -->
            @if(auth()->user()->hasRole('super_admin'))
                <div class="rounded-2xl bg-white dark:bg-slate-900 border border-slate-200/80 dark:border-slate-800 p-6 shadow-sm">
                    <h3 class="text-sm font-bold text-slate-900 dark:text-white mb-2 flex items-center gap-2">
                        <svg class="w-4 h-4 text-purple-600 dark:text-purple-400" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m5.618-4.016A11.955 11.955 0 0112 2.944a11.955 11.955 0 01-8.618 3.04A12.02 12.02 0 003 9c0 5.591 3.824 10.29 9 11.622 5.176-1.332 9-6.03 9-11.622 0-1.042-.133-2.052-.382-3.016z" />
                        </svg>
                        <span>Change Platform Role</span>
                    </h3>
                    <p class="text-xs text-slate-500 dark:text-slate-400 mb-4">
                        Super Administrator privilege to promote or demote user roles.
                    </p>

                    <form method="POST" action="{{ route('admin.users.role', $user->id) }}" class="space-y-3">
                        @csrf
                        <select name="role_id" class="w-full px-3 py-2.5 rounded-xl bg-slate-50 dark:bg-slate-800/80 border border-slate-200 dark:border-slate-700 text-xs focus:ring-2 focus:ring-purple-500/20 focus:border-purple-500 outline-hidden">
                            @foreach($roles as $r)
                                <option value="{{ $r->id }}" {{ $user->hasRole($r->slug) ? 'selected' : '' }}>
                                    {{ $r->name }} ({{ $r->slug }})
                                </option>
                            @endforeach
                        </select>

                        <button type="submit"
                                class="w-full py-2.5 rounded-xl bg-purple-600 hover:bg-purple-500 text-white text-xs font-bold shadow-xs transition cursor-pointer">
                            Update Role Assignment
                        </button>
                    </form>
                </div>
            @endif
        </div>

        <!-- Right Column: Recent Activity Logs (Wallet & Requests) -->
        <div class="lg:col-span-2 space-y-6">
            
            <!-- Recent Wallet Transactions -->
            <div class="rounded-2xl bg-white dark:bg-slate-900 border border-slate-200/80 dark:border-slate-800 shadow-sm overflow-hidden">
                <div class="p-5 border-b border-slate-100 dark:border-slate-800 flex items-center justify-between">
                    <div>
                        <h3 class="text-sm font-bold text-slate-900 dark:text-white">Recent Wallet Transactions</h3>
                        <p class="text-xs text-slate-400 mt-0.5">Audit trail of financial ledger credits and debits</p>
                    </div>
                </div>

                <div class="overflow-x-auto">
                    <table class="min-w-full divide-y divide-slate-200 dark:divide-slate-800 text-left text-xs">
                        <thead class="bg-slate-50 dark:bg-slate-950/60 text-slate-500 dark:text-slate-400 uppercase font-semibold">
                            <tr>
                                <th class="px-5 py-3">Reference</th>
                                <th class="px-5 py-3">Type</th>
                                <th class="px-5 py-3">Category</th>
                                <th class="px-5 py-3">Amount</th>
                                <th class="px-5 py-3">Balance After</th>
                                <th class="px-5 py-3">Date</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-slate-100 dark:divide-slate-800/80">
                            @forelse($recentTransactions as $tx)
                                <tr>
                                    <td class="px-5 py-3 text-slate-800 dark:text-slate-200">
                                        {{ $tx->reference }}
                                    </td>
                                    <td class="px-5 py-3 whitespace-nowrap">
                                        @if($tx->type === 'credit')
                                            <span class="inline-flex items-center px-2 py-0.5 rounded text-[10px] font-bold bg-emerald-50 dark:bg-emerald-500/10 text-emerald-700 dark:text-emerald-400">
                                                Credit
                                            </span>
                                        @else
                                            <span class="inline-flex items-center px-2 py-0.5 rounded text-[10px] font-bold bg-slate-100 dark:bg-slate-800 text-slate-700 dark:text-slate-300">
                                                Debit
                                            </span>
                                        @endif
                                    </td>
                                    <td class="px-5 py-3 capitalize text-slate-600 dark:text-slate-400">
                                        {{ str_replace('_', ' ', $tx->category) }}
                                    </td>
                                    <td class="px-5 py-3 font-bold {{ $tx->type === 'credit' ? 'text-emerald-600 dark:text-emerald-400' : 'text-slate-900 dark:text-white' }}">
                                        {{ $tx->formatted_amount }}
                                    </td>
                                    <td class="px-5 py-3 text-slate-500">
                                        ₦{{ number_format($tx->balance_after, 2) }}
                                    </td>
                                    <td class="px-5 py-3 text-slate-400 whitespace-nowrap">
                                        {{ $tx->created_at->format('d M Y, h:i A') }}
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="6" class="px-5 py-6 text-center text-slate-400">
                                        No financial ledger transactions recorded yet.
                                    </td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>

            <!-- Recent Service Requests -->
            <div class="rounded-2xl bg-white dark:bg-slate-900 border border-slate-200/80 dark:border-slate-800 shadow-sm overflow-hidden">
                <div class="p-5 border-b border-slate-100 dark:border-slate-800 flex items-center justify-between">
                    <div>
                        <h3 class="text-sm font-bold text-slate-900 dark:text-white">Recent Service Submissions</h3>
                        <p class="text-xs text-slate-400 mt-0.5">Clearinghouse jobs and API verifications initiated by user</p>
                    </div>
                </div>

                <div class="overflow-x-auto">
                    <table class="min-w-full divide-y divide-slate-200 dark:divide-slate-800 text-left text-xs">
                        <thead class="bg-slate-50 dark:bg-slate-950/60 text-slate-500 dark:text-slate-400 uppercase font-semibold">
                            <tr>
                                <th class="px-5 py-3">Reference</th>
                                <th class="px-5 py-3">Service</th>
                                <th class="px-5 py-3">Input</th>
                                <th class="px-5 py-3">Status</th>
                                <th class="px-5 py-3">Date</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-slate-100 dark:divide-slate-800/80">
                            @forelse($recentRequests as $sr)
                                <tr>
                                    <td class="px-5 py-3 text-slate-800 dark:text-slate-200">
                                        #{{ $sr->reference }}
                                    </td>
                                    <td class="px-5 py-3 font-medium text-slate-800 dark:text-slate-200">
                                        {{ $sr->service->name }}
                                    </td>
                                    <td class="px-5 py-3 text-slate-500 max-w-xs truncate">
                                        {{ $sr->tracking_input }}
                                    </td>
                                    <td class="px-5 py-3 whitespace-nowrap">
                                        @if($sr->status === 'completed')
                                            <span class="inline-flex items-center px-2 py-0.5 rounded-full text-[10px] font-bold bg-emerald-50 dark:bg-emerald-500/10 text-emerald-700 dark:text-emerald-400">
                                                Completed
                                            </span>
                                        @elseif($sr->status === 'processing')
                                            <span class="inline-flex items-center px-2 py-0.5 rounded-full text-[10px] font-bold bg-indigo-50 dark:bg-indigo-500/10 text-indigo-700 dark:text-indigo-400">
                                                Processing
                                            </span>
                                        @elseif($sr->status === 'failed')
                                            <span class="inline-flex items-center px-2 py-0.5 rounded-full text-[10px] font-bold bg-red-50 dark:bg-red-500/10 text-red-700 dark:text-red-400">
                                                Failed
                                            </span>
                                        @else
                                            <span class="inline-flex items-center px-2 py-0.5 rounded-full text-[10px] font-bold bg-amber-50 dark:bg-amber-500/10 text-amber-700 dark:text-amber-400">
                                                Pending
                                            </span>
                                        @endif
                                    </td>
                                    <td class="px-5 py-3 text-slate-400 whitespace-nowrap">
                                        {{ $sr->created_at->format('d M Y, h:i A') }}
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="5" class="px-5 py-6 text-center text-slate-400">
                                        No service requests submitted yet.
                                    </td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
