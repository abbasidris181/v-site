@extends('layouts.app')

@section('title', $service->name)

@section('content')
<div class="space-y-8">
    <!-- Breadcrumb & Back Navigation -->
    <div class="flex items-center gap-2 text-xs text-slate-500 dark:text-slate-400">
        <a href="{{ route('dashboard') }}" class="hover:text-emerald-600 dark:hover:text-emerald-400 transition-colors">Dashboard</a>
        <span>/</span>
        <span class="text-slate-700 dark:text-slate-200">{{ $service->category->name }}</span>
        <span>/</span>
        <span class="text-emerald-600 dark:text-emerald-400 font-semibold">{{ $service->name }}</span>
    </div>

    <!-- Service Header Card -->
    <div class="rounded-2xl bg-gradient-to-r from-white via-slate-50 to-emerald-50/50 dark:from-slate-900 dark:via-slate-800 dark:to-emerald-950/40 p-6 sm:p-8 border border-slate-200 dark:border-slate-800 shadow-xl">
        <div class="flex items-center gap-3 sm:gap-3.5">
            <x-service-icon :service="$service" size="inline" />
            <h1 class="text-2xl sm:text-3xl font-extrabold text-slate-900 dark:text-white tracking-tight">
                {{ $service->name }}
            </h1>
        </div>
        @if(!empty($service->description))
            <p class="mt-2 text-sm text-slate-600 dark:text-slate-300 max-w-2xl leading-relaxed">
                {{ $service->description }}
            </p>
        @endif
    </div>

    <!-- Service Submission Form Container -->
    <div class="rounded-2xl bg-white dark:bg-slate-900 border border-slate-200 dark:border-slate-800 p-6 sm:p-8 shadow-xl">
        <div class="border-b border-slate-200 dark:border-slate-800 pb-4 mb-6">
            <p class="text-xs text-slate-500 dark:text-slate-400">Provide the required verification inputs below.</p>
        </div>

        <!-- In-Page Error Alert (SRS & Real-Time Feedback) -->
        @if(session('error'))
            <div id="submission-error" class="mb-6 rounded-2xl bg-rose-50 dark:bg-rose-950/40 border border-rose-200 dark:border-rose-800/80 p-4 sm:p-5 flex items-start gap-3.5 shadow-sm">
                <div class="w-8 h-8 rounded-xl bg-rose-100 dark:bg-rose-900/50 text-rose-600 dark:text-rose-400 flex items-center justify-center flex-shrink-0 mt-0.5">
                    <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4m0 4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
                    </svg>
                </div>
                <div class="flex-1 min-w-0">
                    <h4 class="text-sm font-bold text-rose-900 dark:text-rose-200">Verification Request Failed</h4>
                    <p class="text-xs text-rose-700 dark:text-rose-300 mt-1 leading-relaxed">{{ session('error') }}</p>
                </div>
            </div>
        @endif

        <!-- Real-Time Synchronous API Result Feedback (SRS Section 12, 15, 16) -->
        @if(session('api_result'))
            @php 
                $apiReq = session('api_result');
                $apiData = $apiReq->result_payload['data'] ?? [];
                $resolvedPhoto = !empty($apiData['photo_base64']) 
                    ? $apiData['photo_base64'] 
                    : (!empty($apiData['image']) 
                        ? (str_starts_with($apiData['image'], 'data:') || str_starts_with($apiData['image'], 'http') 
                            ? $apiData['image'] 
                            : 'data:image/jpeg;base64,' . ltrim($apiData['image'])) 
                        : null);
            @endphp
            <div id="verification-result" class="mb-8 rounded-2xl bg-gradient-to-br from-emerald-50/90 via-slate-50 to-white dark:from-emerald-950/30 dark:via-slate-900 dark:to-slate-900 border-2 border-emerald-500/40 p-6 sm:p-8 space-y-6 shadow-xl relative overflow-hidden">
                <!-- Background ambient glow -->
                <div class="absolute -top-12 -right-12 w-48 h-48 bg-emerald-500/10 rounded-full blur-2xl pointer-events-none"></div>

                <!-- Header / Profile Summary Card -->
                <div class="flex flex-col md:flex-row md:items-center justify-between gap-6 border-b border-emerald-200/80 dark:border-emerald-500/20 pb-6 relative z-10">
                    <div class="flex flex-col sm:flex-row sm:items-center gap-5">
                        <!-- Applicant Photo Frame -->
                        <div class="w-24 h-28 sm:w-28 sm:h-32 rounded-xl bg-slate-100 dark:bg-slate-800 border-2 border-emerald-600 dark:border-emerald-500 overflow-hidden shadow-md flex-shrink-0 flex items-center justify-center relative group">
                            @if(!empty($resolvedPhoto))
                                <img src="{{ $resolvedPhoto }}" alt="Applicant Photo" class="w-full h-full object-cover">
                            @else
                                <div class="w-full h-full flex flex-col items-center justify-center text-slate-400 dark:text-slate-500">
                                    <svg class="w-12 h-12" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z" />
                                    </svg>
                                </div>
                            @endif
                            <div class="absolute bottom-0 inset-x-0 bg-emerald-700/90 text-white text-[9px] font-bold uppercase tracking-wider text-center py-0.5">
                                Verified
                            </div>
                        </div>

                        <!-- Name & Key Identity Identifiers -->
                        <div class="space-y-1.5">
                            <div class="flex flex-wrap items-center gap-2">
                                <span class="inline-flex items-center gap-1 text-[11px] font-extrabold uppercase tracking-wider text-emerald-700 dark:text-emerald-400 bg-emerald-100/80 dark:bg-emerald-500/15 px-2.5 py-0.5 rounded-full">
                                    <svg class="w-3.5 h-3.5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M5 13l4 4L19 7" />
                                    </svg>
                                    <span>Verified Identity Record</span>
                                </span>
                            </div>

                            @php
                                $rawH3Name = $apiData['full_name'] ?? 'Verified Customer';
                                $cleanH3Name = trim(preg_replace('/\s*[\*]+\s*/', ' ', (string) $rawH3Name));
                                $cleanH3Name = preg_replace('/\s+/', ' ', $cleanH3Name);
                            @endphp
                            <h3 class="text-xl sm:text-2xl font-black text-slate-900 dark:text-white tracking-tight uppercase">
                                {{ !empty($cleanH3Name) ? $cleanH3Name : 'Verified Customer' }}
                            </h3>

                            <div class="flex flex-wrap items-center gap-4 text-xs pt-1">
                                <div class="flex items-center gap-1.5">
                                    <span class="text-slate-400 uppercase font-bold text-[10px]">NIN:</span>
                                    <span class="font-extrabold text-emerald-700 dark:text-emerald-400 font-mono text-sm tracking-wider">
                                        {{ $apiData['nin_formatted'] ?? $apiData['nin'] ?? $apiReq->tracking_input }}
                                    </span>
                                </div>
                                @php
                                    $rawPayload = $apiReq->result_payload['raw'] ?? [];
                                    $rawMsgTrackingId = $rawPayload['message']['trackingId'] 
                                        ?? $rawPayload['message']['tracking_id'] 
                                        ?? $rawPayload['message']['trackingID'] 
                                        ?? $rawPayload['message']['trackingid'] 
                                        ?? $rawPayload['trackingId'] 
                                        ?? $rawPayload['tracking_id'] 
                                        ?? null;

                                    $rawTracking = !empty($rawMsgTrackingId) 
                                        ? (string) $rawMsgTrackingId 
                                        : (!empty($apiData['tracking_id']) ? (string) $apiData['tracking_id'] : (!empty($apiData['trackingId']) ? (string) $apiData['trackingId'] : ''));

                                    $transIdVal = !empty($apiData['trans_id']) ? (string) $apiData['trans_id'] : (!empty($apiData['transID']) ? (string) $apiData['transID'] : '');

                                    $isSynthetic = false;
                                    if (!empty($rawTracking)) {
                                        $trackClean = strtoupper(str_replace(['-', '_'], '', $rawTracking));
                                        $refClean = strtoupper(str_replace(['-', '_'], '', $apiReq->reference));
                                        $transClean = strtoupper(str_replace(['-', '_'], '', $transIdVal));

                                        if (str_starts_with($trackClean, 'TRK')) {
                                            $trackSuffix = substr($trackClean, 3);
                                            if (!empty($trackSuffix) && (
                                                (!empty($transClean) && str_starts_with($transClean, $trackSuffix)) ||
                                                (!empty($transClean) && str_contains($transClean, $trackSuffix)) ||
                                                (!empty($refClean) && str_contains($refClean, $trackSuffix)) ||
                                                (!empty($rawPayload) && empty($rawMsgTrackingId)) ||
                                                $rawTracking === ('TRK-' . strtoupper(substr($transClean, 0, 10)))
                                            )) {
                                                $isSynthetic = true;
                                            }
                                        }
                                    }
                                    $providerTrackingId = ($isSynthetic || empty($rawTracking) || $rawTracking === '—') ? null : $rawTracking;
                                @endphp
                                @if(!empty($providerTrackingId))
                                    <div class="flex items-center gap-1.5 border-l border-slate-200 dark:border-slate-700 pl-4">
                                        <span class="text-slate-400 uppercase font-bold text-[10px]">Tracking ID:</span>
                                        <span class="font-bold text-slate-800 dark:text-slate-200 font-mono">
                                            {{ $providerTrackingId }}
                                        </span>
                                    </div>
                                @endif
                                @if(!empty($apiData['trans_id']))
                                    <div class="flex items-center gap-1.5 border-l border-slate-200 dark:border-slate-700 pl-4">
                                        <span class="text-slate-400 uppercase font-bold text-[10px]">Trans ID:</span>
                                        <span class="font-mono text-slate-600 dark:text-slate-400">
                                            {{ $apiData['trans_id'] }}
                                        </span>
                                    </div>
                                @endif
                            </div>
                        </div>
                    </div>

                    <!-- Slip Action Button -->
                    <div class="flex flex-col sm:flex-row md:flex-col items-start md:items-end gap-1.5 shrink-0">
                        <a href="{{ route('services.slip', $apiReq->reference) }}"
                           class="inline-flex items-center gap-2 px-5 py-3 rounded-xl bg-emerald-600 hover:bg-emerald-500 text-white text-xs font-bold shadow-md shadow-emerald-600/20 transition cursor-pointer">
                            <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 17h2a2 2 0 002-2v-4a2 2 0 00-2-2H5a2 2 0 00-2 2v4a2 2 0 002 2h2m2 4h6a2 2 0 002-2v-4a2 2 0 00-2-2H9a2 2 0 00-2 2v4a2 2 0 002 2zm8-12V5a2 2 0 00-2-2H9a2 2 0 00-2 2v4h10z" />
                            </svg>
                            <span>View Details & Download Slips</span>
                            <span>&rarr;</span>
                        </a>
                        <span class="text-[10px] font-semibold text-emerald-700 dark:text-emerald-400">
                            Standard, Premium & Compact slips available
                        </span>
                    </div>
                </div>

                <!-- Comprehensive Identity Breakdown Grid -->
                <div class="space-y-4 relative z-10">
                    <!-- Section 1: Core Personal & Contact Particulars -->
                    <div>
                        <h4 class="text-xs font-extrabold uppercase tracking-wider text-slate-500 dark:text-slate-400 mb-2.5 flex items-center gap-2">
                            <span class="w-1.5 h-1.5 rounded-full bg-emerald-500"></span>
                            <span>Personal & Contact Particulars</span>
                        </h4>
                        <div class="grid grid-cols-2 sm:grid-cols-3 lg:grid-cols-4 gap-3 text-xs">
                            <div class="p-3 rounded-xl bg-white dark:bg-slate-800/80 border border-slate-200 dark:border-slate-700 shadow-xs">
                                <span class="text-[10px] uppercase font-bold text-slate-400 block mb-0.5">Surname</span>
                                <span class="font-extrabold text-slate-900 dark:text-white uppercase">{{ $apiData['surname'] ?? '—' }}</span>
                            </div>

                            <div class="p-3 rounded-xl bg-white dark:bg-slate-800/80 border border-slate-200 dark:border-slate-700 shadow-xs">
                                <span class="text-[10px] uppercase font-bold text-slate-400 block mb-0.5">First Name</span>
                                <span class="font-extrabold text-slate-900 dark:text-white uppercase">{{ $apiData['first_name'] ?? '—' }}</span>
                            </div>

                            <div class="p-3 rounded-xl bg-white dark:bg-slate-800/80 border border-slate-200 dark:border-slate-700 shadow-xs">
                                <span class="text-[10px] uppercase font-bold text-slate-400 block mb-0.5">Middle Name</span>
                                @php
                                    $rawShowMid = $apiData['middle_name'] ?? ($apiData['middlename'] ?? null);
                                    $trimmedShowMid = is_string($rawShowMid) ? trim($rawShowMid) : '';
                                    $isMaskedShowMid = empty($trimmedShowMid)
                                        || $trimmedShowMid === '—'
                                        || preg_match('/^[\*\s—\-]+$/', $trimmedShowMid)
                                        || in_array(strtolower($trimmedShowMid), ['null', 'nil', 'none', 'n/a', 'na'], true);
                                    $displayShowMid = ! $isMaskedShowMid ? $trimmedShowMid : '';
                                @endphp
                                <span class="font-extrabold text-slate-900 dark:text-white uppercase">{{ $displayShowMid }}</span>
                            </div>

                            <div class="p-3 rounded-xl bg-white dark:bg-slate-800/80 border border-slate-200 dark:border-slate-700 shadow-xs">
                                <span class="text-[10px] uppercase font-bold text-slate-400 block mb-0.5">Date of Birth</span>
                                <span class="font-bold text-slate-900 dark:text-white">{{ $apiData['date_of_birth'] ?? '—' }}</span>
                            </div>

                            <div class="p-3 rounded-xl bg-white dark:bg-slate-800/80 border border-slate-200 dark:border-slate-700 shadow-xs">
                                <span class="text-[10px] uppercase font-bold text-slate-400 block mb-0.5">Gender</span>
                                <span class="font-bold text-slate-900 dark:text-white uppercase">{{ $apiData['gender'] ?? '—' }}</span>
                            </div>

                            <div class="p-3 rounded-xl bg-white dark:bg-slate-800/80 border border-slate-200 dark:border-slate-700 shadow-xs">
                                <span class="text-[10px] uppercase font-bold text-slate-400 block mb-0.5">Telephone Number</span>
                                <span class="font-bold text-slate-900 dark:text-white font-mono">{{ $apiData['phone_number'] ?: '—' }}</span>
                            </div>

                            <div class="p-3 rounded-xl bg-white dark:bg-slate-800/80 border border-slate-200 dark:border-slate-700 shadow-xs">
                                <span class="text-[10px] uppercase font-bold text-slate-400 block mb-0.5">Marital Status</span>
                                <span class="font-bold text-slate-900 dark:text-white">{{ $apiData['marital_status'] ?: '—' }}</span>
                            </div>

                            <div class="p-3 rounded-xl bg-white dark:bg-slate-800/80 border border-slate-200 dark:border-slate-700 shadow-xs">
                                <span class="text-[10px] uppercase font-bold text-slate-400 block mb-0.5">Profession / Job</span>
                                <span class="font-bold text-slate-900 dark:text-white">{{ $apiData['profession'] ?: '—' }}</span>
                            </div>
                        </div>
                    </div>

                    <!-- Section 2: Origin & Place of Birth Particulars -->
                    <div>
                        <h4 class="text-xs font-extrabold uppercase tracking-wider text-slate-500 dark:text-slate-400 mb-2.5 flex items-center gap-2">
                            <span class="w-1.5 h-1.5 rounded-full bg-emerald-500"></span>
                            <span>Origin & Place of Birth</span>
                        </h4>
                        <div class="grid grid-cols-2 sm:grid-cols-3 lg:grid-cols-4 gap-3 text-xs">
                            <div class="p-3 rounded-xl bg-white dark:bg-slate-800/80 border border-slate-200 dark:border-slate-700 shadow-xs">
                                <span class="text-[10px] uppercase font-bold text-slate-400 block mb-0.5">State of Origin</span>
                                <span class="font-bold text-slate-900 dark:text-white">{{ $apiData['state_of_origin'] ?: '—' }}</span>
                            </div>

                            <div class="p-3 rounded-xl bg-white dark:bg-slate-800/80 border border-slate-200 dark:border-slate-700 shadow-xs">
                                <span class="text-[10px] uppercase font-bold text-slate-400 block mb-0.5">LGA of Origin</span>
                                <span class="font-bold text-slate-900 dark:text-white">{{ $apiData['lga_of_origin'] ?: '—' }}</span>
                            </div>

                            <div class="p-3 rounded-xl bg-white dark:bg-slate-800/80 border border-slate-200 dark:border-slate-700 shadow-xs">
                                <span class="text-[10px] uppercase font-bold text-slate-400 block mb-0.5">Place of Origin</span>
                                <span class="font-bold text-slate-900 dark:text-white">{{ $apiData['place_of_origin'] ?: '—' }}</span>
                            </div>

                            <div class="p-3 rounded-xl bg-white dark:bg-slate-800/80 border border-slate-200 dark:border-slate-700 shadow-xs">
                                <span class="text-[10px] uppercase font-bold text-slate-400 block mb-0.5">Country</span>
                                <span class="font-bold text-slate-900 dark:text-white">{{ $apiData['country'] ?: 'NG' }}</span>
                            </div>

                            <div class="p-3 rounded-xl bg-white dark:bg-slate-800/80 border border-slate-200 dark:border-slate-700 shadow-xs">
                                <span class="text-[10px] uppercase font-bold text-slate-400 block mb-0.5">Birth Country</span>
                                <span class="font-bold text-slate-900 dark:text-white">{{ $apiData['birth_country'] ?: 'Nigeria' }}</span>
                            </div>

                            <div class="p-3 rounded-xl bg-white dark:bg-slate-800/80 border border-slate-200 dark:border-slate-700 shadow-xs">
                                <span class="text-[10px] uppercase font-bold text-slate-400 block mb-0.5">Birth State / LGA</span>
                                <span class="font-bold text-slate-900 dark:text-white">
                                    {{ (!empty($apiData['birth_lga']) || !empty($apiData['birth_state'])) ? trim(($apiData['birth_lga'] ?? '') . ', ' . ($apiData['birth_state'] ?? ''), ', ') : '—' }}
                                </span>
                            </div>

                            <div class="p-3 rounded-xl bg-white dark:bg-slate-800/80 border border-slate-200 dark:border-slate-700 shadow-xs">
                                <span class="text-[10px] uppercase font-bold text-slate-400 block mb-0.5">Religion</span>
                                <span class="font-bold text-slate-900 dark:text-white">{{ $apiData['religion'] ?: '—' }}</span>
                            </div>

                            <div class="p-3 rounded-xl bg-white dark:bg-slate-800/80 border border-slate-200 dark:border-slate-700 shadow-xs">
                                <span class="text-[10px] uppercase font-bold text-slate-400 block mb-0.5">Height</span>
                                <span class="font-bold text-slate-900 dark:text-white">{{ $apiData['height'] ?: '—' }}</span>
                            </div>
                        </div>
                    </div>

                    <!-- Section 3: Residential Particulars -->
                    <div>
                        <h4 class="text-xs font-extrabold uppercase tracking-wider text-slate-500 dark:text-slate-400 mb-2.5 flex items-center gap-2">
                            <span class="w-1.5 h-1.5 rounded-full bg-emerald-500"></span>
                            <span>Residential Address Details</span>
                        </h4>
                        <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-3 text-xs">
                            <div class="sm:col-span-2 p-3 rounded-xl bg-white dark:bg-slate-800/80 border border-slate-200 dark:border-slate-700 shadow-xs">
                                <span class="text-[10px] uppercase font-bold text-slate-400 block mb-0.5">Residential Address</span>
                                <span class="font-bold text-slate-900 dark:text-white">{{ $apiData['residence_address'] ?: ($apiData['residence_address_line1'] ?: '—') }}</span>
                            </div>

                            <div class="p-3 rounded-xl bg-white dark:bg-slate-800/80 border border-slate-200 dark:border-slate-700 shadow-xs">
                                <span class="text-[10px] uppercase font-bold text-slate-400 block mb-0.5">Town / City</span>
                                <span class="font-bold text-slate-900 dark:text-white">{{ $apiData['residence_town'] ?: '—' }}</span>
                            </div>

                            <div class="p-3 rounded-xl bg-white dark:bg-slate-800/80 border border-slate-200 dark:border-slate-700 shadow-xs">
                                <span class="text-[10px] uppercase font-bold text-slate-400 block mb-0.5">Residential State / LGA</span>
                                <span class="font-bold text-slate-900 dark:text-white">
                                    {{ (!empty($apiData['residence_lga']) || !empty($apiData['residence_state'])) ? trim(($apiData['residence_lga'] ?? '') . ', ' . ($apiData['residence_state'] ?? ''), ', ') : '—' }}
                                </span>
                            </div>
                        </div>
                    </div>

                    <!-- Section 4: Next of Kin (if present) -->
                    @if(!empty($apiData['nok_name']) || !empty($apiData['nok_firstname']) || !empty($apiData['nok_address']))
                        <div>
                            <h4 class="text-xs font-extrabold uppercase tracking-wider text-slate-500 dark:text-slate-400 mb-2.5 flex items-center gap-2">
                                <span class="w-1.5 h-1.5 rounded-full bg-emerald-500"></span>
                                <span>Next of Kin Information</span>
                            </h4>
                            <div class="grid grid-cols-1 sm:grid-cols-3 gap-3 text-xs">
                                <div class="p-3 rounded-xl bg-white dark:bg-slate-800/80 border border-slate-200 dark:border-slate-700 shadow-xs">
                                    <span class="text-[10px] uppercase font-bold text-slate-400 block mb-0.5">Next of Kin Name</span>
                                    <span class="font-bold text-slate-900 dark:text-white uppercase">{{ $apiData['nok_name'] ?: '—' }}</span>
                                </div>

                                <div class="sm:col-span-2 p-3 rounded-xl bg-white dark:bg-slate-800/80 border border-slate-200 dark:border-slate-700 shadow-xs">
                                    <span class="text-[10px] uppercase font-bold text-slate-400 block mb-0.5">Next of Kin Address</span>
                                    <span class="font-bold text-slate-900 dark:text-white">{{ $apiData['nok_address'] ?: '—' }}</span>
                                </div>
                            </div>
                        </div>
                    @endif
                </div>
            </div>
        @endif

        <!-- Bulk Result Summary Feedback (SRS Section 14) -->
        @if(session('bulk_result'))
            @php $res = session('bulk_result'); @endphp
            <div class="mb-6 rounded-xl bg-slate-50 dark:bg-slate-950 border border-slate-200 dark:border-slate-800 p-5 space-y-4">
                <div class="flex items-center justify-between border-b border-slate-200 dark:border-slate-800/80 pb-3">
                    <h4 class="text-sm font-bold text-slate-900 dark:text-white">Batch Breakdown (SRS Partial Batch Rule)</h4>
                    <span class="text-xs text-slate-500 dark:text-slate-400">Submitted: {{ $res['total_submitted'] }}</span>
                </div>

                <div class="grid grid-cols-1 sm:grid-cols-3 gap-3">
                    <div class="p-3.5 rounded-xl bg-emerald-500/10 border border-emerald-500/30">
                        <span class="text-[10px] font-bold uppercase tracking-wider text-emerald-700 dark:text-emerald-400">Accepted & Created</span>
                        <div class="text-xl font-extrabold text-emerald-700 dark:text-emerald-400 mt-0.5">{{ count($res['accepted_entries']) }} entries</div>
                        <span class="text-[11px] text-slate-600 dark:text-slate-400">Charged: ₦{{ number_format($res['total_charged'], 2) }}</span>
                    </div>

                    <div class="p-3.5 rounded-xl bg-amber-500/10 border border-amber-500/30">
                        <span class="text-[10px] font-bold uppercase tracking-wider text-amber-700 dark:text-amber-400">Unfunded (Wallet Limit)</span>
                        <div class="text-xl font-extrabold text-amber-700 dark:text-amber-400 mt-0.5">{{ count($res['insufficient_balance_entries']) }} entries</div>
                        <span class="text-[11px] text-slate-600 dark:text-slate-400">Exceeded balance</span>
                    </div>

                    <div class="p-3.5 rounded-xl bg-red-500/10 border border-red-500/30">
                        <span class="text-[10px] font-bold uppercase tracking-wider text-red-700 dark:text-red-400">Invalid Format</span>
                        <div class="text-xl font-extrabold text-red-700 dark:text-red-400 mt-0.5">{{ count($res['invalid_entries']) }} entries</div>
                        <span class="text-[11px] text-slate-600 dark:text-slate-400">Validation failed</span>
                    </div>
                </div>

                @if(!empty($res['insufficient_balance_entries']))
                    <div class="text-xs text-amber-800 dark:text-amber-300 bg-amber-50 dark:bg-amber-500/5 p-3 rounded-lg border border-amber-200 dark:border-amber-500/20">
                        <strong>Unfunded entries (not charged):</strong>
                        <div class="mt-1 text-[11px] text-slate-700 dark:text-slate-300 break-words">
                            {{ implode(', ', $res['insufficient_balance_entries']) }}
                        </div>
                    </div>
                @endif
            </div>
        @endif

        <form id="service-submission-form" method="POST" action="{{ route('services.submit', $service->slug) }}" class="space-y-6">
            @csrf

            @if($service->fields_schema)
                <!-- Multi-field Dynamic Schema (e.g. BVN Retrieval) -->
                <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                    @foreach($service->fields_schema as $field)
                        <div>
                            <label for="{{ $field['name'] }}" class="block text-xs font-medium uppercase tracking-wider text-slate-700 dark:text-slate-300 mb-1.5">
                                {{ $field['label'] }} @if($field['required'] ?? false) <span class="text-emerald-600 dark:text-emerald-400">*</span> @endif
                            </label>
                            <input type="{{ $field['type'] ?? 'text' }}" id="{{ $field['name'] }}" name="{{ $field['name'] }}"
                                @if($field['required'] ?? false) required @endif
                                placeholder="{{ $field['placeholder'] ?? '' }}"
                                class="block w-full px-3.5 py-2.5 rounded-xl border border-slate-300 dark:border-slate-700 bg-white dark:bg-slate-800 text-slate-900 dark:text-white placeholder-slate-400 dark:placeholder-slate-500 focus:border-emerald-500 focus:ring-2 focus:ring-emerald-500/20 text-sm">
                        </div>
                    @endforeach
                </div>
            @elseif($service->is_bulk_allowed)
                <!-- Bulk / Multi-line Input Form (e.g. IPE Clearing, NIN Validation, Modification IPE) -->
                <div>
                    <div class="flex items-center justify-between mb-1.5">
                        <label for="tracking_input" class="block text-xs font-medium uppercase tracking-wider text-slate-700 dark:text-slate-300">
                            {{ $service->input_label }} <span class="text-emerald-600 dark:text-emerald-400">*</span>
                        </label>
                        @if($service->validation_rule === 'alphanumeric_15' || in_array($service->slug, ['ipe-clearing', 'modification-ipe']))
                            <span id="bulk_entry_counter" class="text-xs font-bold text-emerald-600 dark:text-emerald-400"></span>
                        @endif
                    </div>
                    <textarea id="tracking_input" name="tracking_input" rows="5" required
                        placeholder="{{ $service->input_placeholder }}"
                        class="block w-full px-4 py-3 rounded-xl border border-slate-300 dark:border-slate-700 bg-white dark:bg-slate-800 text-slate-900 dark:text-white placeholder-slate-400 dark:placeholder-slate-500 text-sm focus:border-emerald-500 focus:ring-2 focus:ring-emerald-500/20 leading-relaxed font-medium">{{ old('tracking_input') }}</textarea>
                    <p class="mt-1.5 text-xs text-slate-500 dark:text-slate-400">
                        @if($service->validation_rule === 'alphanumeric_15' || in_array($service->slug, ['ipe-clearing', 'modification-ipe']))
                            Each 15-character entry automatically occupies a new line (formatted even when pasted without spaces or commas).
                        @else
                            Paste one or multiple entries separated by new lines, commas, or spaces. Entries will be normalized, validated, and processed against your available balance.
                        @endif
                    </p>
                </div>
            @elseif(in_array($service->slug, ['nin-verification', 'nin-verification-2', 'nin-verification-3']))
                <!-- NIN Verification with Method Selector (Nin, Phone number, Demographics) -->
                <div class="space-y-4">
                    <div>
                        <!-- Input Label with Mode Selector -->
                        <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-2.5 mb-2">
                            <label for="nin_mode_selector" class="block text-xs font-semibold uppercase tracking-wider text-slate-700 dark:text-slate-300">
                                Verify Using <span class="text-emerald-600 dark:text-emerald-400">*</span>
                            </label>

                            <!-- Selector with options: Nin , Phone number , Demographics -->
                            <div class="w-full sm:w-64">
                                <select id="nin_mode_selector" name="verification_mode" onchange="switchNinMode(this.value)"
                                    class="w-full px-3.5 py-2 rounded-xl border border-slate-300 dark:border-slate-700 bg-white dark:bg-slate-800 text-slate-900 dark:text-white font-medium text-xs focus:border-emerald-500 focus:ring-2 focus:ring-emerald-500/20 cursor-pointer shadow-xs transition">
                                    <option value="nin" {{ old('verification_mode', 'nin') === 'nin' ? 'selected' : '' }}>Nin</option>
                                    <option value="phone" {{ old('verification_mode') === 'phone' ? 'selected' : '' }}>Phone number</option>
                                    <option value="demographics" {{ old('verification_mode') === 'demographics' ? 'selected' : '' }}>Demographics</option>
                                </select>
                            </div>
                        </div>

                        <!-- 1. Nin Input (11-digit integer) -->
                        <div id="nin_field_wrap" class="{{ old('verification_mode', 'nin') === 'nin' ? 'block' : 'hidden' }}">
                            <input type="text" id="nin_input_field" name="nin" maxlength="11" minlength="11" pattern="[0-9]{11}" inputmode="numeric"
                                oninput="this.value = this.value.replace(/[^0-9]/g, '').slice(0, 11);"
                                onpaste="setTimeout(() => { this.value = this.value.replace(/[^0-9]/g, '').slice(0, 11); }, 10);"
                                value="{{ old('nin', old('verification_mode', 'nin') === 'nin' ? old('tracking_input') : '') }}"
                                placeholder="Enter 11-digit NIN (e.g. 12345678901)"
                                class="block w-full px-4 py-3 rounded-xl border border-slate-300 dark:border-slate-700 bg-white dark:bg-slate-800 text-slate-900 dark:text-white placeholder-slate-400 dark:placeholder-slate-500 text-sm focus:border-emerald-500 focus:ring-2 focus:ring-emerald-500/20 font-mono tracking-wider">
                            <p class="mt-1.5 text-xs text-slate-500 dark:text-slate-400">
                                National Identity Number must be an 11-digit numeric integer.
                            </p>
                            @error('nin')
                                <p class="mt-1 text-xs text-rose-600 dark:text-rose-400">{{ $message }}</p>
                            @enderror
                        </div>

                        <!-- 2. Phone Number Input (11-digit or with NGN country code prefix) -->
                        <div id="phone_field_wrap" class="{{ old('verification_mode') === 'phone' ? 'block' : 'hidden' }}">
                            <input type="tel" id="phone_input_field" name="phone_number"
                                value="{{ old('phone_number', old('verification_mode') === 'phone' ? old('tracking_input') : '') }}"
                                placeholder="Enter phone number (e.g. 08012345678 or +2348012345678)"
                                class="block w-full px-4 py-3 rounded-xl border border-slate-300 dark:border-slate-700 bg-white dark:bg-slate-800 text-slate-900 dark:text-white placeholder-slate-400 dark:placeholder-slate-500 text-sm focus:border-emerald-500 focus:ring-2 focus:ring-emerald-500/20">
                            <p class="mt-1.5 text-xs text-slate-500 dark:text-slate-400">
                                Provide an 11-digit phone number or with Nigerian country code (+234 / 234).
                            </p>
                            @error('phone_number')
                                <p class="mt-1 text-xs text-rose-600 dark:text-rose-400">{{ $message }}</p>
                            @enderror
                        </div>

                        <!-- 3. Demographics Inputs (firstname, middlename, lastname, dob) -->
                        <div id="demographics_field_wrap" class="{{ old('verification_mode') === 'demographics' ? 'block' : 'hidden' }} space-y-3">
                            <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                                <div class="order-1 sm:order-1">
                                    <label for="first_name" class="block text-xs font-medium uppercase tracking-wider text-slate-700 dark:text-slate-300 mb-1.5">
                                        First Name <span class="text-emerald-600 dark:text-emerald-400">*</span>
                                    </label>
                                    <input type="text" id="first_name" name="first_name"
                                        value="{{ old('first_name') }}"
                                        placeholder="e.g. Amina"
                                        class="block w-full px-3.5 py-2.5 rounded-xl border border-slate-300 dark:border-slate-700 bg-white dark:bg-slate-800 text-slate-900 dark:text-white placeholder-slate-400 dark:placeholder-slate-500 text-sm focus:border-emerald-500 focus:ring-2 focus:ring-emerald-500/20">
                                    @error('first_name')
                                        <p class="mt-1 text-xs text-rose-600 dark:text-rose-400">{{ $message }}</p>
                                    @enderror
                                </div>

                                <div class="order-2 sm:order-2">
                                    <label for="middle_name" class="block text-xs font-medium uppercase tracking-wider text-slate-700 dark:text-slate-300 mb-1.5">
                                        Middle Name
                                    </label>
                                    <input type="text" id="middle_name" name="middle_name"
                                        value="{{ old('middle_name') }}"
                                        placeholder="e.g. Kalu"
                                        class="block w-full px-3.5 py-2.5 rounded-xl border border-slate-300 dark:border-slate-700 bg-white dark:bg-slate-800 text-slate-900 dark:text-white placeholder-slate-400 dark:placeholder-slate-500 text-sm focus:border-emerald-500 focus:ring-2 focus:ring-emerald-500/20">
                                    @error('middle_name')
                                        <p class="mt-1 text-xs text-rose-600 dark:text-rose-400">{{ $message }}</p>
                                    @enderror
                                </div>

                                <div class="order-4 sm:order-3">
                                    <label for="dob" class="block text-xs font-medium uppercase tracking-wider text-slate-700 dark:text-slate-300 mb-1.5">
                                        Date of Birth <span class="text-emerald-600 dark:text-emerald-400">*</span>
                                    </label>
                                    <input type="date" id="dob" name="dob"
                                        value="{{ old('dob') }}"
                                        class="block w-full px-3.5 py-2.5 rounded-xl border border-slate-300 dark:border-slate-700 bg-white dark:bg-slate-800 text-slate-900 dark:text-white placeholder-slate-400 dark:placeholder-slate-500 text-sm focus:border-emerald-500 focus:ring-2 focus:ring-emerald-500/20">
                                    @error('dob')
                                        <p class="mt-1 text-xs text-rose-600 dark:text-rose-400">{{ $message }}</p>
                                    @enderror
                                </div>

                                <!-- Gender (Immediately below Date of Birth) -->
                                <div class="order-5 sm:order-5">
                                    <label for="gender" class="block text-xs font-medium uppercase tracking-wider text-slate-700 dark:text-slate-300 mb-1.5">
                                        Gender
                                    </label>
                                    <select id="gender" name="gender"
                                        class="block w-full px-3.5 py-2.5 rounded-xl border border-slate-300 dark:border-slate-700 bg-white dark:bg-slate-800 text-slate-900 dark:text-white text-sm focus:border-emerald-500 focus:ring-2 focus:ring-emerald-500/20 cursor-pointer">
                                        <option value="">Select Gender</option>
                                        <option value="Male" {{ old('gender') === 'Male' ? 'selected' : '' }}>Male</option>
                                        <option value="Female" {{ old('gender') === 'Female' ? 'selected' : '' }}>Female</option>
                                    </select>
                                    @error('gender')
                                        <p class="mt-1 text-xs text-rose-600 dark:text-rose-400">{{ $message }}</p>
                                    @enderror
                                </div>

                                <div class="order-3 sm:order-4">
                                    <label for="last_name" class="block text-xs font-medium uppercase tracking-wider text-slate-700 dark:text-slate-300 mb-1.5">
                                        Lastname
                                    </label>
                                    <input type="text" id="last_name" name="last_name"
                                        value="{{ old('last_name', old('lastname')) }}"
                                        placeholder="e.g. Danjuma"
                                        class="block w-full px-3.5 py-2.5 rounded-xl border border-slate-300 dark:border-slate-700 bg-white dark:bg-slate-800 text-slate-900 dark:text-white placeholder-slate-400 dark:placeholder-slate-500 text-sm focus:border-emerald-500 focus:ring-2 focus:ring-emerald-500/20">
                                    @error('last_name')
                                        <p class="mt-1 text-xs text-rose-600 dark:text-rose-400">{{ $message }}</p>
                                    @enderror
                                    @error('lastname')
                                        <p class="mt-1 text-xs text-rose-600 dark:text-rose-400">{{ $message }}</p>
                                    @enderror
                                </div>
                            </div>
                            <p class="text-xs text-slate-500 dark:text-slate-400">
                                Provide the individual's legal first name, middle name, lastname, and registered date of birth.
                            </p>
                        </div>
                    </div>
                </div>
            @else
                <!-- Single Input Form (e.g. BVN, Personalization, Delinking) -->
                <div>
                    <label for="tracking_input" class="block text-xs font-medium uppercase tracking-wider text-slate-700 dark:text-slate-300 mb-1.5">
                        {{ $service->input_label }} <span class="text-emerald-600 dark:text-emerald-400">*</span>
                    </label>
                    <input type="text" id="tracking_input" name="tracking_input" required
                        value="{{ old('tracking_input') }}"
                        placeholder="{{ $service->input_placeholder }}"
                        @if($service->validation_rule === 'alphanumeric_15') maxlength="15" pattern="[A-Za-z0-9]{15}" title="15 alphanumeric characters" @endif
                        class="block w-full px-4 py-3 rounded-xl border border-slate-300 dark:border-slate-700 bg-white dark:bg-slate-800 text-slate-900 dark:text-white placeholder-slate-400 dark:placeholder-slate-500 text-sm focus:border-emerald-500 focus:ring-2 focus:ring-emerald-500/20 @if($service->validation_rule === 'alphanumeric_15') uppercase @endif">
                    @error('tracking_input')
                        <p class="mt-1.5 text-xs text-rose-600 dark:text-rose-400 font-medium">{{ $message }}</p>
                    @enderror
                </div>
            @endif

            @php
                $isIdentityVerification = in_array($service->slug, ['nin-verification', 'nin-verification-2', 'nin-verification-3', 'bvn-verification']);
            @endphp

            @if($isIdentityVerification)
                <!-- Check Consent Component (NDPR / Regulatory Compliance) -->
                <div class="p-4 rounded-xl border border-slate-200 dark:border-slate-800 bg-slate-50/70 dark:bg-slate-950/60 transition-colors">
                    <label for="check_consent" class="flex items-start gap-3 cursor-pointer select-none">
                        <div class="flex items-center h-5 mt-0.5">
                            <input type="checkbox" id="check_consent" name="consent" value="1" required
                                {{ old('consent') ? 'checked' : '' }}
                                class="w-4 h-4 rounded text-emerald-600 focus:ring-emerald-500/20 border-slate-300 dark:border-slate-700 bg-white dark:bg-slate-800 cursor-pointer">
                        </div>
                        <div class="flex-1 text-xs">
                            <div class="flex items-center gap-2">
                                <span class="font-bold text-slate-800 dark:text-slate-200 tracking-wide uppercase text-[11px]">
                                    Check Consent
                                </span>
                                <span class="text-rose-500 font-bold">*</span>
                                <span class="inline-flex items-center px-1.5 py-0.5 rounded text-[10px] font-semibold bg-emerald-100 text-emerald-800 dark:bg-emerald-950/60 dark:text-emerald-400">
                                    NDPR / NDPA
                                </span>
                            </div>
                            <p class="text-slate-600 dark:text-slate-400 mt-1 leading-relaxed">
                                I confirm that I have obtained the consent of the identity owner to verify their {{ str_contains($service->slug, 'bvn') ? 'Bank Verification Number (BVN)' : 'National Identification Number (NIN)' }} and process their identity record in compliance with the Nigeria Data Protection Act (NDPA/NDPR) and regulatory guidelines.
                            </p>
                        </div>
                    </label>
                    @error('consent')
                        <p class="mt-2 text-xs text-rose-600 dark:text-rose-400 font-semibold flex items-center gap-1">
                            <svg class="w-3.5 h-3.5 flex-shrink-0" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4m0 4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
                            </svg>
                            <span>{{ $message }}</span>
                        </p>
                    @enderror
                </div>
            @endif

            <!-- Balance & Submission Info -->
            <div class="p-4 rounded-xl bg-slate-50 dark:bg-slate-950 border border-slate-200 dark:border-slate-800 flex flex-col sm:flex-row sm:items-center justify-between gap-3 text-xs text-slate-600 dark:text-slate-400">
                <div class="flex items-center gap-2">
                    <svg class="w-4 h-4 text-emerald-600 dark:text-emerald-400 flex-shrink-0" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
                    </svg>
                    <span>Your Available Wallet Balance: <strong class="text-emerald-600 dark:text-emerald-400">{{ $wallet->formatted_balance }}</strong></span>
                </div>
                <a href="{{ route('wallet.index') }}" class="text-emerald-600 dark:text-emerald-400 hover:text-emerald-500 dark:hover:text-emerald-300 font-semibold underline">
                    Fund Wallet
                </a>
            </div>

            <!-- Submit Button -->
            <div>
                <button type="submit" id="service-submit-btn"
                    class="w-full sm:w-auto px-6 py-3 rounded-xl text-sm font-semibold text-white bg-gradient-to-r from-emerald-600 to-teal-600 hover:from-emerald-500 hover:to-teal-500 shadow-lg shadow-emerald-600/30 transition-all cursor-pointer inline-flex items-center justify-center gap-2">
                    <span id="service-submit-spinner" class="hidden">
                        <svg class="animate-spin h-4 w-4 text-white" fill="none" viewBox="0 0 24 24">
                            <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                            <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8v8H4z"></path>
                        </svg>
                    </span>
                    <span id="service-submit-text">
                        {{ $service->type === 'api' ? 'Verify' : 'Submit Request' }} &bull; {{ $service->formatted_price }}
                    </span>
                </button>
            </div>
        </form>
    </div>

    <!-- SRS Section 23: Reusable History Table for This Specific Service -->
    <div>
        <x-service-history-table :requests="$requests" :service="$service" :showService="false" />
    </div>
