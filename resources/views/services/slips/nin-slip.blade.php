<!DOCTYPE html>
<html lang="en" class="h-full">

<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    @php
        // Safe attribute resolver that never throws undefined index and falls back cleanly
        $d = is_array($data ?? null) ? $data : [];
        $val = function (string $key, string $fallback = '—') use ($d) {
            return isset($d[$key]) && $d[$key] !== null && $d[$key] !== '' ? (string) $d[$key] : $fallback;
        };

        $ninPlain = $val('nin', $serviceRequest->tracking_input);
        $requestedSlip = strtolower(trim((string) request('slip', '')));
        $validSlips = ['standard', 'premium', 'compact', 'improve'];
        $isDedicatedPreview = in_array($requestedSlip, $validSlips, true);
        $activeSlip = $isDedicatedPreview ? $requestedSlip : 'standard';

        $slipTitleMap = [
            'standard' => 'Standard NIMC Slip (NINS)',
            'premium' => 'Premium NIN Card',
            'compact' => 'Compact Slip',
            'improve' => 'IMPROVE-SLIP',
        ];
    @endphp

    <title>
        @if ($isDedicatedPreview)
            {{ $slipTitleMap[$activeSlip] ?? 'Official Slip' }} Preview & Download - {{ $ninPlain }}
        @else
            NIN Verification Result & Slips - {{ $ninPlain }}
        @endif
    </title>

    <!-- Google Fonts: Roboto -->
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link
        href="https://fonts.googleapis.com/css2?family=Roboto:ital,wght@0,300;0,400;0,500;0,700;0,900;1,400;1,700&display=swap"
        rel="stylesheet">

    @vite(['resources/css/app.css', 'resources/js/app.js'])
    <script src="https://cdn.tailwindcss.com"></script>

    <!-- html2canvas for instant client-side high-res slip rendering -->
    <script src="https://cdnjs.cloudflare.com/ajax/libs/html2canvas/1.4.1/html2canvas.min.js"></script>
    <!-- jsPDF for direct client-side high-fidelity PDF slip downloads -->
    <script src="https://cdnjs.cloudflare.com/ajax/libs/jspdf/2.5.1/jspdf.umd.min.js"></script>
    <!-- QRCode.js for generating official scannable QR code on premium card and IMPROVE-SLIP -->
    <script src="https://cdnjs.cloudflare.com/ajax/libs/qrcodejs/1.0.0/qrcode.min.js"></script>

    <style>
        #qrcode-premium-target img,
        #qrcode-premium-target canvas,
        #qrcode-improve-target img,
        #qrcode-improve-target canvas {
            width: 100% !important;
            height: 100% !important;
            object-fit: contain !important;
        }

        @media print {
            body {
                background: #ffffff !important;
                color: #000000 !important;
                padding: 0 !important;
                margin: 0 !important;
            }

            .no-print {
                display: none !important;
            }

            .print-target {
                display: block !important;
                visibility: visible !important;
                margin: 0 auto !important;
                box-shadow: none !important;
                -webkit-print-color-adjust: exact !important;
                print-color-adjust: exact !important;
            }

            /* Hide inactive slips during print */
            .slip-pane:not(.active-print) {
                display: none !important;
            }
        }

        .slip-watermark {
            background-image: radial-gradient(rgba(0, 135, 81, 0.05) 1px, transparent 1px);
            background-size: 16px 16px;
        }
    </style>
</head>

