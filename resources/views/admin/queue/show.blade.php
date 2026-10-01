@extends('layouts.admin')

@section('content')
<div class="space-y-6 max-w-6xl mx-auto">
    <!-- Top Bar Navigation -->
    <div class="flex items-center justify-between">
        <a href="{{ route('admin.queue.index') }}"
           class="inline-flex items-center gap-1.5 text-xs font-semibold text-slate-600 dark:text-slate-400 hover:text-indigo-600 dark:hover:text-indigo-400 transition-colors">
            <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18" />
            </svg>
            <span>Back to Submitted Request</span>
        </a>

        <div class="flex items-center gap-2 text-xs text-slate-500">
            <span>Reference:</span>
            <span class="font-bold text-slate-900 dark:text-white bg-slate-100 dark:bg-slate-800 px-2 py-0.5 rounded border border-slate-200 dark:border-slate-700">
                #{{ $serviceRequest->reference }}
            </span>
        </div>
    </div>

    <!-- Main Two-Column Layout -->
    <div class="grid grid-cols-1 lg:grid-cols-3 gap-6 items-start">
        <!-- Left Column: Details (2 Cols) -->
        <div class="lg:col-span-2 space-y-6">
            <!-- 1. Service & Tracking Card -->
            <div class="rounded-2xl bg-white dark:bg-slate-900 border border-slate-200/80 dark:border-slate-800 p-6 shadow-sm">
                <div class="flex items-center justify-between border-b border-slate-100 dark:border-slate-800 pb-4">
                    <div>
                        <span class="text-xs font-semibold text-indigo-600 dark:text-indigo-400 uppercase tracking-wider">
                            {{ $serviceRequest->service->category ? $serviceRequest->service->category->name : 'Manual Clearinghouse Service' }}
                        </span>
                        <h2 class="text-xl font-bold text-slate-900 dark:text-white mt-0.5">
                            {{ $serviceRequest->service->name }}
                        </h2>
                    </div>
                    <div class="text-right">
                        <span class="text-[10px] text-slate-400 uppercase tracking-wider block">Charged Fee</span>
                        <span class="text-lg font-extrabold text-emerald-600 dark:text-emerald-400">
                            {{ $serviceRequest->formatted_amount }}
                        </span>
                    </div>
                </div>

                <!-- Primary Tracking Value Display -->
                <div class="mt-4 p-4 rounded-xl bg-slate-50 dark:bg-slate-800/60 border border-slate-200 dark:border-slate-700">
                    <span class="text-[11px] font-semibold text-slate-500 dark:text-slate-400 uppercase tracking-wider block">
                        Primary Search Identifier
                    </span>
                    <div class="mt-1 text-base font-bold text-slate-900 dark:text-white select-all">
                        {{ $serviceRequest->tracking_input }}
                    </div>
                </div>

                <!-- Multi-field Custom Payload (if any) -->
                @if(is_array($serviceRequest->input_payload) && count($serviceRequest->input_payload) > 1)
                    <div class="mt-4 pt-4 border-t border-slate-100 dark:border-slate-800">
                        <h3 class="text-xs font-bold text-slate-800 dark:text-slate-200 uppercase tracking-wider mb-2">
                            Submitted Form Payload
                        </h3>
                        <div class="grid grid-cols-1 sm:grid-cols-2 gap-3 text-xs">
                            @foreach($serviceRequest->input_payload as $key => $val)
                                <div class="p-3 rounded-lg bg-slate-50 dark:bg-slate-800/40 border border-slate-200/80 dark:border-slate-800">
                                    <span class="text-slate-400 uppercase text-[10px] block">{{ str_replace('_', ' ', $key) }}</span>
                                    <span class="font-medium text-slate-900 dark:text-white select-all">{{ is_array($val) ? json_encode($val) : $val }}</span>
                                </div>
                            @endforeach
                        </div>
                    </div>
                @endif

                <!-- Bulk Batch Origin (if applicable) -->
                @if($serviceRequest->batch)
                    <div class="mt-4 pt-4 border-t border-slate-100 dark:border-slate-800 flex items-center justify-between text-xs text-slate-500">
                        <span>Submitted via Bulk Batch: <strong class="text-slate-800 dark:text-slate-200">#{{ $serviceRequest->batch->batch_reference }}</strong></span>
                        <span>Batch Size: {{ $serviceRequest->batch->accepted_count }} accepted of {{ $serviceRequest->batch->total_submitted }}</span>
                    </div>
                @endif
            </div>

            <!-- 2. Customer Profile Card -->
            <div class="rounded-2xl bg-white dark:bg-slate-900 border border-slate-200/80 dark:border-slate-800 p-6 shadow-sm">
                <h3 class="text-sm font-bold text-slate-900 dark:text-white uppercase tracking-wider mb-4 flex items-center gap-2">
                    <svg class="w-4 h-4 text-indigo-600 dark:text-indigo-400" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z" />
                    </svg>
                    <span>Submitting Customer Profile</span>
                </h3>

                <div class="grid grid-cols-1 sm:grid-cols-2 gap-4 text-xs">
                    <div>
                        <span class="text-slate-400">Full Legal Name</span>
                        <div class="font-semibold text-slate-900 dark:text-white mt-0.5">{{ $serviceRequest->user->full_name }}</div>
                    </div>
                    <div>
                        <span class="text-slate-400">Email Address</span>
                        <div class="font-semibold text-slate-900 dark:text-white mt-0.5">{{ $serviceRequest->user->email }}</div>
                    </div>
                    <div>
                        <span class="text-slate-400">Phone Number</span>
                        <div class="font-semibold text-slate-900 dark:text-white mt-0.5">{{ $serviceRequest->user->phone_number }}</div>
                    </div>
                    <div>
                        <span class="text-slate-400">Business / Enterprise</span>
                        <div class="font-semibold text-slate-900 dark:text-white mt-0.5">{{ $serviceRequest->user->business }}</div>
                    </div>
                </div>
            </div>

            <!-- 3. Audit History & Resolution Card -->
            <div class="rounded-2xl bg-white dark:bg-slate-900 border border-slate-200/80 dark:border-slate-800 p-6 shadow-sm">
                <h3 class="text-sm font-bold text-slate-900 dark:text-white uppercase tracking-wider mb-4 flex items-center gap-2">
                    <svg class="w-4 h-4 text-emerald-600 dark:text-emerald-400" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z" />
                    </svg>
                    <span>Lifecycle & Audit Trail</span>
                </h3>

                <div class="space-y-3 text-xs">
                    <div class="flex items-center justify-between py-2 border-b border-slate-100 dark:border-slate-800">
                        <span class="text-slate-500">Submitted Timestamp:</span>
                        <span class="text-slate-900 dark:text-white font-medium">
                            {{ $serviceRequest->created_at->format('M d, Y H:i:s') }}
                        </span>
                    </div>



                    @if($serviceRequest->processed_by)
                        <div class="flex items-center justify-between py-2 border-b border-slate-100 dark:border-slate-800">
                            <span class="text-slate-500">Resolved By:</span>
                            <span class="font-medium text-slate-900 dark:text-white">
                                {{ $serviceRequest->processedBy ? $serviceRequest->processedBy->full_name : 'Staff' }}
                            </span>
                        </div>
                    @endif

                    @if($serviceRequest->completed_at)
                        <div class="flex items-center justify-between py-2 border-b border-slate-100 dark:border-slate-800">
                            <span class="text-slate-500">Completed Timestamp:</span>
                            <span class="text-emerald-600 dark:text-emerald-400 font-semibold">
                                {{ $serviceRequest->completed_at->format('M d, Y H:i:s') }}
                            </span>
                        </div>
                    @endif

                    @if($serviceRequest->rejection_reason)
                        <div class="p-3 rounded-xl bg-red-50 dark:bg-red-500/10 border border-red-200 dark:border-red-500/20 text-red-800 dark:text-red-300">
                            <strong class="block font-semibold mb-0.5">Rejection Reason:</strong>
                            <p>{{ $serviceRequest->rejection_reason }}</p>
                        </div>
                    @endif

                    @if($serviceRequest->admin_notes)
                        <div class="p-3 rounded-xl bg-slate-50 dark:bg-slate-800/50 border border-slate-200 dark:border-slate-700 text-slate-700 dark:text-slate-300">
                            <strong class="block font-semibold mb-0.5 text-slate-900 dark:text-white">Administrative Notes:</strong>
                            <p>{{ $serviceRequest->admin_notes }}</p>
                        </div>
                    @endif

                    @if($serviceRequest->result_payload)
                        <div class="p-3 rounded-xl bg-emerald-50 dark:bg-emerald-500/10 border border-emerald-200 dark:border-emerald-500/20 text-emerald-800 dark:text-emerald-300">
                            <strong class="block font-semibold mb-0.5">Resolution Payload:</strong>
                            <pre class="text-[11px] whitespace-pre-wrap">{{ json_encode($serviceRequest->result_payload, JSON_PRETTY_PRINT) }}</pre>
                        </div>
                    @endif

                    @if($serviceRequest->isRefunded())
                        <div class="p-3 rounded-xl bg-amber-50 dark:bg-amber-500/10 border border-amber-200 dark:border-amber-500/20 text-amber-800 dark:text-amber-300">
                            <div class="flex items-center justify-between">
                                <strong>Refund Issued:</strong>
                                <span class="">{{ $serviceRequest->refunded_at->format('M d, Y H:i:s') }}</span>
                            </div>
                            <p class="mt-1 text-[11px]">
                                Authorized by: {{ $serviceRequest->refundedBy ? $serviceRequest->refundedBy->full_name : 'Staff/Admin' }}
                            </p>
                        </div>
                    @endif
                </div>
            </div>
        </div>

        <!-- Right Column: Interactive Actions & Status Controls (1 Col) -->
        <div class="space-y-6">
            <!-- Current Status Card -->
            <div class="rounded-2xl bg-white dark:bg-slate-900 border border-slate-200/80 dark:border-slate-800 p-6 shadow-sm">
                <span class="text-xs font-semibold text-slate-400 uppercase tracking-wider block">
                    Current Queue Status
                </span>
                <div class="mt-2">
                    @if($serviceRequest->isPending())
                        <span class="inline-flex items-center gap-1.5 px-3 py-1 rounded-full text-xs font-bold bg-amber-50 dark:bg-amber-500/10 text-amber-700 dark:text-amber-400 border border-amber-200 dark:border-amber-500/20">
                            <span class="w-2 h-2 rounded-full bg-amber-500 animate-pulse"></span>
                            <span>Pending</span>
                        </span>
                    @elseif($serviceRequest->isProcessing())
                        <span class="inline-flex items-center gap-1.5 px-3 py-1 rounded-full text-xs font-bold bg-indigo-50 dark:bg-indigo-500/10 text-indigo-700 dark:text-indigo-400 border border-indigo-200 dark:border-indigo-500/20">
                            <span class="w-2 h-2 rounded-full bg-indigo-500 animate-pulse"></span>
                            <span>In Processing</span>
                        </span>
                    @elseif($serviceRequest->isCompleted())
                        <span class="inline-flex items-center gap-1.5 px-3 py-1 rounded-full text-xs font-bold bg-emerald-50 dark:bg-emerald-500/10 text-emerald-700 dark:text-emerald-400 border border-emerald-200 dark:border-emerald-500/20">
                            <span>Completed</span>
                        </span>
                    @elseif($serviceRequest->isFailed())
                        <span class="inline-flex items-center gap-1.5 px-3 py-1 rounded-full text-xs font-bold bg-red-50 dark:bg-red-500/10 text-red-700 dark:text-red-400 border border-red-200 dark:border-red-500/20">
                            <span>Failed</span>
                        </span>
                    @endif
                </div>

                <!-- Action Controls Based on State -->
                <div class="mt-6 pt-6 border-t border-slate-100 dark:border-slate-800 space-y-4">
                    @if($serviceRequest->isPending())
                        <div>
                            <form method="POST" action="{{ route('admin.queue.pick', $serviceRequest->id) }}">
                                @csrf
                                <button type="submit"
                                        class="w-full py-2.5 px-4 rounded-xl bg-indigo-600 hover:bg-indigo-500 text-white text-xs font-semibold shadow-xs transition cursor-pointer flex items-center justify-center gap-1.5">
                                    <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M14.752 11.168l-3.197-2.132A1 1 0 0010 9.87v4.263a1 1 0 001.555.832l3.197-2.132a1 1 0 000-1.664z" />
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
                                    </svg>
                                    <span>Move to In Processing</span>
                                </button>
                            </form>
                        </div>
                    @endif

                    <!-- Direct Complete or Fail Options for Pending & Processing Requests -->
                    @if(in_array($serviceRequest->status, ['pending', 'processing']))
                        <!-- Mark as Completed Form -->
                        <div class="p-4 rounded-xl bg-emerald-50/50 dark:bg-emerald-950/20 border border-emerald-200 dark:border-emerald-500/30">
                            <h4 class="text-xs font-bold text-emerald-800 dark:text-emerald-300 uppercase tracking-wider mb-2">
                                Complete Request
                            </h4>
                            <form method="POST" action="{{ route('admin.queue.complete', $serviceRequest->id) }}" class="space-y-3">
                                @csrf
                                <div>
                                    <label class="block text-[11px] font-medium text-slate-700 dark:text-slate-300 mb-1">Resolution Reference / Slip #</label>
                                    <input type="text" name="resolution_reference" placeholder="e.g. CLR-984128"
                                           class="w-full px-3 py-1.5 text-xs rounded-lg bg-white dark:bg-slate-900 border border-slate-300 dark:border-slate-700 text-slate-900 dark:text-white">
                                </div>
                                <div>
                                    <label class="block text-[11px] font-medium text-slate-700 dark:text-slate-300 mb-1">Admin Notes (Optional)</label>
                                    <textarea name="admin_notes" rows="2" placeholder="Clearance verified with immigration registry..."
                                              class="w-full px-3 py-1.5 text-xs rounded-lg bg-white dark:bg-slate-900 border border-slate-300 dark:border-slate-700 text-slate-900 dark:text-white"></textarea>
                                </div>
                                <button type="submit"
                                        class="w-full py-2 px-3 rounded-lg bg-emerald-600 hover:bg-emerald-500 text-white text-xs font-bold transition cursor-pointer">
                                    Mark as Completed
                                </button>
                            </form>
                        </div>

                        <!-- Mark as Failed Form -->
                        <div class="p-4 rounded-xl bg-red-50/50 dark:bg-red-950/20 border border-red-200 dark:border-red-500/30">
                            <h4 class="text-xs font-bold text-red-800 dark:text-red-300 uppercase tracking-wider mb-2">
                                Reject / Fail Request
                            </h4>
                            <form method="POST" action="{{ route('admin.queue.fail', $serviceRequest->id) }}" class="space-y-3">
                                @csrf
                                <div>
                                    <label class="block text-[11px] font-medium text-slate-700 dark:text-slate-300 mb-1">
                                        Rejection Reason <span class="text-red-500">*</span>
                                    </label>
                                    <textarea name="rejection_reason" rows="2" required minlength="5"
                                              placeholder="Reason for failure (e.g. Biometric record mismatch)..."
                                              class="w-full px-3 py-1.5 text-xs rounded-lg bg-white dark:bg-slate-900 border border-slate-300 dark:border-slate-700 text-slate-900 dark:text-white"></textarea>
                                </div>
                                <div>
                                    <label class="block text-[11px] font-medium text-slate-700 dark:text-slate-300 mb-1">Admin Notes (Optional)</label>
                                    <input type="text" name="admin_notes" placeholder="Internal remarks..."
                                           class="w-full px-3 py-1.5 text-xs rounded-lg bg-white dark:bg-slate-900 border border-slate-300 dark:border-slate-700 text-slate-900 dark:text-white">
                                </div>

                                <div class="text-[10px] text-amber-700 dark:text-amber-400 bg-amber-50 dark:bg-amber-500/10 p-2 rounded-lg border border-amber-200 dark:border-amber-500/20 leading-tight">
                                    ⚠️ <strong>Notice:</strong> Failing this job will NOT automatically refund the customer's wallet.
                                </div>

                                <button type="submit"
                                        onclick="return confirm('Are you sure you want to fail this request? Note that the customer will not be automatically refunded.')"
                                        class="w-full py-2 px-3 rounded-lg bg-red-600 hover:bg-red-500 text-white text-xs font-bold transition cursor-pointer">
                                    Mark as Failed
                                </button>
                            </form>
                        </div>
                    @endif

                    <!-- 3. If Failed: Manual Refund Button Option -->
                    @if($serviceRequest->isFailed())
                        <div class="p-4 rounded-xl bg-slate-50 dark:bg-slate-800/80 border border-slate-200 dark:border-slate-700">
                            <span class="text-xs font-bold text-slate-800 dark:text-slate-200 uppercase tracking-wider block mb-1">
                                Financial Refund Control
                            </span>

                            @if($serviceRequest->isRefunded())
                                <div class="text-xs text-emerald-600 dark:text-emerald-400 font-semibold flex items-center gap-1.5 mt-2">
                                    <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7" />
                                    </svg>
                                    <span>Refund of {{ $serviceRequest->formatted_amount }} already processed.</span>
                                </div>
                            @else
                                <p class="text-[11px] text-slate-500 dark:text-slate-400 leading-relaxed mb-3">
                                    Customer paid <strong class="text-slate-900 dark:text-white">{{ $serviceRequest->formatted_amount }}</strong> upon submission. You may credit this back to their wallet balance if warranted.
                                </p>

                                <form method="POST" action="{{ route('admin.queue.refund', $serviceRequest->id) }}">
                                    @csrf
                                    <button type="submit"
                                            onclick="return confirm('Credit {{ $serviceRequest->formatted_amount }} back to customer wallet? This action is atomic and creates an immutable audit ledger entry.')"
                                            class="w-full py-2.5 px-4 rounded-xl bg-amber-600 hover:bg-amber-500 text-white text-xs font-bold shadow-md shadow-amber-600/20 transition cursor-pointer flex items-center justify-center gap-2">
                                        <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8c-1.657 0-3 .895-3 2s1.343 2 3 2 3 .895 3 2-1.343 2-3 2m0-8c1.11 0 2.08.402 2.599 1M12 8V7m0 1v8m0 0v1m0-1c-1.11 0-2.08-.402-2.599-1M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
                                        </svg>
                                        <span>Issue Manual Refund ({{ $serviceRequest->formatted_amount }})</span>
                                    </button>
                                </form>
                            @endif
                        </div>
                    @endif
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