</div>

@if(in_array($service->slug, ['nin-verification', 'nin-verification-2', 'nin-verification-3']))
    <script>
        function switchNinMode(mode) {
            const ninWrap = document.getElementById('nin_field_wrap');
            const phoneWrap = document.getElementById('phone_field_wrap');
            const demoWrap = document.getElementById('demographics_field_wrap');

            const ninInput = document.getElementById('nin_input_field');
            const phoneInput = document.getElementById('phone_input_field');
            const fnInput = document.getElementById('first_name');
            const mnInput = document.getElementById('middle_name');
            const lnInput = document.getElementById('last_name');
            const dobInput = document.getElementById('dob');
            const genderInput = document.getElementById('gender');

            if (mode === 'nin') {
                if (ninWrap) ninWrap.classList.remove('hidden');
                if (phoneWrap) phoneWrap.classList.add('hidden');
                if (demoWrap) demoWrap.classList.add('hidden');
                if (ninInput) { ninInput.required = true; ninInput.disabled = false; }
                if (phoneInput) { phoneInput.required = false; phoneInput.disabled = true; }
                if (fnInput) { fnInput.required = false; fnInput.disabled = true; }
                if (mnInput) { mnInput.disabled = true; }
                if (lnInput) { lnInput.disabled = true; }
                if (dobInput) { dobInput.required = false; dobInput.disabled = true; }
                if (genderInput) { genderInput.disabled = true; }
            } else if (mode === 'phone') {
                if (ninWrap) ninWrap.classList.add('hidden');
                if (phoneWrap) phoneWrap.classList.remove('hidden');
                if (demoWrap) demoWrap.classList.add('hidden');
                if (ninInput) { ninInput.required = false; ninInput.disabled = true; }
                if (phoneInput) { phoneInput.required = true; phoneInput.disabled = false; }
                if (fnInput) { fnInput.required = false; fnInput.disabled = true; }
                if (mnInput) { mnInput.disabled = true; }
                if (lnInput) { lnInput.disabled = true; }
                if (dobInput) { dobInput.required = false; dobInput.disabled = true; }
                if (genderInput) { genderInput.disabled = true; }
            } else if (mode === 'demographics') {
                if (ninWrap) ninWrap.classList.add('hidden');
                if (phoneWrap) phoneWrap.classList.add('hidden');
                if (demoWrap) demoWrap.classList.remove('hidden');
                if (ninInput) { ninInput.required = false; ninInput.disabled = true; }
                if (phoneInput) { phoneInput.required = false; phoneInput.disabled = true; }
                if (fnInput) { fnInput.required = true; fnInput.disabled = false; }
                if (mnInput) { mnInput.disabled = false; }
                if (lnInput) { lnInput.disabled = false; }
                if (dobInput) { dobInput.required = true; dobInput.disabled = false; }
                if (genderInput) { genderInput.disabled = false; }
            }
        }

        document.addEventListener('DOMContentLoaded', function() {
            const selector = document.getElementById('nin_mode_selector');
            if (selector) {
                switchNinMode(selector.value);
            }
        });
    </script>
