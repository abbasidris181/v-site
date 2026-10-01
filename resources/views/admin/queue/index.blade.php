@extends('layouts.admin')

@section('content')
@php
    $isIpeService = in_array($singleService?->slug, ['ipe-clearing', 'modification-ipe']);
    $svcName = $singleService ? ($singleService->slug === 'ipe-clearing' ? 'IPE' : $singleService->name) : 'Service';
    $inputColumnHeader = $isIpeService ? 'Old Tracking ID' : 'Input';
@endphp

<div class="space-y-6">
    @if(session('success'))
        <div class="p-4 rounded-xl bg-emerald-50 dark:bg-emerald-500/10 border border-emerald-200 dark:border-emerald-500/30 text-emerald-800 dark:text-emerald-300 text-xs font-semibold">
            {{ session('success') }}
        </div>
    @endif

    @if(session('error'))
        <div class="p-4 rounded-xl bg-red-50 dark:bg-red-500/10 border border-red-200 dark:border-red-500/30 text-red-800 dark:text-red-300 text-xs font-semibold">
            {{ session('error') }}
        </div>
    @endif

    <!-- 1. Records of Total records, pending, failed, successful -->
    <div class="grid grid-cols-2 lg:grid-cols-4 gap-4">
        <!-- Total Service Records -->
        <div class="rounded-2xl bg-white dark:bg-slate-900 border border-slate-200/80 dark:border-slate-800 p-5 shadow-xs">
            <span class="text-xs font-medium text-slate-500 dark:text-slate-400">Total {{ $svcName }} Records</span>
            <div class="mt-2 text-2xl font-extrabold text-slate-900 dark:text-white">
                {{ $counts['all'] }}
            </div>
        </div>

        <!-- Pending -->
        <div class="rounded-2xl bg-white dark:bg-slate-900 border border-slate-200/80 dark:border-slate-800 p-5 shadow-xs">
            <span class="text-xs font-medium text-amber-600 dark:text-amber-400">Pending</span>
            <div class="mt-2 text-2xl font-extrabold text-amber-600 dark:text-amber-400">
                {{ $counts['pending'] }}
            </div>
        </div>

        <!-- Failed -->
        <div class="rounded-2xl bg-white dark:bg-slate-900 border border-slate-200/80 dark:border-slate-800 p-5 shadow-xs">
            <span class="text-xs font-medium text-red-600 dark:text-red-400">Failed</span>
            <div class="mt-2 text-2xl font-extrabold text-red-600 dark:text-red-400">
                {{ $counts['failed'] }}
            </div>
        </div>

        <!-- Successful -->
        <div class="rounded-2xl bg-white dark:bg-slate-900 border border-slate-200/80 dark:border-slate-800 p-5 shadow-xs">
            <span class="text-xs font-medium text-emerald-600 dark:text-emerald-400">Successful</span>
            <div class="mt-2 text-2xl font-extrabold text-emerald-600 dark:text-emerald-400">
                {{ $counts['completed'] }}
            </div>
        </div>
    </div>

    <!-- 2. Table with specific <th>: Ref, Date and Time, Old Tracking ID, Reply, Refund -->
    <div class="rounded-2xl bg-white dark:bg-slate-900 border border-slate-200/80 dark:border-slate-800 shadow-xs overflow-hidden">
        <div class="overflow-x-auto">
            <table class="w-full text-left text-xs text-slate-600 dark:text-slate-300">
                <thead class="bg-slate-50 dark:bg-slate-950/60 text-slate-500 dark:text-slate-400 uppercase tracking-wider text-[11px] border-b border-slate-200 dark:border-slate-800">
                    <tr>
                        <th class="py-3.5 px-4 font-semibold">Ref</th>
                        <th class="py-3.5 px-4 font-semibold whitespace-nowrap">Date and Time</th>
                        <th class="py-3.5 px-4 font-semibold">Email</th>
                        <th class="py-3.5 px-4 font-semibold whitespace-nowrap">{{ $inputColumnHeader }}</th>
                        <th class="py-3.5 px-4 font-semibold text-center">Action</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-100 dark:divide-slate-800/80 font-medium">
                    @forelse($requests as $req)
                        <tr class="hover:bg-slate-50/80 dark:hover:bg-slate-800/40 transition-colors">
                            <!-- Ref -->
                            <td class="py-3 px-4 font-mono font-semibold text-slate-800 dark:text-slate-200 whitespace-nowrap">
                                {{ $req->reference }}
                            </td>

                            <!-- Date and Time -->
                            <td class="py-3 px-4 text-slate-500 dark:text-slate-400 whitespace-nowrap">
                                {{ $req->created_at->format('M d, Y h:i A') }}
                            </td>

                            <!-- Email -->
                            <td class="py-3 px-4 text-slate-700 dark:text-slate-300 whitespace-nowrap">
                                {{ $req->user?->email ?? '—' }}
                            </td>

                            <!-- Old Tracking ID / Input -->
                            <td class="py-3 px-4 font-mono text-slate-700 dark:text-slate-300">
                                {{ $req->tracking_input }}
                            </td>

                            <!-- Action -->
                            <td class="py-3 px-4 text-center whitespace-nowrap">
                                <a href="{{ route('admin.queue.show', $req->id) }}"
                                   class="inline-flex items-center gap-1.5 px-3 py-1.5 rounded-lg text-xs font-semibold text-indigo-600 dark:text-indigo-400 bg-indigo-50 dark:bg-indigo-500/10 hover:bg-indigo-100 dark:hover:bg-indigo-500/20 border border-indigo-200 dark:border-indigo-500/30 transition shadow-xs cursor-pointer">
                                    <svg class="w-3.5 h-3.5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z" />
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z" />
                                    </svg>
                                    <span>View</span>
                                </a>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="5" class="py-10 text-center text-slate-400 dark:text-slate-500">
                                No {{ $svcName }} records found.
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        @if($requests->hasPages())
            <div class="p-4 border-t border-slate-100 dark:border-slate-800">
                {{ $requests->links() }}
            </div>
        @endif
    </div>
</div>
@endsection
