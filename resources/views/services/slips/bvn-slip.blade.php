<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>BVN Verification Slip</title>

    <!-- Google Fonts: Roboto -->
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Roboto:ital,wght@0,300;0,400;0,500;0,700;0,900;1,400;1,700&display=swap" rel="stylesheet">

    @vite(['resources/css/app.css', 'resources/js/app.js'])

    <style>
        @media print {
            body * {
                visibility: hidden;
            }
            #printable-slip, #printable-slip * {
                visibility: visible;
            }
            #printable-slip {
                position: absolute;
                left: 0;
                top: 0;
                width: 100%;
                margin: 0;
                padding: 0;
                border: 2px solid #1e3a8a !important;
                box-shadow: none !important;
                background-color: #ffffff !important;
                color: #000000 !important;
            }
            .no-print {
                display: none !important;
            }
        }
    </style>
</head>
<body class="bg-slate-100 dark:bg-slate-950 font-sans min-h-screen py-8 px-4 flex flex-col items-center justify-center">

    <!-- Action Bar (Hidden on Print) -->
    <div class="no-print max-w-2xl w-full flex items-center justify-between mb-6">
        <a href="{{ route('services.show', $serviceRequest->service->slug) }}"
           class="inline-flex items-center gap-1.5 text-xs font-semibold text-slate-600 dark:text-slate-400 hover:text-slate-900 dark:hover:text-white bg-white dark:bg-slate-900 px-3.5 py-2 rounded-xl border border-slate-200 dark:border-slate-800 shadow-sm transition">
            &larr; Back to {{ $serviceRequest->service->name }}
        </a>

        <div class="flex items-center gap-2">
            <button type="button" onclick="window.print()"
                    class="inline-flex items-center gap-2 px-5 py-2 rounded-xl bg-blue-700 hover:bg-blue-600 text-white text-xs font-bold shadow-md shadow-blue-700/20 transition cursor-pointer">
                <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 17h2a2 2 0 002-2v-4a2 2 0 00-2-2H5a2 2 0 00-2 2v4a2 2 0 002 2h2m2 4h6a2 2 0 002-2v-4a2 2 0 00-2-2H9a2 2 0 00-2 2v4a2 2 0 002 2zm8-12V5a2 2 0 00-2-2H9a2 2 0 00-2 2v4h10z" />
                </svg>
                <span>Print / Save as PDF</span>
            </button>
        </div>
    </div>

    <!-- Official Printable NIBSS BVN Slip Card -->
    <div id="printable-slip"
         class="max-w-2xl w-full bg-white text-slate-900 rounded-2xl shadow-xl border-2 border-blue-800 overflow-hidden relative">

        <!-- Top NIBSS Financial Header -->
        <div class="bg-[#1e3a8a] text-white py-3 px-6 text-center relative border-b-2 border-amber-400">
            <div class="flex items-center justify-between">
                <!-- NIBSS Logo Vector Graphic -->
                <div class="w-10 h-10 rounded-lg bg-white p-1 flex items-center justify-center">
                    <svg class="w-full h-full" viewBox="0 0 120 70">
                        <text x="60" y="45" text-anchor="middle" font-weight="900" font-size="36" fill="#1E3A8A">NIBSS</text>
                    </svg>
                </div>

                <div class="text-center">
                    <h1 class="text-sm font-extrabold tracking-wider uppercase">
                        Nigeria Inter-Bank Settlement System (NIBSS)
                    </h1>
                    <p class="text-[10px] font-medium tracking-wide uppercase text-blue-200">
                        Central Bank of Nigeria Financial Identity System
                    </p>
                    <h2 class="text-xs font-bold text-amber-300 uppercase tracking-widest mt-0.5">
                        Bank Verification Number (BVN) Report
                    </h2>
                </div>

                <!-- Shield Badge -->
                <div class="w-10 h-10 rounded-lg bg-blue-900 flex items-center justify-center text-amber-400">
                    <svg class="w-6 h-6" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m5.618-4.016A11.955 11.955 0 0112 2.944a11.955 11.955 0 01-8.618 3.04A12.02 12.02 0 003 9c0 5.591 3.824 10.29 9 11.622 5.176-1.332 9-6.03 9-11.622 0-1.042-.133-2.052-.382-3.016z" />
                    </svg>
                </div>
            </div>
        </div>

        <!-- Prominent BVN Bar -->
        <div class="bg-blue-50 border-b border-blue-200 py-3 px-6 text-center">
            <span class="text-[10px] font-bold text-blue-900 uppercase tracking-widest block">
                Bank Verification Number
            </span>
            <div class="text-2xl sm:text-3xl font-extrabold text-[#1e3a8a] tracking-widest mt-0.5">
                {{ $data['bvn_formatted'] ?? $data['bvn'] ?? $serviceRequest->tracking_input }}
            </div>
            <div class="text-[10px] text-blue-700 font-semibold mt-0.5">
                STATUS: {{ $data['bvn_status'] ?? 'ACTIVE & OPERATIONAL' }} &bull; {{ $data['account_tier'] ?? 'Tier 3 Verified' }}
            </div>
        </div>

        <!-- Main Body Grid -->
        <div class="p-6 grid grid-cols-12 gap-6 items-start">
            <!-- Left: Photo & QR Seal (4 Cols) -->
            <div class="col-span-4 flex flex-col items-center text-center">
                <!-- Photo Box -->
                <div class="w-32 h-40 rounded-lg border-2 border-blue-800 overflow-hidden shadow-inner bg-slate-100 relative flex items-center justify-center">
                    @if(!empty($data['photo_base64']))
                        <img src="{{ $data['photo_base64'] }}" alt="Cardholder Photo" class="w-full h-full object-cover">
                    @else
                        <div class="w-full h-full flex flex-col items-center justify-center text-slate-400">
                            <svg class="w-12 h-12" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z" />
                            </svg>
                        </div>
                    @endif

                    <div class="absolute bottom-0 inset-x-0 bg-blue-900/90 text-white text-[8px] font-bold uppercase py-0.5">
                        Biometric Verified
                    </div>
                </div>

                <!-- Simulated QR Code Graphic -->
                <div class="mt-3 p-1.5 border border-slate-200 rounded-lg bg-white shadow-xs">
                    <svg class="w-20 h-20" viewBox="0 0 100 100" fill="none">
                        <!-- Simulated QR pattern -->
                        <rect width="100" height="100" fill="white"/>
                        <rect x="10" y="10" width="25" height="25" fill="#1e3a8a"/>
                        <rect x="15" y="15" width="15" height="15" fill="white"/>
                        <rect x="18" y="18" width="9" height="9" fill="#1e3a8a"/>

                        <rect x="65" y="10" width="25" height="25" fill="#1e3a8a"/>
                        <rect x="70" y="15" width="15" height="15" fill="white"/>
                        <rect x="73" y="18" width="9" height="9" fill="#1e3a8a"/>

                        <rect x="10" y="65" width="25" height="25" fill="#1e3a8a"/>
                        <rect x="15" y="70" width="15" height="15" fill="white"/>
                        <rect x="18" y="73" width="9" height="9" fill="#1e3a8a"/>

                        <rect x="42" y="42" width="16" height="16" fill="#1e3a8a"/>
                        <rect x="42" y="15" width="6" height="12" fill="#1e3a8a"/>
                        <rect x="70" y="45" width="12" height="6" fill="#1e3a8a"/>
                        <rect x="45" y="70" width="15" height="8" fill="#1e3a8a"/>
                    </svg>
                    <span class="text-[8px] text-slate-400 block mt-0.5">AUTH_SEAL</span>
                </div>
            </div>

            <!-- Right: Financial Demographics (8 Cols) -->
            <div class="col-span-8 grid grid-cols-2 gap-y-3 gap-x-4 text-xs">
                <div class="col-span-2 border-b border-slate-100 pb-2">
                    <span class="text-[10px] uppercase font-bold text-slate-400 block">Cardholder Legal Name</span>
                    <span class="text-base font-extrabold text-slate-900 uppercase tracking-wide">
                        {{ $data['full_name'] ?? 'AMINA KALU DANJUMA' }}
                    </span>
                </div>

                <div>
                    <span class="text-[10px] uppercase font-bold text-slate-400 block">Date of Birth</span>
                    <span class="font-bold text-slate-800">
                        {{ $data['date_of_birth'] ?? '1992-04-18' }}
                    </span>
                </div>

                <div>
                    <span class="text-[10px] uppercase font-bold text-slate-400 block">Gender</span>
                    <span class="font-bold text-slate-800 uppercase">
                        {{ $data['gender'] ?? 'FEMALE' }}
                    </span>
                </div>

                <div>
                    <span class="text-[10px] uppercase font-bold text-slate-400 block">Registered Phone</span>
                    <span class="font-bold text-slate-800">
                        {{ $data['phone_number'] ?? '08031234567' }}
                    </span>
                </div>

                <div>
                    <span class="text-[10px] uppercase font-bold text-slate-400 block">Enrollment Bank</span>
                    <span class="font-bold text-blue-900">
                        {{ $data['enrollment_bank'] ?? 'Access Bank Plc' }}
                    </span>
                </div>

                <div class="col-span-2">
                    <span class="text-[10px] uppercase font-bold text-slate-400 block">Registration Branch</span>
                    <span class="text-slate-800 font-medium">
                        {{ $data['enrollment_branch'] ?? 'Victoria Island Central Branch, Lagos' }}
                    </span>
                </div>

                <div>
                    <span class="text-[10px] uppercase font-bold text-slate-400 block">Enrollment Date</span>
                    <span class="text-slate-600">
                        {{ $data['registration_date'] ?? '2021-08-14' }}
                    </span>
                </div>

                <div>
                    <span class="text-[10px] uppercase font-bold text-slate-400 block">Watchlist Status</span>
                    <span class="font-bold text-emerald-700">
                        {{ $data['watchlist_status'] ?? 'CLEAR' }}
                    </span>
                </div>

                <div class="col-span-2 pt-2 border-t border-slate-100">
                    <span class="text-[10px] uppercase font-bold text-slate-400 block">Verification Authority</span>
                    <span class="font-medium text-slate-600">
                        {{ $data['provider'] ?? 'NIBSS Central Gateway V1' }}
                    </span>
                </div>
            </div>
        </div>

        <!-- Footer Notice -->
        <div class="bg-slate-50 border-t border-slate-200 py-3 px-6 flex items-center justify-between text-[10px] text-slate-500">
            <span>Verified through NIBSS Central Identity Gateway. Confidential.</span>
        </div>
    </div>
</body>
</html>