@endif

@if($service->is_bulk_allowed && ($service->validation_rule === 'alphanumeric_15' || in_array($service->slug, ['ipe-clearing', 'modification-ipe'])))
    <script>
        document.addEventListener('DOMContentLoaded', function() {
            const textarea = document.getElementById('tracking_input');
            const counter = document.getElementById('bulk_entry_counter');
            const unitPrice = {{ (float) $service->price }};

            if (!textarea) return;

            function format15CharText(raw) {
                if (!raw) return '';
                // Split by newlines, commas, semicolons, tabs, and spaces
                const tokens = raw.split(/[\r\n,;\s]+/);
                const entries = [];

                for (let i = 0; i < tokens.length; i++) {
                    let token = tokens[i].trim().toUpperCase();
                    if (!token) continue;

                    // When no spacing or comma is indicated and token exceeds 15 chars,
                    // chunk every 15 characters into its own line
                    if (token.length > 15) {
                        const chunks = token.match(/.{1,15}/g);
                        if (chunks) {
                            entries.push(...chunks);
                        }
                    } else {
                        entries.push(token);
                    }
                }

                return entries.join('\n');
            }

            function updateCounter() {
                if (!counter) return;
                const text = textarea.value.trim();
                if (!text) {
                    counter.textContent = '';
                    return;
                }
                const lines = text.split('\n').map(l => l.trim()).filter(l => l.length > 0);
                const count = lines.length;
                const totalCost = (count * unitPrice).toLocaleString('en-NG', { minimumFractionDigits: 2, maximumFractionDigits: 2 });
                counter.textContent = count === 1
                    ? `1 entry detected • ₦${totalCost}`
                    : `${count} entries detected • ₦${totalCost}`;
            }

            // Handle Paste: format immediately so each 15 characters occupies a new line
            textarea.addEventListener('paste', function(e) {
                e.preventDefault();
                const pastedText = (e.clipboardData || window.clipboardData).getData('text');
                if (!pastedText) return;

                const start = this.selectionStart;
                const end = this.selectionEnd;
                const current = this.value;

                const before = current.substring(0, start);
                const after = current.substring(end);

                const separatorBefore = before && !before.endsWith('\n') ? '\n' : '';
                const separatorAfter = after && !after.startsWith('\n') ? '\n' : '';

                const combined = before + separatorBefore + pastedText + separatorAfter + after;
                const formatted = format15CharText(combined);

                this.value = formatted;
                this.setSelectionRange(formatted.length, formatted.length);
                updateCounter();
            });

            // Handle typing / input: auto-wrap to newline when a continuous string reaches 16 characters or when commas/spaces are typed
            textarea.addEventListener('input', function(e) {
                const val = this.value;
                const needsFormatting = /[,;\t]/.test(val) || val.split('\n').some(line => line.trim().length > 15);

                if (needsFormatting) {
                    const cursorPos = this.selectionStart;
                    const prevLen = val.length;
                    const formatted = format15CharText(val);

                    this.value = formatted;
                    const newLen = formatted.length;
                    const nextPos = Math.min(newLen, Math.max(0, cursorPos + (newLen - prevLen)));
                    this.setSelectionRange(nextPos, nextPos);
                }
                updateCounter();
            });

            // Handle Blur: guarantee clean multi-line formatting
            textarea.addEventListener('blur', function() {
                if (this.value) {
                    this.value = format15CharText(this.value);
                    updateCounter();
                }
            });

            // Initial counter sync
            updateCounter();
        });
    </script>