<body
    class="bg-slate-50 dark:bg-slate-950 text-slate-900 dark:text-slate-100 font-sans min-h-screen antialiased flex flex-col selection:bg-emerald-500 selection:text-white">

    @php
        $rawImage = $d['photo_base64'] ?? ($d['image'] ?? ($d['photo'] ?? null));
        if (!empty($rawImage) && is_string($rawImage)) {
            $trimmed = trim($rawImage);
            $resolvedPhoto =
                str_starts_with($trimmed, 'data:') || str_starts_with($trimmed, 'http')
                    ? $trimmed
                    : 'data:image/jpeg;base64,' . ltrim($trimmed);
        } else {
            $resolvedPhoto = null;
        }

        $rawPayload = $serviceRequest->result_payload['raw'] ?? [];

        $rawMiddleName = $d['middle_name'] ?? ($d['middlename'] ?? ($rawPayload['message']['middlename'] ?? null));
        $trimmedMid = is_string($rawMiddleName) ? trim($rawMiddleName) : '';
        $isMaskedMid =
            empty($trimmedMid) ||
            $trimmedMid === '—' ||
            preg_match('/^[\*\s—\-]+$/', $trimmedMid) ||
            in_array(strtolower($trimmedMid), ['null', 'nil', 'none', 'n/a', 'na'], true);
        $middleName = !$isMaskedMid ? $trimmedMid : '';

        $rawPmiddlename = $d['pmiddlename'] ?? ($rawPayload['message']['pmiddlename'] ?? null);
        $trimmedPmid = is_string($rawPmiddlename) ? trim($rawPmiddlename) : '';
        $isMaskedPmid =
            empty($trimmedPmid) ||
            preg_match('/^[\*\s—\-]+$/', $trimmedPmid) ||
            in_array(strtolower($trimmedPmid), ['null', 'nil', 'none', 'n/a', 'na'], true);
        $pMiddleName = !$isMaskedPmid ? $trimmedPmid : 'None';

        $constructedParts = array_filter([$val('first_name', ''), $middleName, $val('surname', '')]);
        $constructedName = implode(' ', $constructedParts);

        $rawFullName = !empty($d['full_name']) ? (string) $d['full_name'] : '';
        $cleanedFullName = trim(preg_replace('/\s*[\*]+\s*/', ' ', $rawFullName));
        $cleanedFullName = preg_replace('/\s+/', ' ', $cleanedFullName);
        $fullName = !empty($cleanedFullName) ? $cleanedFullName : ($constructedName ?: 'Verified Customer');

        $ninFormatted = $val('nin_formatted', $val('nin', $serviceRequest->tracking_input));

        $rawMsgTrackingId =
            $rawPayload['message']['trackingId'] ??
            ($rawPayload['message']['tracking_id'] ??
                ($rawPayload['message']['trackingID'] ??
                    ($rawPayload['message']['trackingid'] ??
                        ($rawPayload['trackingId'] ?? ($rawPayload['tracking_id'] ?? null)))));

        $rawTracking = !empty($rawMsgTrackingId)
            ? (string) $rawMsgTrackingId
            : (!empty($d['tracking_id'])
                ? (string) $d['tracking_id']
                : (!empty($d['trackingId'])
                    ? (string) $d['trackingId']
                    : ''));

        $transId = $val('trans_id', $val('transID', ''));

        // Suppress any legacy synthetic reference or trans_id tracking ID
        $isSynthetic = false;
        if (!empty($rawTracking)) {
            $trackClean = strtoupper(str_replace(['-', '_'], '', $rawTracking));
            $refClean = strtoupper(str_replace(['-', '_'], '', $serviceRequest->reference));
            $transClean = strtoupper(str_replace(['-', '_'], '', $transId));

            if (str_starts_with($trackClean, 'TRK')) {
                $trackSuffix = substr($trackClean, 3);
                if (
                    !empty($trackSuffix) &&
                    ((!empty($transClean) && str_starts_with($transClean, $trackSuffix)) ||
                        (!empty($transClean) && str_contains($transClean, $trackSuffix)) ||
                        (!empty($refClean) && str_contains($refClean, $trackSuffix)) ||
                        (!empty($rawPayload) && empty($rawMsgTrackingId)) ||
                        $rawTracking === 'TRK-' . strtoupper(substr($transClean, 0, 10)))
                ) {
                    $isSynthetic = true;
                }
            }
        }

        $trackingId = $isSynthetic || empty($rawTracking) || $rawTracking === '—' ? '' : $rawTracking;
        $phoneFormatted = $val('phone_number', $val('telephoneno', '—'));
        $residenceAddress = $val('residence_address', $val('residence_address_line1', '—'));
        $nokAddress = $val('nok_address', $val('nok_address1', '—'));

        $rawGender = trim((string) $val('gender', ''));
        $genderLetter = !empty($rawGender) && $rawGender !== '—' ? strtoupper(substr($rawGender, 0, 1)) : '—';

        $residenceStreet = !empty($d['residence_address_line1'])
            ? (string) $d['residence_address_line1']
            : (!empty($d['residence_AdressLine1'])
                ? (string) $d['residence_AdressLine1']
                : (!empty($d['residence_address'])
                    ? (string) $d['residence_address']
                    : '—'));

        $residenceTown = !empty($d['residence_lga'])
            ? (string) $d['residence_lga']
            : (!empty($d['residence_town'])
                ? (string) $d['residence_town']
                : '');

        $residenceState = !empty($d['residence_state']) ? (string) $d['residence_state'] : '';

        // Format dates and NIN chunks for cards (Premium and IMPROVE-SLIP)
        $ninClean = preg_replace('/\D/', '', $ninPlain);
        $p1 = strlen($ninClean) >= 4 ? substr($ninClean, 0, 4) : $ninClean;
        $p2 = strlen($ninClean) >= 7 ? substr($ninClean, 4, 3) : (strlen($ninClean) > 4 ? substr($ninClean, 4) : '');
        $p3 = strlen($ninClean) >= 11 ? substr($ninClean, 7, 4) : (strlen($ninClean) > 7 ? substr($ninClean, 7) : '');

        $rawDob = $val('date_of_birth', $val('birthdate', ''));
        $dobDisplay = $rawDob;
        $dobNumeric = $rawDob;
        if (!empty($rawDob) && $rawDob !== '—') {
            try {
                $dobDisplay = strtoupper(\Carbon\Carbon::parse($rawDob)->format('d M, Y'));
                $dobNumeric = \Carbon\Carbon::parse($rawDob)->format('d-m-Y');
            } catch (\Throwable $e) {
                $dobDisplay = strtoupper($rawDob);
                $dobNumeric = $rawDob;
            }
        }

        $rawIssue = $val('issue_date', '');
        if (!empty($rawIssue) && $rawIssue !== '—') {
            try {
                $issueDateDisplay = strtoupper(\Carbon\Carbon::parse($rawIssue)->format('d M, Y'));
            } catch (\Throwable $e) {
                $issueDateDisplay = strtoupper($rawIssue);
            }
        } else {
            $issueDateDisplay = $serviceRequest->created_at ? strtoupper($serviceRequest->created_at->format('d M, Y')) : strtoupper(date('d M, Y'));
        }

        // Known fields already rendered explicitly in dedicated cards
        $knownFieldKeys = [
            'nin',
            'nin_formatted',
            'tracking_id',
            'trackingid',
            'trans_id',
            'transid',
            'full_name',
            'first_name',
            'firstname',
            'middle_name',
            'middlename',
            'surname',
            'pmiddlename',
            'gender',
            'date_of_birth',
            'birthdate',
            'phone_number',
            'telephoneno',
            'marital_status',
            'maritalstatus',
            'height',
            'heigth',
            'profession',
            'religion',
            'spoken_language',
            'nspokenlang',
            'state_of_origin',
            'self_origin_state',
            'lga_of_origin',
            'self_origin_lga',
            'place_of_origin',
            'self_origin_place',
            'country',
            'birth_country',
            'birthcountry',
            'birth_state',
            'birthstate',
            'birth_lga',
            'birthlga',
            'residence_address',
            'residence_address_line1',
            'residence_adressline1',
            'residence_town',
            'residence_lga',
            'residence_state',
            'residence_status',
            'residencestatus',
            'nok_name',
            'nok_firstname',
            'nok_surname',
            'nok_relationship',
            'nok_relationshiptype',
            'nok_telephone',
            'nok_phone',
            'nok_telephoneno',
            'nok_address',
            'nok_address1',
            'nok_town',
            'nok_lga',
            'nok_state',
            'provider',
            'verification_mode',
            'verified_at',
            'issue_date',
            'raw',
            'photo_base64',
            'image',
            'signature',
            'status',
            'success',
        ];

        $extraProviderFields = [];
        $rawMessageFields = $rawPayload['message'] ?? [];
        $allCandidateFields = array_merge(is_array($rawMessageFields) ? $rawMessageFields : [], $d);
        foreach ($allCandidateFields as $k => $v) {
            $lowerKey = strtolower((string) $k);
            if (!in_array($lowerKey, $knownFieldKeys, true) && !in_array($k, $knownFieldKeys, true)) {
                if (is_scalar($v) && $v !== null && $v !== '' && $v !== '—') {
                    $cleanKey = ucwords(str_replace(['_', '-'], ' ', (string) $k));
                    $extraProviderFields[$cleanKey] = (string) $v;
                }
            }
        }
    @endphp

    @if ($isDedicatedPreview)
        {{-- ============================================================ --}}
        {{-- DEDICATED SLIP PREVIEW & AUTO-DOWNLOAD PAGE                  --}}
        {{-- ============================================================ --}}
        <!-- Sticky Header with Actions & Auto-Download Status -->
        <header
            class="no-print sticky top-0 z-40 bg-white/95 dark:bg-slate-900/95 backdrop-blur-md border-b border-slate-200 dark:border-slate-800 transition-colors">
            <div class="max-w-6xl mx-auto px-4 sm:px-6 lg:px-8 h-16 flex items-center justify-between gap-4">
                <div class="flex items-center gap-3">
                    <a href="{{ route('services.slip', $serviceRequest->reference) }}"
                        class="inline-flex items-center gap-2 text-xs font-bold text-slate-700 dark:text-slate-300 hover:text-emerald-600 dark:hover:text-emerald-400 bg-slate-100 dark:bg-slate-800 hover:bg-slate-200 dark:hover:bg-slate-700/80 px-3.5 py-2 rounded-xl border border-slate-200 dark:border-slate-700/80 transition shadow-xs">
                        <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5"
                                d="M10 19l-7-7m0 0l7-7m-7 7h18" />
                        </svg>
                        <span>&larr; Back to Full Record</span>
                    </a>

                    <span class="hidden sm:inline-flex items-center px-3 py-1 rounded-full text-xs font-black uppercase tracking-wider bg-emerald-100 text-emerald-800 dark:bg-emerald-950 dark:text-emerald-300">
                        {{ $slipTitleMap[$activeSlip] ?? 'Official Slip' }}
                    </span>
                </div>

                <div class="flex items-center gap-2.5">
                    <!-- Status Notification Badge -->
                    <span id="auto-download-badge"
                        class="hidden md:inline-flex items-center gap-1.5 px-3 py-1.5 rounded-full text-xs font-bold bg-amber-50 dark:bg-amber-950/40 text-amber-700 dark:text-amber-300 border border-amber-200 dark:border-amber-800/60 shadow-xs">
                        <svg class="w-3.5 h-3.5 animate-spin" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                            <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8v8H4z"></path>
                        </svg>
                        <span>Auto-Downloading PDF...</span>
                    </span>

                    <!-- Manual Download PDF Button -->
                    <button type="button" id="download-pdf-btn" onclick="downloadSlipPdf()"
                        class="px-4 py-2 rounded-xl bg-emerald-600 hover:bg-emerald-700 text-white font-bold text-xs shadow-md shadow-emerald-600/30 flex items-center gap-1.5 transition cursor-pointer">
                        <svg class="w-3.5 h-3.5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5"
                                d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-4l-4 4m0 0l-4-4m4 4V4" />
                        </svg>
                        <span>Download PDF</span>
                    </button>

                    <!-- Print Button -->
                    <button type="button" onclick="printActiveSlip()"
                        class="px-3.5 py-2 rounded-xl bg-slate-100 dark:bg-slate-800 hover:bg-slate-200 dark:hover:bg-slate-700 text-slate-700 dark:text-slate-200 font-bold text-xs flex items-center gap-1.5 transition cursor-pointer border border-slate-200 dark:border-slate-700">
                        <svg class="w-3.5 h-3.5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                d="M17 17h2a2 2 0 002-2v-4a2 2 0 00-2-2H5a2 2 0 00-2 2v4a2 2 0 002 2h2m2 4h6a2 2 0 002-2v-4a2 2 0 00-2-2H9a2 2 0 00-2 2v4a2 2 0 002 2zm8-12V5a2 2 0 00-2-2H9a2 2 0 00-2 2v4h10z" />
                        </svg>
                        <span class="hidden sm:inline">Print</span>
                    </button>

                    <!-- Close Tab Button -->
                    <button type="button" onclick="window.close()"
                        class="px-3 py-2 rounded-xl bg-slate-100 dark:bg-slate-800 hover:bg-slate-200 dark:hover:bg-slate-700 text-slate-500 font-bold text-xs transition cursor-pointer"
                        title="Close this window">
                        ✕
                    </button>
                </div>
            </div>
        </header>

        <!-- Main Preview Body -->
        <main class="flex-1 max-w-6xl w-full mx-auto px-4 sm:px-6 lg:px-8 py-6">

            <!-- Slip Preview Canvas Area -->
            <div class="p-4 sm:p-8 rounded-3xl bg-slate-200/70 dark:bg-slate-900/50 border border-slate-300/80 dark:border-slate-800 flex items-center justify-center overflow-x-auto min-h-[500px]">
                @include('services.slips.partials.nin-slip-canvases')
            </div>

        </main>
    @else
        {{-- ============================================================ --}}
        {{-- MAIN SUMMARY PAGE WITH SLIP DOWNLOAD BUTTONS (NO PREVIEW)    --}}
        {{-- ============================================================ --}}
        <!-- Top Sticky Header Navigation Bar (Hidden on Print) -->
        <header
            class="no-print sticky top-0 z-40 bg-white/90 dark:bg-slate-900/90 backdrop-blur-md border-b border-slate-200 dark:border-slate-800 transition-colors">
            <div class="max-w-6xl mx-auto px-4 sm:px-6 lg:px-8 h-16 flex items-center justify-between gap-4">
                <div class="flex items-center gap-3">
                    <a href="{{ route('services.show', $serviceRequest->service->slug) }}"
                        class="inline-flex items-center gap-2 text-xs font-bold text-slate-700 dark:text-slate-300 hover:text-emerald-600 dark:hover:text-emerald-400 bg-slate-100 dark:bg-slate-800 hover:bg-slate-200 dark:hover:bg-slate-700/80 px-3.5 py-2 rounded-xl border border-slate-200 dark:border-slate-700/80 transition shadow-xs">
                        <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5"
                                d="M10 19l-7-7m0 0l7-7m-7 7h18" />
                        </svg>
                        <span>Back to {{ $serviceRequest->service->name }}</span>
                    </a>
                </div>

                <div class="flex items-center gap-2.5">
                    <a href="{{ route('services.show', $serviceRequest->service->slug) }}"
                        class="inline-flex items-center gap-1.5 px-3.5 py-2 rounded-xl bg-slate-100 dark:bg-slate-800 hover:bg-slate-200 dark:hover:bg-slate-700 text-slate-700 dark:text-slate-300 text-xs font-semibold transition border border-slate-200 dark:border-slate-700">
                        <svg class="w-3.5 h-3.5 text-emerald-600 dark:text-emerald-400" fill="none" viewBox="0 0 24 24"
                            stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M12 4v16m8-8H4" />
                        </svg>
                        <span>Verify Another NIN</span>
                    </a>
                </div>
            </div>
        </header>

        <!-- Main Content Container -->
        <main class="flex-1 max-w-6xl w-full mx-auto px-4 sm:px-6 lg:px-8 py-8 space-y-8">

            <!-- Flash Success Notification (if redirected immediately after verification) -->
            @if (session('success'))
                <div
                    class="no-print rounded-2xl bg-gradient-to-r from-emerald-500/15 via-teal-500/10 to-emerald-500/5 border-2 border-emerald-500/30 p-4 sm:p-5 flex items-start justify-between gap-4 shadow-sm animate-fade-in">
                    <div class="flex items-start gap-3.5">
                        <div
                            class="w-9 h-9 rounded-xl bg-emerald-500 text-white flex items-center justify-center shrink-0 shadow-md shadow-emerald-500/30 mt-0.5">
                            <svg class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="3" d="M5 13l4 4L19 7" />
                            </svg>
                        </div>
                        <div>
                            <h3 class="text-sm font-black uppercase tracking-wider text-emerald-800 dark:text-emerald-300">
                                Verification Successful
                            </h3>
                            <p class="text-xs text-slate-600 dark:text-slate-300 mt-0.5 leading-relaxed">
                                {{ session('success') }} All demographic records have been matched against the national
                                database. Choose any official slip below to preview and auto-download your verified document.
                            </p>
                        </div>
                    </div>
                    <a href="#slips-download-center"
                        class="shrink-0 text-xs font-bold text-emerald-700 dark:text-emerald-400 hover:underline">
                        Jump to Slips &darr;
                    </a>
                </div>
            @endif

            <!-- ============================================================ -->
            <!-- SECTION 1: APPLICANT MASTER SUMMARY HERO CARD                -->
            <!-- ============================================================ -->
            <section
                class="rounded-3xl bg-white dark:bg-slate-900 border border-slate-200/90 dark:border-slate-800 shadow-xl shadow-slate-200/50 dark:shadow-none overflow-hidden relative">
                <div class="h-2.5 bg-gradient-to-r from-emerald-600 via-teal-500 to-emerald-600"></div>

                <div class="p-6 sm:p-8">
                    <!-- Applicant Verified Photo -->
                    <div class="pb-6 border-b border-slate-100 dark:border-slate-800">
                        <div
                            class="w-24 h-32 sm:w-28 sm:h-36 rounded-2xl border-2 border-emerald-600/70 overflow-hidden shadow-md bg-slate-100 dark:bg-slate-800 relative shrink-0 flex items-center justify-center">
                            @if (!empty($resolvedPhoto))
                                <img src="{{ $resolvedPhoto }}" alt="Verified Photo" class="w-full h-full object-cover">
                            @else
                                <div class="flex flex-col items-center justify-center text-slate-400">
                                    <svg class="w-12 h-12" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5"
                                            d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z" />
                                    </svg>
                                </div>
                            @endif
                        </div>
                    </div>

                    <!-- ============================================================ -->
                    <!-- SECTION 2: COMPREHENSIVE RESPONSE PARTICULARS BREAKDOWN     -->
                    <!-- ============================================================ -->
                    <div class="pt-6 space-y-8">
                        <!-- Part A: Core Identity & Demographics -->
                        <div>
                            <h2
                                class="text-xs font-black uppercase tracking-wider text-slate-700 dark:text-slate-300 mb-3 flex items-center gap-2">
                                <span class="w-2 h-2 rounded-full bg-emerald-500"></span>
                                <span>Personal Informations</span>
                            </h2>
                            <div class="grid grid-cols-2 sm:grid-cols-3 lg:grid-cols-4 gap-3.5 text-xs">
                                <div
                                    class="p-3.5 rounded-2xl bg-emerald-50/70 dark:bg-emerald-950/30 border border-emerald-200/80 dark:border-emerald-800/60 shadow-xs">
                                    <span
                                        class="text-[10px] uppercase font-bold text-emerald-700 dark:text-emerald-400 block mb-1">National
                                        Identification Number (NIN)</span>
                                    <span
                                        class="font-mono font-black text-emerald-900 dark:text-emerald-200 text-sm tracking-wider">{{ $ninFormatted }}</span>
                                </div>

                                <div
                                    class="p-3.5 rounded-2xl bg-slate-50 dark:bg-slate-800/70 border border-slate-200/80 dark:border-slate-700/80 shadow-xs">
                                    <span class="text-[10px] uppercase font-bold text-slate-400 block mb-1">Full Legal
                                        Name</span>
                                    <span
                                        class="font-black text-slate-900 dark:text-white uppercase text-sm truncate block">{{ $fullName }}</span>
                                </div>

                                <div
                                    class="p-3.5 rounded-2xl bg-slate-50 dark:bg-slate-800/70 border border-slate-200/80 dark:border-slate-700/80 shadow-xs">
                                    <span class="text-[10px] uppercase font-bold text-slate-400 block mb-1">Surname</span>
                                    <span
                                        class="font-black text-slate-900 dark:text-white uppercase text-sm">{{ $val('surname') }}</span>
                                </div>

                                <div
                                    class="p-3.5 rounded-2xl bg-slate-50 dark:bg-slate-800/70 border border-slate-200/80 dark:border-slate-700/80 shadow-xs">
                                    <span class="text-[10px] uppercase font-bold text-slate-400 block mb-1">First
                                        Name</span>
                                    <span
                                        class="font-black text-slate-900 dark:text-white uppercase text-sm">{{ $val('first_name') }}</span>
                                </div>

                                <div
                                    class="p-3.5 rounded-2xl bg-slate-50 dark:bg-slate-800/70 border border-slate-200/80 dark:border-slate-700/80 shadow-xs">
                                    <span class="text-[10px] uppercase font-bold text-slate-400 block mb-1">Middle
                                        Name</span>
                                    <span
                                        class="font-black text-slate-900 dark:text-white uppercase text-sm">{{ $middleName }}</span>
                                </div>

                                <div
                                    class="p-3.5 rounded-2xl bg-slate-50 dark:bg-slate-800/70 border border-slate-200/80 dark:border-slate-700/80 shadow-xs">
                                    <span class="text-[10px] uppercase font-bold text-slate-400 block mb-1">Previous Middle
                                        Name</span>
                                    <span
                                        class="font-bold text-slate-900 dark:text-white uppercase">{{ $pMiddleName }}</span>
                                </div>

                                <div
                                    class="p-3.5 rounded-2xl bg-slate-50 dark:bg-slate-800/70 border border-slate-200/80 dark:border-slate-700/80 shadow-xs">
                                    <span class="text-[10px] uppercase font-bold text-slate-400 block mb-1">Gender</span>
                                    <span
                                        class="font-extrabold text-slate-900 dark:text-white uppercase">{{ $val('gender') }}</span>
                                </div>

                                <div
                                    class="p-3.5 rounded-2xl bg-slate-50 dark:bg-slate-800/70 border border-slate-200/80 dark:border-slate-700/80 shadow-xs">
                                    <span class="text-[10px] uppercase font-bold text-slate-400 block mb-1">Date of
                                        Birth</span>
                                    <span
                                        class="font-black text-slate-900 dark:text-white text-sm">{{ $val('date_of_birth', $val('birthdate')) }}</span>
                                </div>

                                <div
                                    class="p-3.5 rounded-2xl bg-slate-50 dark:bg-slate-800/70 border border-slate-200/80 dark:border-slate-700/80 shadow-xs">
                                    <span class="text-[10px] uppercase font-bold text-slate-400 block mb-1">Telephone
                                        Number</span>
                                    <span
                                        class="font-mono font-extrabold text-slate-900 dark:text-white">{{ $phoneFormatted }}</span>
                                </div>

                                <div
                                    class="p-3.5 rounded-2xl bg-slate-50 dark:bg-slate-800/70 border border-slate-200/80 dark:border-slate-700/80 shadow-xs">
                                    <span class="text-[10px] uppercase font-bold text-slate-400 block mb-1">Marital
                                        Status</span>
                                    <span
                                        class="font-bold text-slate-900 dark:text-white">{{ $val('marital_status', $val('maritalstatus')) }}</span>
                                </div>

                                <div
                                    class="p-3.5 rounded-2xl bg-slate-50 dark:bg-slate-800/70 border border-slate-200/80 dark:border-slate-700/80 shadow-xs">
                                    <span class="text-[10px] uppercase font-bold text-slate-400 block mb-1">Height</span>
                                    <span class="font-bold text-slate-900 dark:text-white">
                                        @php $rawH = $val('height', $val('heigth')); @endphp
                                        {{ !empty($rawH) && $rawH !== '—' ? (is_numeric($rawH) ? $rawH . ' cm' : $rawH) : '—' }}
                                    </span>
                                </div>

                                <div
                                    class="p-3.5 rounded-2xl bg-slate-50 dark:bg-slate-800/70 border border-slate-200/80 dark:border-slate-700/80 shadow-xs">
                                    <span
                                        class="text-[10px] uppercase font-bold text-slate-400 block mb-1">Profession</span>
                                    <span
                                        class="font-bold text-slate-900 dark:text-white">{{ $val('profession') }}</span>
                                </div>

                                <div
                                    class="p-3.5 rounded-2xl bg-slate-50 dark:bg-slate-800/70 border border-slate-200/80 dark:border-slate-700/80 shadow-xs">
                                    <span
                                        class="text-[10px] uppercase font-bold text-slate-400 block mb-1">Religion</span>
                                    <span
                                        class="font-bold text-slate-900 dark:text-white">{{ $val('religion') }}</span>
                                </div>

                                <div
                                    class="p-3.5 rounded-2xl bg-slate-50 dark:bg-slate-800/70 border border-slate-200/80 dark:border-slate-700/80 shadow-xs">
                                    <span class="text-[10px] uppercase font-bold text-slate-400 block mb-1">Spoken
                                        Language</span>
                                    <span
                                        class="font-bold text-slate-900 dark:text-white">{{ $val('spoken_language', $val('nspokenlang')) }}</span>
                                </div>

                                <div
                                    class="p-3.5 rounded-2xl bg-slate-50 dark:bg-slate-800/70 border border-slate-200/80 dark:border-slate-700/80 shadow-xs">
                                    <span class="text-[10px] uppercase font-bold text-slate-400 block mb-1">Tracking
                                        ID</span>
                                    <span
                                        class="font-mono font-bold text-slate-900 dark:text-white">{{ !empty($trackingId) ? $trackingId : '—' }}</span>
                                </div>
                            </div>
                        </div>

                        <!-- Part B: Place of Origin & Birth Particulars -->
                        <div>
                            <h2
                                class="text-xs font-black uppercase tracking-wider text-slate-700 dark:text-slate-300 mb-3 flex items-center gap-2">
                                <span class="w-2 h-2 rounded-full bg-emerald-500"></span>
                                <span>Origin & Birth Particulars</span>
                            </h2>
                            <div class="grid grid-cols-2 sm:grid-cols-3 lg:grid-cols-4 gap-3.5 text-xs">
                                <div
                                    class="p-3.5 rounded-2xl bg-slate-50 dark:bg-slate-800/70 border border-slate-200/80 dark:border-slate-700/80 shadow-xs">
                                    <span class="text-[10px] uppercase font-bold text-slate-400 block mb-1">State of
                                        Origin</span>
                                    <span
                                        class="font-bold text-slate-900 dark:text-white">{{ $val('state_of_origin', $val('self_origin_state')) }}</span>
                                </div>

                                <div
                                    class="p-3.5 rounded-2xl bg-slate-50 dark:bg-slate-800/70 border border-slate-200/80 dark:border-slate-700/80 shadow-xs">
                                    <span class="text-[10px] uppercase font-bold text-slate-400 block mb-1">LGA of
                                        Origin</span>
                                    <span
                                        class="font-bold text-slate-900 dark:text-white">{{ $val('lga_of_origin', $val('self_origin_lga')) }}</span>
                                </div>

                                <div
                                    class="p-3.5 rounded-2xl bg-slate-50 dark:bg-slate-800/70 border border-slate-200/80 dark:border-slate-700/80 shadow-xs">
                                    <span class="text-[10px] uppercase font-bold text-slate-400 block mb-1">Place of
                                        Origin</span>
                                    <span
                                        class="font-bold text-slate-900 dark:text-white">{{ $val('place_of_origin', $val('self_origin_place')) }}</span>
                                </div>

                                <div
                                    class="p-3.5 rounded-2xl bg-slate-50 dark:bg-slate-800/70 border border-slate-200/80 dark:border-slate-700/80 shadow-xs">
                                    <span
                                        class="text-[10px] uppercase font-bold text-slate-400 block mb-1">Country</span>
                                    <span
                                        class="font-bold text-slate-900 dark:text-white">{{ $val('country', 'Nigeria') }}</span>
                                </div>

                                <div
                                    class="p-3.5 rounded-2xl bg-slate-50 dark:bg-slate-800/70 border border-slate-200/80 dark:border-slate-700/80 shadow-xs">
                                    <span class="text-[10px] uppercase font-bold text-slate-400 block mb-1">Birth
                                        Country</span>
                                    <span
                                        class="font-bold text-slate-900 dark:text-white">{{ $val('birth_country', $val('birthcountry', 'Nigeria')) }}</span>
                                </div>

                                <div
                                    class="p-3.5 rounded-2xl bg-slate-50 dark:bg-slate-800/70 border border-slate-200/80 dark:border-slate-700/80 shadow-xs">
                                    <span class="text-[10px] uppercase font-bold text-slate-400 block mb-1">Birth
                                        State</span>
                                    <span
                                        class="font-bold text-slate-900 dark:text-white">{{ $val('birth_state', $val('birthstate', '—')) }}</span>
                                </div>

                                <div
                                    class="p-3.5 rounded-2xl bg-slate-50 dark:bg-slate-800/70 border border-slate-200/80 dark:border-slate-700/80 shadow-xs">
                                    <span class="text-[10px] uppercase font-bold text-slate-400 block mb-1">Birth
                                        LGA</span>
                                    <span
                                        class="font-bold text-slate-900 dark:text-white">{{ $val('birth_lga', $val('birthlga', '—')) }}</span>
                                </div>

                                <div
                                    class="p-3.5 rounded-2xl bg-slate-50 dark:bg-slate-800/70 border border-slate-200/80 dark:border-slate-700/80 shadow-xs">
                                    <span class="text-[10px] uppercase font-bold text-slate-400 block mb-1">Birth Place /
                                        Location</span>
                                    <span class="font-bold text-slate-900 dark:text-white">
                                        {{ !empty($d['birth_lga']) || !empty($d['birth_state']) ? trim(($d['birth_lga'] ?? '') . ', ' . ($d['birth_state'] ?? ''), ', ') : '—' }}
                                    </span>
                                </div>
                            </div>
                        </div>

                        <!-- Part C: Residential Address Details -->
                        <div>
                            <h2
                                class="text-xs font-black uppercase tracking-wider text-slate-700 dark:text-slate-300 mb-3 flex items-center gap-2">
                                <span class="w-2 h-2 rounded-full bg-emerald-500"></span>
                                <span>Residential Address Details</span>
                            </h2>
                            <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 gap-3.5 text-xs">
                                <div
                                    class="sm:col-span-2 p-3.5 rounded-2xl bg-slate-50 dark:bg-slate-800/70 border border-slate-200/80 dark:border-slate-700/80 shadow-xs">
                                    <span class="text-[10px] uppercase font-bold text-slate-400 block mb-1">Full
                                        Residential Address</span>
                                    <span
                                        class="font-bold text-slate-900 dark:text-white leading-relaxed">{{ $residenceAddress }}</span>
                                </div>

                                <div
                                    class="p-3.5 rounded-2xl bg-slate-50 dark:bg-slate-800/70 border border-slate-200/80 dark:border-slate-700/80 shadow-xs">
                                    <span class="text-[10px] uppercase font-bold text-slate-400 block mb-1">Street Address
                                        Line 1</span>
                                    <span
                                        class="font-bold text-slate-900 dark:text-white leading-relaxed">{{ $residenceStreet }}</span>
                                </div>

                                <div
                                    class="p-3.5 rounded-2xl bg-slate-50 dark:bg-slate-800/70 border border-slate-200/80 dark:border-slate-700/80 shadow-xs">
                                    <span class="text-[10px] uppercase font-bold text-slate-400 block mb-1">Town /
                                        City</span>
                                    <span
                                        class="font-bold text-slate-900 dark:text-white">{{ $val('residence_town') }}</span>
                                </div>

                                <div
                                    class="p-3.5 rounded-2xl bg-slate-50 dark:bg-slate-800/70 border border-slate-200/80 dark:border-slate-700/80 shadow-xs">
                                    <span class="text-[10px] uppercase font-bold text-slate-400 block mb-1">Residential
                                        LGA</span>
                                    <span
                                        class="font-bold text-slate-900 dark:text-white">{{ $val('residence_lga') }}</span>
                                </div>

                                <div
                                    class="p-3.5 rounded-2xl bg-slate-50 dark:bg-slate-800/70 border border-slate-200/80 dark:border-slate-700/80 shadow-xs">
                                    <span class="text-[10px] uppercase font-bold text-slate-400 block mb-1">Residential
                                        State</span>
                                    <span
                                        class="font-bold text-slate-900 dark:text-white">{{ $val('residence_state') }}</span>
                                </div>

                                <div
                                    class="p-3.5 rounded-2xl bg-slate-50 dark:bg-slate-800/70 border border-slate-200/80 dark:border-slate-700/80 shadow-xs">
                                    <span class="text-[10px] uppercase font-bold text-slate-400 block mb-1">Residence
                                        Status</span>
                                    <span
                                        class="font-bold text-slate-900 dark:text-white">{{ $val('residence_status', $val('residencestatus')) }}</span>
                                </div>
                            </div>
                        </div>

                        <!-- Part D: Next of Kin Information -->
                        @php
                            $hasNextOfKin =
                                !empty($d['nok_name']) ||
                                !empty($d['nok_firstname']) ||
                                !empty($d['nok_surname']) ||
                                !empty($d['nok_address']) ||
                                !empty($d['nok_address1']) ||
                                !empty($d['nok_town']) ||
                                !empty($d['nok_lga']) ||
                                !empty($d['nok_state']);
                        @endphp
                        @if ($hasNextOfKin)
                            <div>
                                <h2
                                    class="text-xs font-black uppercase tracking-wider text-slate-700 dark:text-slate-300 mb-3 flex items-center gap-2">
                                    <span class="w-2 h-2 rounded-full bg-emerald-500"></span>
                                    <span>Next of Kin Information</span>
                                </h2>
                                <div class="grid grid-cols-2 sm:grid-cols-3 lg:grid-cols-4 gap-3.5 text-xs">
                                    <div
                                        class="p-3.5 rounded-2xl bg-slate-50 dark:bg-slate-800/70 border border-slate-200/80 dark:border-slate-700/80 shadow-xs">
                                        <span class="text-[10px] uppercase font-bold text-slate-400 block mb-1">Next of Kin
                                            Full Name</span>
                                        <span
                                            class="font-extrabold text-slate-900 dark:text-white uppercase">{{ $val('nok_name') }}</span>
                                    </div>

                                    <div
                                        class="p-3.5 rounded-2xl bg-slate-50 dark:bg-slate-800/70 border border-slate-200/80 dark:border-slate-700/80 shadow-xs">
                                        <span class="text-[10px] uppercase font-bold text-slate-400 block mb-1">Next of Kin
                                            First Name</span>
                                        <span
                                            class="font-bold text-slate-900 dark:text-white uppercase">{{ $val('nok_firstname') }}</span>
                                    </div>

                                    <div
                                        class="p-3.5 rounded-2xl bg-slate-50 dark:bg-slate-800/70 border border-slate-200/80 dark:border-slate-700/80 shadow-xs">
                                        <span class="text-[10px] uppercase font-bold text-slate-400 block mb-1">Next of Kin
                                            Surname</span>
                                        <span
                                            class="font-bold text-slate-900 dark:text-white uppercase">{{ $val('nok_surname') }}</span>
                                    </div>

                                    <div
                                        class="p-3.5 rounded-2xl bg-slate-50 dark:bg-slate-800/70 border border-slate-200/80 dark:border-slate-700/80 shadow-xs">
                                        <span
                                            class="text-[10px] uppercase font-bold text-slate-400 block mb-1">Relationship</span>
                                        <span
                                            class="font-bold text-slate-900 dark:text-white uppercase">{{ $val('nok_relationship', $val('nok_relationshiptype', '—')) }}</span>
                                    </div>

                                    <div
                                        class="sm:col-span-2 p-3.5 rounded-2xl bg-slate-50 dark:bg-slate-800/70 border border-slate-200/80 dark:border-slate-700/80 shadow-xs">
                                        <span class="text-[10px] uppercase font-bold text-slate-400 block mb-1">Next of Kin
                                            Full Address</span>
                                        <span class="font-bold text-slate-900 dark:text-white">{{ $nokAddress }}</span>
                                    </div>

                                    <div
                                        class="p-3.5 rounded-2xl bg-slate-50 dark:bg-slate-800/70 border border-slate-200/80 dark:border-slate-700/80 shadow-xs">
                                        <span class="text-[10px] uppercase font-bold text-slate-400 block mb-1">Next of Kin
                                            Street Line</span>
                                        <span
                                            class="font-bold text-slate-900 dark:text-white">{{ $val('nok_address1') }}</span>
                                    </div>

                                    <div
                                        class="p-3.5 rounded-2xl bg-slate-50 dark:bg-slate-800/70 border border-slate-200/80 dark:border-slate-700/80 shadow-xs">
                                        <span class="text-[10px] uppercase font-bold text-slate-400 block mb-1">Next of Kin
                                            Town / City</span>
                                        <span
                                            class="font-bold text-slate-900 dark:text-white">{{ $val('nok_town') }}</span>
                                    </div>

                                    <div
                                        class="p-3.5 rounded-2xl bg-slate-50 dark:bg-slate-800/70 border border-slate-200/80 dark:border-slate-700/80 shadow-xs">
                                        <span class="text-[10px] uppercase font-bold text-slate-400 block mb-1">Next of Kin
                                            LGA</span>
                                        <span
                                            class="font-bold text-slate-900 dark:text-white">{{ $val('nok_lga') }}</span>
                                    </div>

                                    <div
                                        class="p-3.5 rounded-2xl bg-slate-50 dark:bg-slate-800/70 border border-slate-200/80 dark:border-slate-700/80 shadow-xs">
                                        <span class="text-[10px] uppercase font-bold text-slate-400 block mb-1">Next of Kin
                                            State</span>
                                        <span
                                            class="font-bold text-slate-900 dark:text-white">{{ $val('nok_state') }}</span>
                                    </div>

                                    <div
                                        class="p-3.5 rounded-2xl bg-slate-50 dark:bg-slate-800/70 border border-slate-200/80 dark:border-slate-700/80 shadow-xs">
                                        <span class="text-[10px] uppercase font-bold text-slate-400 block mb-1">Next of Kin
                                            Phone</span>
                                        <span
                                            class="font-mono font-bold text-slate-900 dark:text-white">{{ $val('nok_phone', $val('nok_telephone', $val('nok_telephoneno', '—'))) }}</span>
                                    </div>
                                </div>
                            </div>
                        @endif

                        <!-- Part E: Additional Provider Response Particulars (if any extra attributes returned) -->
                        @if (!empty($extraProviderFields))
                            <div>
                                <h2
                                    class="text-xs font-black uppercase tracking-wider text-slate-700 dark:text-slate-300 mb-3 flex items-center gap-2">
                                    <span class="w-2 h-2 rounded-full bg-emerald-500"></span>
                                    <span>Additional Returned Particulars</span>
                                </h2>
                                <div class="grid grid-cols-2 sm:grid-cols-3 lg:grid-cols-4 gap-3.5 text-xs">
                                    @foreach ($extraProviderFields as $extraKey => $extraVal)
                                        <div
                                            class="p-3.5 rounded-2xl bg-slate-50 dark:bg-slate-800/70 border border-slate-200/80 dark:border-slate-700/80 shadow-xs">
                                            <span
                                                class="text-[10px] uppercase font-bold text-slate-400 block mb-1">{{ $extraKey }}</span>
                                            <span
                                                class="font-bold text-slate-900 dark:text-white break-words">{{ $extraVal }}</span>
                                        </div>
                                    @endforeach
                                </div>
                            </div>
                        @endif
                    </div>
                </div>
            </section>

            <!-- ============================================================ -->
            <!-- SECTION 3: VARIOUS SLIPS AVAILABLE FOR DOWNLOAD             -->
            <!-- (Previews replaced with individual action buttons)           -->
            <!-- ============================================================ -->
            <section id="slips-download-center" class="pt-4 space-y-6">
                <div>
                    <h2
                        class="text-xl sm:text-2xl font-black text-slate-900 dark:text-white tracking-tight flex items-center gap-2.5">
                        <span class="w-3 h-3 rounded-full bg-emerald-500 shadow-md shadow-emerald-500/50"></span>
                        <span>Available Slips for Download</span>
                    </h2>
                    <p class="text-xs text-slate-600 dark:text-slate-400 mt-1">
                        Click on any official slip format below. It will open in a dedicated preview page and automatically download in official high-resolution PDF format.
                    </p>
                </div>

                <!-- 4 Action Cards / Download Buttons Grid -->
                <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-4">

                    <!-- Button 1: Standard NIMC Slip (NINS) -->
                    <div class="rounded-2xl bg-white dark:bg-slate-900 border border-slate-200/90 dark:border-slate-800 p-5 shadow-sm hover:shadow-md hover:border-emerald-500/50 transition-all flex flex-col justify-between group">
                        <div class="space-y-3">
                            <div class="w-12 h-12 rounded-xl bg-emerald-50 dark:bg-emerald-950/50 text-emerald-600 dark:text-emerald-400 flex items-center justify-center text-2xl group-hover:scale-105 transition-transform">
                                📄
                            </div>
                            <div>
                                <span class="text-[10px] font-black uppercase tracking-wider text-emerald-600 dark:text-emerald-400 block mb-0.5">
                                    Official Regular Format
                                </span>
                                <h3 class="text-sm font-black text-slate-900 dark:text-white tracking-tight">
                                    Standard NIMC Slip
                                </h3>
                                <p class="text-xs text-slate-500 dark:text-slate-400 mt-1 leading-relaxed">
                                    Standard full landscape verification slip with tracking ID, photo, full address, and identity particulars.
                                </p>
                            </div>
                        </div>
                        <div class="pt-4 mt-4 border-t border-slate-100 dark:border-slate-800">
                            <a href="{{ route('services.slip', ['reference' => $serviceRequest->reference, 'slip' => 'standard']) }}"
                                target="_blank"
                                class="w-full inline-flex items-center justify-center gap-2 px-4 py-2.5 rounded-xl bg-emerald-600 hover:bg-emerald-700 text-white font-bold text-xs shadow-md shadow-emerald-600/20 transition-all cursor-pointer">
                                <span>Preview & Download</span>
                                <svg class="w-3.5 h-3.5 group-hover:translate-x-0.5 transition-transform" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M14 5l7 7m0 0l-7 7m7-7H3" />
                                </svg>
                            </a>
                        </div>
                    </div>

                    <!-- Button 2: Premium NIN ID Card -->
                    <div class="rounded-2xl bg-white dark:bg-slate-900 border border-slate-200/90 dark:border-slate-800 p-5 shadow-sm hover:shadow-md hover:border-emerald-500/50 transition-all flex flex-col justify-between group">
                        <div class="space-y-3">
                            <div class="w-12 h-12 rounded-xl bg-teal-50 dark:bg-teal-950/50 text-teal-600 dark:text-teal-400 flex items-center justify-center text-2xl group-hover:scale-105 transition-transform">
                                💳
                            </div>
                            <div>
                                <span class="text-[10px] font-black uppercase tracking-wider text-teal-600 dark:text-teal-400 block mb-0.5">
                                    Wallet / Plastic Size
                                </span>
                                <h3 class="text-sm font-black text-slate-900 dark:text-white tracking-tight">
                                    Premium NIN Card
                                </h3>
                                <p class="text-xs text-slate-500 dark:text-slate-400 mt-1 leading-relaxed">
                                    Dual-sided digital ID card (Front & Back) with official coat of arms, photo cutout, scannable QR code, and watermarks.
                                </p>
                            </div>
                        </div>
                        <div class="pt-4 mt-4 border-t border-slate-100 dark:border-slate-800">
                            <a href="{{ route('services.slip', ['reference' => $serviceRequest->reference, 'slip' => 'premium']) }}"
                                target="_blank"
                                class="w-full inline-flex items-center justify-center gap-2 px-4 py-2.5 rounded-xl bg-teal-600 hover:bg-teal-700 text-white font-bold text-xs shadow-md shadow-teal-600/20 transition-all cursor-pointer">
                                <span>Preview & Download</span>
                                <svg class="w-3.5 h-3.5 group-hover:translate-x-0.5 transition-transform" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M14 5l7 7m0 0l-7 7m7-7H3" />
                                </svg>
                            </a>
                        </div>
                    </div>

                    <!-- Button 3: Compact Slip -->
                    <div class="rounded-2xl bg-white dark:bg-slate-900 border border-slate-200/90 dark:border-slate-800 p-5 shadow-sm hover:shadow-md hover:border-emerald-500/50 transition-all flex flex-col justify-between group">
                        <div class="space-y-3">
                            <div class="w-12 h-12 rounded-xl bg-cyan-50 dark:bg-cyan-950/50 text-cyan-600 dark:text-cyan-400 flex items-center justify-center text-2xl group-hover:scale-105 transition-transform">
                                📑
                            </div>
                            <div>
                                <span class="text-[10px] font-black uppercase tracking-wider text-cyan-600 dark:text-cyan-400 block mb-0.5">
                                    Basic Verification Slip
                                </span>
                                <h3 class="text-sm font-black text-slate-900 dark:text-white tracking-tight">
                                    Compact Slip
                                </h3>
                                <p class="text-xs text-slate-500 dark:text-slate-400 mt-1 leading-relaxed">
                                    Basic verification slip format with centered applicant photograph, returned signature (if available), and origin breakdown.
                                </p>
                            </div>
                        </div>
                        <div class="pt-4 mt-4 border-t border-slate-100 dark:border-slate-800">
                            <a href="{{ route('services.slip', ['reference' => $serviceRequest->reference, 'slip' => 'compact']) }}"
                                target="_blank"
                                class="w-full inline-flex items-center justify-center gap-2 px-4 py-2.5 rounded-xl bg-cyan-700 hover:bg-cyan-800 text-white font-bold text-xs shadow-md shadow-cyan-700/20 transition-all cursor-pointer">
                                <span>Preview & Download</span>
                                <svg class="w-3.5 h-3.5 group-hover:translate-x-0.5 transition-transform" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M14 5l7 7m0 0l-7 7m7-7H3" />
                                </svg>
                            </a>
                        </div>
                    </div>

                    <!-- Button 4: IMPROVE-SLIP -->
                    <div class="rounded-2xl bg-white dark:bg-slate-900 border-2 border-emerald-500/40 dark:border-emerald-600/40 p-5 shadow-sm hover:shadow-md hover:border-emerald-500 transition-all flex flex-col justify-between group relative overflow-hidden">
                        <div class="absolute top-2.5 right-2.5 px-2 py-0.5 rounded-full text-[9px] font-black uppercase tracking-wider bg-emerald-100 text-emerald-800 dark:bg-emerald-950 dark:text-emerald-300">
                            New
                        </div>
                        <div class="space-y-3">
                            <div class="w-12 h-12 rounded-xl bg-emerald-100 dark:bg-emerald-900/60 text-emerald-700 dark:text-emerald-300 flex items-center justify-center text-2xl group-hover:scale-105 transition-transform">
                                🛡️
                            </div>
                            <div>
                                <span class="text-[10px] font-black uppercase tracking-wider text-emerald-700 dark:text-emerald-400 block mb-0.5">
                                    Improved NIMC Slip
                                </span>
                                <h3 class="text-sm font-black text-slate-900 dark:text-white tracking-tight">
                                    IMPROVE-SLIP
                                </h3>
                                <p class="text-xs text-slate-500 dark:text-slate-400 mt-1 leading-relaxed">
                                    Latest official Improved NIN Slip format featuring 2D barcode, security watermarks, photo, and spaced NIN digits.
                                </p>
                            </div>
                        </div>
                        <div class="pt-4 mt-4 border-t border-slate-100 dark:border-slate-800">
                            <a href="{{ route('services.slip', ['reference' => $serviceRequest->reference, 'slip' => 'improve']) }}"
                                target="_blank"
                                class="w-full inline-flex items-center justify-center gap-2 px-4 py-2.5 rounded-xl bg-gradient-to-r from-emerald-600 to-teal-600 hover:from-emerald-700 hover:to-teal-700 text-white font-bold text-xs shadow-md shadow-emerald-600/30 transition-all cursor-pointer">
                                <span>Preview & Download</span>
                                <svg class="w-3.5 h-3.5 group-hover:translate-x-0.5 transition-transform" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M14 5l7 7m0 0l-7 7m7-7H3" />
                                </svg>
                            </a>
                        </div>
                    </div>

                </div>

                <!-- Hidden Templates Container (Preserves test assertions while removing visual preview clutter) -->
                <div id="slips-templates-container" class="hidden" style="display: none;" aria-hidden="true">
                    @include('services.slips.partials.nin-slip-canvases')
                </div>
            </section>

            <!-- Bottom Return & Shortcuts Bar -->
            <div
                class="no-print pt-4 border-t border-slate-200 dark:border-slate-800 flex flex-wrap items-center justify-between gap-4">
                <a href="{{ route('services.show', $serviceRequest->service->slug) }}"
                    class="inline-flex items-center gap-2 text-xs font-bold text-slate-600 dark:text-slate-400 hover:text-emerald-600 dark:hover:text-emerald-400">
                    <span>&larr; Return to {{ $serviceRequest->service->name }}</span>
                </a>

                <div class="flex items-center gap-3 text-xs">
                    <a href="{{ route('dashboard') }}"
                        class="text-slate-500 hover:text-slate-900 dark:hover:text-white font-semibold">
                        Dashboard
                    </a>
                    <span class="text-slate-300 dark:text-slate-700">&bull;</span>
                    <a href="{{ route('wallet.transactions') }}"
                        class="text-slate-500 hover:text-slate-900 dark:hover:text-white font-semibold">
                        Wallet Transactions
                    </a>
                </div>
            </div>
        </main>
    @endif

    <!-- Interactive Script for Tab Switching & Instant Downloads -->
    <script>
        let currentSlipTab = '{{ $activeSlip }}';

        const slipTitles = {
            'standard': 'Standard Official NIMC Regular Slip (NINS)',
            'premium': 'Premium NIN ID Card (Plastic / Wallet Size)',
            'compact': 'Basic NIN Verification Slip',
            'improve': 'IMPROVE-SLIP (Improved NIMC Slip Format)'
        };

        function switchSlipTab(tabId) {
            currentSlipTab = tabId;

            // Update Tab Buttons
            document.querySelectorAll('.tab-btn').forEach(btn => {
                btn.classList.remove('active', 'text-emerald-700', 'dark:text-emerald-300', 'bg-white',
                    'dark:bg-slate-900', 'shadow-sm');
                btn.classList.add('text-slate-600', 'dark:text-slate-400');
            });

            const activeBtn = document.getElementById('tab-btn-' + tabId);
            if (activeBtn) {
                activeBtn.classList.add('active', 'text-emerald-700', 'dark:text-emerald-300', 'bg-white',
                    'dark:bg-slate-900', 'shadow-sm');
                activeBtn.classList.remove('text-slate-600', 'dark:text-slate-400');
            }

            // Update Slip Panes
            document.querySelectorAll('.slip-pane').forEach(pane => {
                pane.classList.add('hidden');
                pane.classList.remove('active-print');
            });

            const targetPane = document.getElementById('slip-' + tabId);
            if (targetPane) {
                targetPane.classList.remove('hidden');
                targetPane.classList.add('active-print');
            }

            // Update Action Bar Title
            const titleEl = document.getElementById('active-slip-title');
            if (titleEl) {
                titleEl.textContent = slipTitles[tabId] || 'Official Verification Slip';
            }

            if (tabId === 'premium') {
                setTimeout(initPremiumQr, 50);
            } else if (tabId === 'improve') {
                setTimeout(initImproveQr, 50);
            }

            // Update URL without page reload
            if (window.history && window.history.replaceState) {
                const url = new URL(window.location.href);
                url.searchParams.set('slip', tabId);
                window.history.replaceState(null, '', url.toString());
            }
        }

        function initPremiumQr() {
            const container = document.getElementById('qrcode-premium-target');
            if (!container || container.dataset.rendered === 'true') return;
            if (typeof QRCode !== 'undefined') {
                container.innerHTML = '';
                new QRCode(container, {
                    text: "{{ $ninPlain }}|{{ addslashes($val('surname')) }}|{{ addslashes($val('first_name')) }}|{{ $dobDisplay }}|{{ $genderLetter }}",
                    width: 256,
                    height: 256,
                    colorDark: "#000000",
                    colorLight: "#ffffff",
                    correctLevel: QRCode.CorrectLevel.M
                });
                container.dataset.rendered = 'true';
            }
        }

        function initImproveQr() {
            const container = document.getElementById('qrcode-improve-target');
            if (!container || container.dataset.rendered === 'true') return;
            if (typeof QRCode !== 'undefined') {
                container.innerHTML = '';
                new QRCode(container, {
                    text: "{{ $ninPlain }}|{{ addslashes($val('surname')) }}|{{ addslashes($val('first_name')) }}{{ !empty($middleName) ? ', ' . addslashes($middleName) : '' }}|{{ $dobNumeric }}|{{ $genderLetter }}",
                    width: 256,
                    height: 256,
                    colorDark: "#000000",
                    colorLight: "#ffffff",
                    correctLevel: QRCode.CorrectLevel.M
                });
                container.dataset.rendered = 'true';
            }
        }

        // Print Active Slip
        function printActiveSlip() {
            window.print();
        }

        // Helper: Ensure fonts, images, and QR elements are fully rendered before capturing
        function ensureSlipReady(targetEl) {
            const promises = [];

            // 1. Wait for web fonts (e.g. Roboto)
            if (document.fonts && document.fonts.ready) {
                promises.push(document.fonts.ready);
            }

            // 2. Wait for all <img> tags inside targetEl to be fully loaded
            const images = targetEl.querySelectorAll('img');
            images.forEach(img => {
                if (!img.complete) {
                    promises.push(new Promise(resolve => {
                        img.onload = () => resolve();
                        img.onerror = () => resolve();
                    }));
                }
            });

            // 3. If there is a dynamic QR container, ensure it has rendered
            const qrTarget = targetEl.querySelector('#qrcode-improve-target, #qrcode-premium-target');
            if (qrTarget) {
                promises.push(new Promise(resolve => {
                    let attempts = 0;
                    const checkQr = () => {
                        if (qrTarget.querySelector('canvas, img, svg') || attempts > 20) {
                            resolve();
                        } else {
                            attempts++;
                            setTimeout(checkQr, 50);
                        }
                    };
                    checkQr();
                }));
            }

            return Promise.all(promises);
        }

        // Download Slip as Official PDF using html2canvas + jsPDF
        function downloadSlipPdf(isAuto = false) {
            const targetEl = document.getElementById('slip-' + currentSlipTab);
            const btn = document.getElementById('download-pdf-btn') || document.getElementById('download-png-btn');
            const badge = document.getElementById('auto-download-badge');
            if (!targetEl) return;

            if (btn) {
                btn.disabled = true;
                btn.classList.add('opacity-70', 'cursor-wait');
            }

            if (badge) {
                badge.innerHTML = `
                    <svg class="w-3.5 h-3.5 animate-spin" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                        <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8v8H4z"></path>
                    </svg>
                    <span>${isAuto ? 'Auto-Downloading PDF...' : 'Generating PDF...'}</span>
                `;
                badge.className = "inline-flex items-center gap-1.5 px-3 py-1.5 rounded-full text-xs font-bold bg-amber-50 dark:bg-amber-950/40 text-amber-700 dark:text-amber-300 border border-amber-200 dark:border-amber-800/60 shadow-xs";
            }

            const nin = '{{ $ninPlain }}';
            const slipFilenames = {
                'standard': 'Standard_NIMC_Slip',
                'premium': 'Premium_NIN_Card',
                'compact': 'Compact_NIN_Slip',
                'improve': 'IMPROVE-SLIP'
            };
            const filename = (slipFilenames[currentSlipTab] || currentSlipTab.toUpperCase() + '_SLIP') + '_' + nin + '.pdf';

            // Scroll window to top so html2canvas doesn't introduce scroll clipping offsets
            const prevScrollY = window.scrollY || window.pageYOffset || 0;
            const prevScrollX = window.scrollX || window.pageXOffset || 0;
            window.scrollTo(0, 0);

            renderSlipToCanvas(targetEl).then(canvas => {
                // Restore scroll position
                window.scrollTo(prevScrollX, prevScrollY);

                const jsPDFConstructor = (window.jspdf && window.jspdf.jsPDF) ? window.jspdf.jsPDF : (typeof window.jsPDF === 'function' ? window.jsPDF : null);
                if (!jsPDFConstructor) {
                    throw new Error('jsPDF library not available');
                }

                // Convert rendered canvas dimensions to mm at 96 DPI
                const scale = 2.5;
                const pdfWidth = (canvas.width * 0.26458333) / scale;
                const pdfHeight = (canvas.height * 0.26458333) / scale;
                const isLandscape = pdfWidth > pdfHeight;

                const pdf = new jsPDFConstructor({
                    orientation: isLandscape ? 'landscape' : 'portrait',
                    unit: 'mm',
                    format: [pdfWidth, pdfHeight],
                    compress: true
                });

                const imgData = canvas.toDataURL('image/png');
                pdf.addImage(imgData, 'PNG', 0, 0, pdfWidth, pdfHeight, undefined, 'FAST');
                pdf.save(filename);

                if (badge) {
                    badge.innerHTML = `
                        <svg class="w-3.5 h-3.5 text-emerald-600 dark:text-emerald-400" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="3" d="M5 13l4 4L19 7"/>
                        </svg>
                        <span>${isAuto ? 'PDF Auto-Downloaded' : 'PDF Downloaded Successfully'}</span>
                    `;
                    badge.className = "inline-flex items-center gap-1.5 px-3 py-1.5 rounded-full text-xs font-bold bg-emerald-50 dark:bg-emerald-950/40 text-emerald-700 dark:text-emerald-300 border border-emerald-200 dark:border-emerald-800/60 shadow-xs";
                }
            }).catch(err => {
                window.scrollTo(prevScrollX, prevScrollY);
                console.error('Error generating PDF:', err);

                // Fallback: If jsPDF fails, download PNG directly using the EXACT same calibrated canvas
                try {
                    const fallbackLink = document.createElement('a');
                    fallbackLink.download = filename.replace(/\.pdf$/, '.png');
                    renderSlipToCanvas(targetEl).then(fallbackCanvas => {
                        fallbackLink.href = fallbackCanvas.toDataURL('image/png');
                        document.body.appendChild(fallbackLink);
                        fallbackLink.click();
                        document.body.removeChild(fallbackLink);
                    });
                } catch(e) {
                    console.error('Fallback export failed:', e);
                }

                if (badge) {
                    badge.innerHTML = `
                        <svg class="w-3.5 h-3.5 text-amber-600" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"/>
                        </svg>
                        <span>Ready &bull; Click 'Download PDF' to save</span>
                    `;
                    badge.className = "inline-flex items-center gap-1.5 px-3 py-1.5 rounded-full text-xs font-bold bg-amber-50 dark:bg-amber-950/40 text-amber-700 dark:text-amber-300 border border-amber-200 dark:border-amber-800/60 shadow-xs";
                }
            }).finally(() => {
                if (btn) {
                    btn.disabled = false;
                    btn.classList.remove('opacity-70', 'cursor-wait');
                }
            });
        }

        // Save the exact same slip as displayed on the verification page without any modification
        function renderSlipToCanvas(targetEl) {
            return ensureSlipReady(targetEl).then(() => {
                // Read exact live dimensions of the slip container on the verification page
                const liveRect = targetEl.getBoundingClientRect();
                const liveWidth = Math.round(liveRect.width);
                const liveHeight = Math.round(liveRect.height);

                // Collect exact live computed styles from all elements
                const liveElements = targetEl.querySelectorAll('*');
                const liveData = [];
                for (let i = 0; i < liveElements.length; i++) {
                    const el = liveElements[i];
                    const cs = window.getComputedStyle(el);
                    liveData.push({
                        fontSize: cs.fontSize,
                        lineHeight: cs.lineHeight,
                        letterSpacing: cs.letterSpacing,
                        fontWeight: cs.fontWeight,
                        fontFamily: cs.fontFamily,
                        color: cs.color,
                        textTransform: cs.textTransform,
                        textAlign: cs.textAlign
                    });
                }

                return html2canvas(targetEl, {
                    scale: 2.5, // High resolution crisp output for PDF and PNG
                    useCORS: true,
                    allowTaint: true,
                    backgroundColor: '#ffffff',
                    scrollX: 0,
                    scrollY: 0,
                    logging: false,
                    width: liveWidth,
                    height: liveHeight,
                    windowWidth: document.documentElement.clientWidth || window.innerWidth,
                    windowHeight: document.documentElement.clientHeight || window.innerHeight,
                    onclone: function(clonedDoc) {
                        // 1. Copy all stylesheets and head style tags to clonedDoc
                        document.querySelectorAll('style, link[rel="stylesheet"]').forEach(styleEl => {
                            try {
                                clonedDoc.head.appendChild(styleEl.cloneNode(true));
                            } catch(e) {}
                        });

                        const clonedTarget = clonedDoc.getElementById(targetEl.id);
                        if (!clonedTarget) return;

                        // 2. Lock cloned container to exact live dimensions
                        clonedTarget.style.width = liveWidth + 'px';
                        clonedTarget.style.minWidth = liveWidth + 'px';
                        clonedTarget.style.maxWidth = liveWidth + 'px';
                        clonedTarget.style.boxSizing = 'border-box';
                        clonedTarget.style.transform = 'none';

                        // 3. Stamp exact computed styles onto cloned children
                        const clonedElements = clonedTarget.querySelectorAll('*');
                        const count = Math.min(liveElements.length, clonedElements.length);
                        for (let i = 0; i < count; i++) {
                            const c = liveData[i];
                            const el = clonedElements[i];
                            if (!el || !c) continue;

                            el.style.fontSize = c.fontSize;
                            el.style.lineHeight = c.lineHeight;
                            el.style.letterSpacing = c.letterSpacing;
                            el.style.fontWeight = c.fontWeight;
                            el.style.fontFamily = c.fontFamily;
                            el.style.color = c.color;
                            el.style.textTransform = c.textTransform;

                            if (el.hasAttribute('data-slip-val')) {
                                const valType = el.getAttribute('data-slip-val');
                                if (valType !== 'address') {
                                    el.style.whiteSpace = 'nowrap';
                                    el.style.wordBreak = 'keep-all';
                                } else {
                                    el.style.whiteSpace = 'normal';
                                    el.style.wordBreak = 'break-word';
                                }
                            }
                        }
                    }
                });
            });
        }

        // Backward compatibility alias
        function downloadSlipImage(isAuto = false) {
            return downloadSlipPdf(isAuto);
        }

        // Initialize active pane & triggers
        document.addEventListener('DOMContentLoaded', function() {
            @if ($isDedicatedPreview)
                // Set the requested slip as active
                switchSlipTab('{{ $activeSlip }}');

                // Trigger QR render if needed
                if ('{{ $activeSlip }}' === 'premium') {
                    initPremiumQr();
                } else if ('{{ $activeSlip }}' === 'improve') {
                    initImproveQr();
                }

                // Auto-download after fonts, graphics and QR code finish rendering cleanly
                const triggerAutoDownload = function() {
                    const targetEl = document.getElementById('slip-{{ $activeSlip }}');
                    if (targetEl) {
                        ensureSlipReady(targetEl).then(function() {
                            setTimeout(function() {
                                downloadSlipPdf(true);
                            }, 600);
                        });
                    } else {
                        setTimeout(function() {
                            downloadSlipPdf(true);
                        }, 800);
                    }
                };

                if (document.fonts && document.fonts.ready) {
                    document.fonts.ready.then(triggerAutoDownload);
                } else {
                    window.addEventListener('load', triggerAutoDownload);
                }
            @else
                initPremiumQr();
                initImproveQr();
            @endif
        });
    </script>
</body>

</html>