@endif

<script>
    document.addEventListener('DOMContentLoaded', function() {
        const form = document.getElementById('service-submission-form');
        const submitBtn = document.getElementById('service-submit-btn');
        const spinner = document.getElementById('service-submit-spinner');
        const submitText = document.getElementById('service-submit-text');

        if (form && submitBtn) {
            form.addEventListener('submit', function(e) {
                const consentInput = document.getElementById('check_consent');
                if (consentInput && !consentInput.checked) {
                    e.preventDefault();
                    if (window.showFailedToast) {
                        window.showFailedToast('Consent Required', 'Please check consent confirming you have the owner\'s permission to verify this identity.');
                    }
                    consentInput.focus();
                    return false;
                }

                if (!form.checkValidity()) {
                    if (window.showFailedToast) {
                        window.showFailedToast('Validation Failed', 'Please complete all required input fields before submitting.');
                    }
                    return;
                }

                // Prevent disabling submit button synchronously which cancels form submission in some browsers
                setTimeout(function() {
                    submitBtn.disabled = true;
                    submitBtn.classList.add('opacity-80', 'cursor-not-allowed');
                }, 10);

                if (spinner) spinner.classList.remove('hidden');
                if (submitText) submitText.textContent = 'Processing...';
            });
        }

        // Auto-scroll to verification result or error alert if present
        const targetScroll = document.getElementById('verification-result') || document.getElementById('submission-error');
        if (targetScroll) {
            setTimeout(function() {
                targetScroll.scrollIntoView({ behavior: 'smooth', block: 'start' });
            }, 150);
        }
    });
</script>
@endsection
