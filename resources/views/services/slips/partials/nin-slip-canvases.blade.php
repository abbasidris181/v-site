{{-- ===================================================================== --}}
{{-- NIN SLIP CANVASES PARTIAL (Standard, Premium, Compact, IMPROVE-SLIP)   --}}
{{-- Reusable across Main Summary Page (hidden for tests) & Dedicated Pages --}}
{{-- ===================================================================== --}}

<!-- ===================================================================== -->
<!-- SLIP 1: STANDARD OFFICIAL NIMC REGULAR SLIP (NINS)                    -->
<!-- ===================================================================== -->
<div id="slip-standard"
    class="slip-pane active-print w-full max-w-4xl bg-white text-slate-900 rounded-none shadow-2xl overflow-hidden relative print-target select-none mx-auto"
    style="aspect-ratio: 2924 / 1437; min-width: 600px; container-type: inline-size;">

    @php
        $regularBgPath = public_path('images/card_and_Slip/regular.png');
        $regularBgSrc = file_exists($regularBgPath) ? 'data:image/png;base64,' . base64_encode(file_get_contents($regularBgPath)) : asset('images/card_and_Slip/regular.png');
    @endphp

    <!-- Official Regular NINS Template Background Image -->
    <img src="{{ $regularBgSrc }}"
        alt="National Identification Number Slip (NINS)"
        class="absolute inset-0 w-full h-full object-fill pointer-events-none select-none z-0">

    <!-- Overlay Identity Data on Top of Template -->
    <div class="absolute inset-0 z-10 font-sans text-slate-900 pointer-events-auto">
        <!-- Tracking ID Value -->
        <div data-slip-val="tracking-id" class="absolute flex items-center font-bold text-black uppercase tracking-normal select-text"
            style="left: 11.8%; top: 26.85%; width: auto; height: 5.2%; font-size: clamp(12px, 1.5cqw, 16px); line-height: 1; overflow: visible; white-space: nowrap !important; word-break: keep-all !important;">
            {{ $trackingId }}
        </div>

        <!-- NIN Value -->
        <div data-slip-val="nin" class="absolute flex items-center font-bold text-black tracking-normal select-text"
            style="left: 11.8%; top: 36.66%; width: auto; height: 5.2%; font-size: clamp(13px, 1.6cqw, 18px); line-height: 1; overflow: visible; white-space: nowrap !important; word-break: keep-all !important;">
            {{ $ninPlain }}
        </div>

        <!-- Surname Value -->
        <div data-slip-val="surname" class="absolute flex items-center font-bold text-black uppercase tracking-normal select-text"
            style="left: 38.2%; top: 26.85%; width: auto; height: 5.2%; font-size: clamp(12px, 1.5cqw, 16px); line-height: 1; overflow: visible; white-space: nowrap !important; word-break: keep-all !important;">
            {{ $val('surname') }}
        </div>

        <!-- First Name Value -->
        <div data-slip-val="first-name" class="absolute flex items-center font-bold text-black uppercase tracking-normal select-text"
            style="left: 38.2%; top: 36.66%; width: auto; height: 5.2%; font-size: clamp(12px, 1.5cqw, 16px); line-height: 1; overflow: visible; white-space: nowrap !important; word-break: keep-all !important;">
            {{ $val('first_name') }}
        </div>

        <!-- Middle Name Value -->
        <div data-slip-val="middle-name" class="absolute flex items-center font-bold text-black uppercase tracking-normal select-text"
            style="left: 38.2%; top: 46.73%; width: auto; height: 5.2%; font-size: clamp(12px, 1.5cqw, 16px); line-height: 1; overflow: visible; white-space: nowrap !important; word-break: keep-all !important;">
            {{ $middleName }}
        </div>

        <!-- Gender Value -->
        <div data-slip-val="gender" class="absolute flex items-center font-bold text-black uppercase tracking-normal select-text"
            style="left: 38.2%; top: 56.76%; width: auto; height: 5.2%; font-size: clamp(12px, 1.5cqw, 16px); line-height: 1; overflow: visible; white-space: nowrap !important; word-break: keep-all !important;">
            {{ $genderLetter }}
        </div>

        <!-- Residential Street Address Value -->
        <div data-slip-val="address" class="absolute block pr-1 select-text font-bold text-slate-900 uppercase whitespace-normal break-words"
            style="left: 57.5%; top: 28.5%; width: 25.8%; font-size: clamp(10px, 1.2cqw, 13px); line-height: 1.25; overflow: visible;">
            {{ $residenceStreet }}
        </div>

        <!-- Residence LGA / Town (Moved down to Row 3 level) -->
        @if (!empty($residenceTown))
            <div data-slip-val="residence-lga" class="absolute flex items-center pr-2 select-text font-bold text-slate-900 uppercase whitespace-nowrap"
                style="left: 57.5%; top: 46.73%; width: 25.5%; height: 5.2%; font-size: clamp(10.5px, 1.3cqw, 14px); line-height: 1; overflow: visible;">
                {{ $residenceTown }}
            </div>
        @endif

        <!-- Residence State (Moved down to Row 4 level) -->
        @if (!empty($residenceState))
            <div data-slip-val="residence-state" class="absolute flex items-center pr-2 select-text font-bold text-slate-900 uppercase whitespace-nowrap"
                style="left: 57.5%; top: 56.76%; width: 25.5%; height: 5.2%; font-size: clamp(10.5px, 1.3cqw, 14px); line-height: 1; overflow: visible;">
                {{ $residenceState }}
            </div>
        @endif

        <!-- Applicant Photograph (Far Right Cell) -->
        <div class="absolute overflow-hidden flex items-center justify-center bg-white"
            style="left: 83.6%; top: 23.94%; width: 16.0%; height: 39.60%;">
            @if (!empty($resolvedPhoto))
                <img src="{{ $resolvedPhoto }}" alt="Applicant Photograph"
                    class="w-full h-full object-cover object-center select-none pointer-events-none">
            @else
                <div class="w-full h-full flex flex-col items-center justify-center bg-slate-100 text-slate-400">
                    <svg class="w-10 h-10 text-slate-300" fill="currentColor" viewBox="0 0 24 24">
                        <path d="M12 12c2.21 0 4-1.79 4-4s-1.79-4-4-4-4 1.79-4 4 1.79 4 4 4zm0 2c-2.67 0-8 1.34-8 4v2h16v-2c0-2.66-5.33-4-8-4z" />
                    </svg>
                </div>
            @endif
        </div>
    </div>
</div>

<!-- ===================================================================== -->
<!-- SLIP 2: PREMIUM NIN ID CARD (WALLET / PLASTIC FORMAT)                 -->
<!-- ===================================================================== -->
<div id="slip-premium"
    class="slip-pane hidden w-full max-w-[560px] bg-white text-slate-900 rounded-none shadow-2xl p-6 sm:p-8 flex flex-col items-center select-none print-target mx-auto">

    <!-- Sheet Header: Official Centered NIMC Logo & Title -->
    <div class="flex flex-col items-center text-center mb-5 w-full">
        <img src="{{ asset('images/services/nimc logo.png') }}" alt="National Identity Management Commission"
            class="w-16 h-16 sm:w-20 sm:h-20 object-contain pointer-events-none select-none mb-1">
        <h2 class="text-sm sm:text-base md:text-lg font-black text-slate-900 tracking-tight text-center">
            National Identity Management Commission (NIMC)
        </h2>
        <p class="text-[11px] font-bold text-slate-500 uppercase tracking-widest">
            Federal Republic of Nigeria &bull; Digital NIN Card
        </p>
    </div>

    @php
        $premiumBgPath = public_path('images/card_and_Slip/premium.jpg');
        $premiumBgSrc = file_exists($premiumBgPath) ? 'data:image/jpeg;base64,' . base64_encode(file_get_contents($premiumBgPath)) : asset('images/card_and_Slip/premium.jpg');
        $backBgPath = public_path('images/card_and_Slip/back.png');
        $backBgSrc = file_exists($backBgPath) ? 'data:image/png;base64,' . base64_encode(file_get_contents($backBgPath)) : asset('images/card_and_Slip/back.png');
    @endphp

    <!-- Front Card Container (1374 x 872) -->
    <div class="relative w-full rounded-none overflow-hidden shadow-md border border-slate-300 select-none"
        style="aspect-ratio: 1374 / 872; container-type: inline-size;">

        <!-- Official Front Template Background Image -->
        <img src="{{ $premiumBgSrc }}"
            data-original-src="{{ asset('images/card_and_Slip/premium.jpg') }}"
            alt="Digital NIN Card Front" crossorigin="anonymous"
            class="absolute inset-0 w-full h-full object-fill pointer-events-none select-none z-0">

        <!-- Overlay Identity Data on Top of Front Template -->
        <div class="absolute inset-0 z-10 font-sans text-slate-900 pointer-events-auto">

            <!-- Applicant Photograph (Left Cutout) -->
            <div class="absolute overflow-hidden flex items-center justify-center bg-slate-100 rounded-none select-none"
                style="left: 2.04%; top: 25.69%; width: 24.15%; height: 43.92%;">
                @if (!empty($resolvedPhoto))
                    <img src="{{ $resolvedPhoto }}" alt="Applicant Photograph"
                        class="w-full h-full object-cover object-center select-none pointer-events-none">
                @else
                    <div class="w-full h-full flex flex-col items-center justify-center bg-slate-100 text-slate-400">
                        <svg class="w-10 h-10 text-slate-300" fill="currentColor" viewBox="0 0 24 24">
                            <path d="M12 12c2.21 0 4-1.79 4-4s-1.79-4-4-4-4 1.79-4 4 1.79 4 4 4zm0 2c-2.67 0-8 1.34-8 4v2h16v-2c0-2.66-5.33-4-8-4z" />
                        </svg>
                    </div>
                @endif

                <!-- Watermark 1: Diagonal Ghost NIN across bottom-right of photo -->
                <div class="absolute inset-0 pointer-events-none overflow-hidden select-none flex items-end justify-end p-0.5">
                    <div class="font-sans font-normal select-none whitespace-nowrap rotate-[-50deg] origin-bottom-right mb-2 mr-[-4%]"
                        style="font-family: 'Helvetica Neue', Helvetica, Arial, sans-serif; color: rgb(220, 220, 220); font-size: clamp(8px, 1.25cqw, 14px);">
                        {{ $ninClean }}
                    </div>
                </div>
            </div>

            <!-- Watermark 2: Diagonal Ghost NIN in bottom-left corner below photo -->
            <div class="absolute pointer-events-none select-none overflow-hidden"
                style="left: 3.0%; top: 72.0%; width: 23.0%; height: 15.0%;">
                <div class="font-sans font-normal select-none whitespace-nowrap rotate-[-50deg] origin-center mt-3"
                    style="font-family: 'Helvetica Neue', Helvetica, Arial, sans-serif; color: rgb(220, 220, 220); font-size: clamp(8px, 1.25cqw, 14px); text-shadow: 0 0 1px rgba(50, 90, 50, 0.4);">
                    {{ $ninClean }}
                </div>
            </div>

            <!-- QR Code (Top-Right Cutout) -->
            <div class="absolute overflow-hidden bg-white select-none flex items-center justify-center p-0.5"
                style="left: 72.15%; top: 7.00%; width: 24.73%; height: 38.88%;">
                <div id="qrcode-premium-target" class="w-full h-full flex items-center justify-center">
                    <svg viewBox="0 0 33 33" class="w-full h-full" shape-rendering="crispEdges">
                        <rect width="33" height="33" fill="#ffffff" />
                        <rect x="0" y="0" width="7" height="7" fill="#000000" />
                        <rect x="1" y="1" width="5" height="5" fill="#ffffff" />
                        <rect x="2" y="2" width="3" height="3" fill="#000000" />
                        <rect x="26" y="0" width="7" height="7" fill="#000000" />
                        <rect x="27" y="1" width="5" height="5" fill="#ffffff" />
                        <rect x="28" y="2" width="3" height="3" fill="#000000" />
                        <rect x="0" y="26" width="7" height="7" fill="#000000" />
                        <rect x="1" y="27" width="5" height="5" fill="#ffffff" />
                        <rect x="2" y="28" width="3" height="3" fill="#000000" />
                        <rect x="20" y="20" width="5" height="5" fill="#000000" />
                        <rect x="21" y="21" width="3" height="3" fill="#ffffff" />
                        <rect x="22" y="22" width="1" height="1" fill="#000000" />
                    </svg>
                </div>
            </div>

            <!-- SURNAME/NOM Value -->
            <div data-slip-val="surname" class="absolute font-bold text-black uppercase tracking-tight select-text block"
                style="left: 31.05%; top: 34.0%; width: auto; font-size: clamp(13px, 2.7cqw, 20px); line-height: 1; overflow: visible; white-space: nowrap !important; word-break: keep-all !important;">
                {{ $val('surname') }}
            </div>

            <!-- GIVEN NAMES/PRÉNOMS Value -->
            <div data-slip-val="given-names" class="absolute font-bold text-black uppercase tracking-tight select-text block"
                style="left: 31.05%; top: 46.2%; width: auto; font-size: clamp(12px, 2.4cqw, 18px); line-height: 1; overflow: visible; white-space: nowrap !important; word-break: keep-all !important;">
                {!! str_replace(' ', '&nbsp;', e($val('first_name') . (!empty($middleName) ? ', ' . $middleName : ''))) !!}
            </div>

            <!-- DATE OF BIRTH Value -->
            <div data-slip-val="dob" class="absolute font-bold text-black uppercase tracking-tight select-text block"
                style="left: 30.98%; top: 60.5%; width: auto; font-size: clamp(11.5px, 2.3cqw, 17px); line-height: 1; overflow: visible; white-space: nowrap !important; word-break: keep-all !important;">
                {{ $dobDisplay }}
            </div>

            <!-- SEX/SEXE Value -->
            <div data-slip-val="sex" class="absolute font-bold text-black uppercase tracking-tight select-text block"
                style="left: 56.58%; top: 60.5%; width: auto; font-size: clamp(11.5px, 2.3cqw, 17px); line-height: 1; overflow: visible; white-space: nowrap !important; word-break: keep-all !important;">
                {{ $genderLetter }}
            </div>

            <!-- Watermark 3: Inverted 180° Ghost NIN above ISSUE DATE (seamlessly masks NGA) -->
            <div class="absolute pointer-events-none select-none flex items-center justify-center bg-[#d8ebd3]"
                style="left: 76.0%; top: 48.5%; width: 18.0%; height: 10.5%;">
                <div class="font-sans font-normal select-none whitespace-nowrap rotate-180"
                    style="font-family: 'Helvetica Neue', Helvetica, Arial, sans-serif; color: rgb(220, 220, 220); font-size: clamp(8px, 1.25cqw, 14px); text-shadow: 0 0 1px rgba(50, 90, 50, 0.5);">
                    {{ $ninClean }}
                </div>
            </div>

            <!-- ISSUE DATE Value -->
            <div data-slip-val="issue-date" class="absolute font-bold text-black uppercase tracking-tight select-text block"
                style="left: 77.2%; top: 67.5%; width: auto; font-size: clamp(11px, 2.2cqw, 16px); line-height: 1; overflow: visible; white-space: nowrap !important; word-break: keep-all !important;">
                {{ $issueDateDisplay }}
            </div>

            <!-- Watermark 4: Ghost NIN directly below ISSUE DATE value -->
            <div class="absolute pointer-events-none select-none flex items-center"
                style="left: 77.2%; top: 72.8%; width: 20.0%; height: 4.5%;">
                <div class="font-sans font-normal select-none whitespace-nowrap"
                    style="font-family: 'Helvetica Neue', Helvetica, Arial, sans-serif; color: rgb(220, 220, 220); font-size: clamp(8px, 1.2cqw, 13px); text-shadow: 0 0 1px rgba(50, 90, 50, 0.4);">
                    {{ $ninClean }}
                </div>
            </div>

            <!-- NIN NUMBER (Spaced Large Bold: 4 digits, 3 digits, 4 digits) -->
            <div data-slip-val="nin" class="absolute font-black text-black select-text overflow-visible text-center block"
                style="left: 0; right: 0; top: 84.0%; font-size: clamp(26px, 5.5cqw, 36px); line-height: 1; white-space: nowrap !important; word-break: keep-all !important;">
                <span class="font-mono font-black tracking-wider whitespace-nowrap">{{ $p1 }}&nbsp;&nbsp;&nbsp;{{ $p2 }}&nbsp;&nbsp;&nbsp;{{ $p3 }}</span>
            </div>
        </div>
    </div>

    <!-- Back Card Container (520 x 319) Directly Beneath Front Card -->
    <div class="relative w-full rounded-none overflow-hidden shadow-md border border-slate-300 mt-3 sm:mt-4 select-none"
        style="aspect-ratio: 520 / 319;">
        <!-- Official Upside Down Back of Card Template -->
        <img src="{{ $backBgSrc }}"
            alt="Digital NIN Card Back" crossorigin="anonymous"
            class="w-full h-full object-fill pointer-events-none select-none">
    </div>
</div>

<!-- ===================================================================== -->
<!-- SLIP 3: COMPACT / BASIC VERIFICATION SLIP                             -->
<!-- ===================================================================== -->
<div id="slip-compact"
    class="slip-pane hidden w-full max-w-4xl bg-white text-slate-900 rounded-none shadow-2xl overflow-hidden relative print-target select-none mx-auto"
    style="aspect-ratio: 2481 / 1710; min-width: 600px; container-type: inline-size;">

    @php
        $basicBgPath = public_path('images/card_and_Slip/basic.jpg');
        $basicBgSrc = file_exists($basicBgPath) ? 'data:image/jpeg;base64,' . base64_encode(file_get_contents($basicBgPath)) : asset('images/card_and_Slip/basic.jpg');
    @endphp

    <!-- Official Basic NINS Template Background Image -->
    <img src="{{ $basicBgSrc }}"
        data-original-src="{{ asset('images/card_and_Slip/basic.jpg') }}"
        alt="National Identification Number Basic Slip" crossorigin="anonymous"
        class="absolute inset-0 w-full h-full object-fill pointer-events-none select-none z-0">

    <!-- Overlay Identity Data on Top of Template -->
    <div class="absolute inset-0 z-10 font-sans text-slate-800 pointer-events-auto">
        <!-- First Name Value -->
        <div data-slip-val="first-name" class="absolute block font-bold text-slate-800 uppercase tracking-normal select-text"
            style="left: 13.3%; top: 28.1%; width: auto; font-size: clamp(11.5px, 1.4cqw, 16px); line-height: 1; overflow: visible; white-space: nowrap !important; word-break: keep-all !important;">
            {{ $val('first_name') }}
        </div>

        <!-- Middle Name Value -->
        <div data-slip-val="middle-name" class="absolute block font-bold text-slate-800 uppercase tracking-normal select-text"
            style="left: 13.3%; top: 35.6%; width: auto; font-size: clamp(11.5px, 1.4cqw, 16px); line-height: 1; overflow: visible; white-space: nowrap !important; word-break: keep-all !important;">
            {{ $middleName }}
        </div>

        <!-- Last Name Value -->
        <div data-slip-val="surname" class="absolute block font-bold text-slate-800 uppercase tracking-normal select-text"
            style="left: 13.3%; top: 42.0%; width: auto; font-size: clamp(11.5px, 1.4cqw, 16px); line-height: 1; overflow: visible; white-space: nowrap !important; word-break: keep-all !important;">
            {{ $val('surname') }}
        </div>

        <!-- Date of Birth Value -->
        <div data-slip-val="dob" class="absolute block font-bold text-slate-800 font-mono tracking-normal select-text"
            style="left: 13.3%; top: 49.4%; width: auto; font-size: clamp(11.5px, 1.4cqw, 16px); line-height: 1; overflow: visible; white-space: nowrap !important; word-break: keep-all !important;">
            {{ $val('date_of_birth', $val('birthdate')) }}
        </div>

        <!-- Gender Value -->
        <div data-slip-val="gender" class="absolute block font-bold text-slate-800 uppercase tracking-normal select-text"
            style="left: 13.3%; top: 56.8%; width: auto; font-size: clamp(11.5px, 1.4cqw, 16px); line-height: 1; overflow: visible; white-space: nowrap !important; word-break: keep-all !important;">
            {{ $genderLetter }}
        </div>

        <!-- Applicant Photograph (Center Photo Box) -->
        <div class="absolute overflow-hidden flex items-center justify-center bg-slate-100 select-none"
            style="left: 28.95%; top: 26.35%; width: 18.38%; height: 29.80%;">
            @if (!empty($resolvedPhoto))
                <img src="{{ $resolvedPhoto }}" alt="Applicant Photograph"
                    class="w-full h-full object-cover object-center select-none pointer-events-none">
            @else
                <div class="w-full h-full flex flex-col items-center justify-center bg-slate-100 text-slate-400">
                    <svg class="w-12 h-12 text-slate-300" fill="currentColor" viewBox="0 0 24 24">
                        <path d="M12 12c2.21 0 4-1.79 4-4s-1.79-4-4-4-4 1.79-4 4 1.79 4 4 4zm0 2c-2.67 0-8 1.34-8 4v2h16v-2c0-2.66-5.33-4-8-4z" />
                    </svg>
                </div>
            @endif
        </div>

        <!-- Returned Signature (Only Added When Response Returns It) -->
        @php
            $rawSig = $d['signature'] ?? ($rawPayload['message']['signature'] ?? null);
            $hasSig = !empty($rawSig) && is_string($rawSig) && $rawSig !== '—' && !in_array(strtolower($rawSig), ['null', 'none', 'n/a'], true);
            $signaturePhoto = $hasSig
                ? (str_starts_with($rawSig, 'data:')
                    ? $rawSig
                    : (filter_var($rawSig, FILTER_VALIDATE_URL)
                        ? $rawSig
                        : 'data:image/png;base64,' . $rawSig))
                : null;
        @endphp
        @if (!empty($signaturePhoto))
            <div class="absolute flex items-center select-none"
                style="left: 34.8%; top: 57.1%; width: 12.5%; height: 2.6%;">
                <img src="{{ $signaturePhoto }}" alt="Signature"
                    class="max-h-full max-w-full object-contain pointer-events-none">
            </div>
        @endif

        <!-- NIN NUMBER Value -->
        <div data-slip-val="nin" class="absolute block font-black text-slate-900 font-mono tracking-wider select-text overflow-visible"
            style="left: 22.0%; top: 62.8%; width: auto; font-size: clamp(20px, 2.5cqw, 28px); line-height: 1; white-space: nowrap !important; word-break: keep-all !important;">
            {{ $ninPlain }}
        </div>

        <!-- Tracking ID Value -->
        <div data-slip-val="tracking-id" class="absolute block font-bold text-slate-800 font-mono tracking-normal select-text"
            style="left: 13.3%; top: 70.4%; width: auto; font-size: clamp(10.5px, 1.25cqw, 15px); line-height: 1; overflow: visible; white-space: nowrap !important; word-break: keep-all !important;">
            {{ !empty($trackingId) ? $trackingId : '—' }}
        </div>

        <!-- Phone Number Value -->
        <div data-slip-val="phone" class="absolute block font-bold text-slate-800 font-mono tracking-normal select-text"
            style="left: 39.5%; top: 70.4%; width: auto; font-size: clamp(10.5px, 1.25cqw, 15px); line-height: 1; overflow: visible; white-space: nowrap !important; word-break: keep-all !important;">
            {{ $phoneFormatted }}
        </div>

        <!-- Residence State Value -->
        <div data-slip-val="residence-state" class="absolute block font-bold text-slate-800 uppercase tracking-normal select-text"
            style="left: 13.3%; top: 78.6%; width: auto; font-size: clamp(10.5px, 1.25cqw, 15px); line-height: 1; overflow: visible; white-space: nowrap !important; word-break: keep-all !important;">
            {{ $val('residence_state', $val('self_origin_state')) }}
        </div>

        <!-- Residence LGA/Town Value -->
        <div data-slip-val="residence-lga" class="absolute block font-bold text-slate-800 uppercase tracking-normal select-text"
            style="left: 39.5%; top: 78.6%; width: auto; font-size: clamp(10.5px, 1.25cqw, 15px); line-height: 1; overflow: visible; white-space: nowrap !important; word-break: keep-all !important;">
            {{ $residenceTown }}
        </div>

        <!-- Birth State Value -->
        <div data-slip-val="birth-state" class="absolute block font-bold text-slate-800 uppercase tracking-normal select-text"
            style="left: 13.3%; top: 84.4%; width: auto; font-size: clamp(10.5px, 1.25cqw, 15px); line-height: 1; overflow: visible; white-space: nowrap !important; word-break: keep-all !important;">
            {{ $val('birth_state', $val('birthstate', $val('state_of_origin', '---'))) }}
        </div>

        <!-- Birth LGA Value -->
        <div data-slip-val="birth-lga" class="absolute block font-bold text-slate-800 uppercase tracking-normal select-text"
            style="left: 39.5%; top: 84.4%; width: auto; font-size: clamp(10.5px, 1.25cqw, 15px); line-height: 1; overflow: visible; white-space: nowrap !important; word-break: keep-all !important;">
            {{ $val('birth_lga', $val('birthlga', $val('lga_of_origin', '---'))) }}
        </div>

        <!-- Address Value -->
        <div data-slip-val="address" class="absolute block font-bold text-slate-800 uppercase tracking-normal select-text"
            style="left: 13.3%; top: 90.4%; width: auto; font-size: clamp(10.5px, 1.25cqw, 15px); line-height: 1; overflow: visible; white-space: nowrap !important; word-break: keep-all !important;">
            {{ $residenceAddress }}
        </div>
    </div>
</div>

<!-- ===================================================================== -->
<!-- SLIP 4: IMPROVE-SLIP (NIMC OFFICIAL IMPROVED SLIP FORMAT)              -->
<!-- Exactly matching official sheet: Top instructions, seamless fold line  -->
<!-- ===================================================================== -->
<div id="slip-improve"
    class="slip-pane hidden w-full max-w-[620px] bg-white text-slate-900 p-8 sm:p-10 flex flex-col items-center select-none shadow-2xl print-target mx-auto">

    <!-- Header Text (Exact wording & style from official Improved NIN Slip) -->
    <div class="flex flex-col items-center text-center mb-6 w-full font-sans select-none">
        <h2 class="text-sm sm:text-base md:text-lg font-bold text-black tracking-tight text-center leading-snug">
            Please find below your Improved NIN Slip
        </h2>
        <p class="text-xs sm:text-sm font-bold text-black mt-1 text-center">
            You may cut it out of the paper, fold and laminate as desired.
        </p>
        <p class="text-xs sm:text-sm font-bold text-black mt-1 text-center">
            For your security &amp; privacy, please DO NOT permit others to make photocopies of this slip.
        </p>
    </div>

    <!-- The Dual Card Unit (Front and Back seamless with border & fold line) -->
    <div class="w-full border border-black overflow-hidden bg-white select-none">

        <!-- FRONT CARD (1367 x 876) -->
        <div class="relative w-full overflow-hidden select-none border-b border-black"
            style="aspect-ratio: 1367 / 876; container-type: inline-size;">

            @php
                $improveBgPath = public_path('images/card_and_Slip/improve.jpg');
                $improveBgSrc = file_exists($improveBgPath) ? 'data:image/jpeg;base64,' . base64_encode(file_get_contents($improveBgPath)) : asset('images/card_and_Slip/improve.jpg');
                $backBgPath = public_path('images/card_and_Slip/back.png');
                $backBgSrc = file_exists($backBgPath) ? 'data:image/png;base64,' . base64_encode(file_get_contents($backBgPath)) : asset('images/card_and_Slip/back.png');
            @endphp

            <!-- Front Template Background Image -->
            <img src="{{ $improveBgSrc }}"
                alt="Improved NIN Slip Front"
                class="absolute inset-0 w-full h-full object-fill pointer-events-none select-none z-0">

            <!-- Front Identity Data Overlay -->
            <div class="absolute inset-0 z-10 font-sans text-black pointer-events-auto">

                <!-- Verified Photograph (Framed cleanly: left 3.3%, top 26.0%, width 21.2%, height 38.0%, subtle rounded corners, NO watermark) -->
                <div class="absolute overflow-hidden flex items-center justify-center bg-slate-100 select-none shadow-xs"
                    style="left: 3.3%; top: 26.0%; width: 21.2%; height: 38.0%; border-radius: clamp(3px, 0.6cqw, 6px);">
                    @if (!empty($resolvedPhoto))
                        <img src="{{ $resolvedPhoto }}" alt="Applicant Photograph"
                            class="w-full h-full object-cover object-center select-none pointer-events-none">
                    @else
                        <div class="w-full h-full flex flex-col items-center justify-center bg-slate-100 text-slate-400">
                            <svg class="w-10 h-10 text-slate-300" fill="currentColor" viewBox="0 0 24 24">
                                <path d="M12 12c2.21 0 4-1.79 4-4s-1.79-4-4-4-4 1.79-4 4 1.79 4 4 4zm0 2c-2.67 0-8 1.34-8 4v2h16v-2c0-2.66-5.33-4-8-4z" />
                            </svg>
                        </div>
                    @endif
                </div>

                <!-- Surname / Nom Value (Cleanly placed directly below label) -->
                <div data-slip-val="surname" class="absolute font-bold text-black uppercase tracking-tight select-text block"
                    style="left: 28.2%; top: 31.0%; width: auto; font-size: clamp(13px, 2.6cqw, 20px); line-height: 1; overflow: visible; white-space: nowrap !important; word-break: keep-all !important;">
                    {{ $val('surname') }}
                </div>

                <!-- Given Names / Prénoms Value (Cleanly placed directly below label, formatted as FIRSTNAME,    MIDDLENAME) -->
                @php
                    $givenNamesFormatted = $val('first_name');
                    if (!empty($middleName)) {
                        if (!str_contains($givenNamesFormatted, ',')) {
                            $givenNamesFormatted .= ',    ' . $middleName;
                        } else {
                            $givenNamesFormatted .= '    ' . $middleName;
                        }
                    }
                @endphp
                <div data-slip-val="given-names" class="absolute font-bold text-black uppercase tracking-tight select-text block"
                    style="left: 28.2%; top: 43.5%; width: auto; font-size: clamp(12px, 2.3cqw, 17px); line-height: 1; overflow: visible; white-space: nowrap !important; word-break: keep-all !important;">
                    {!! str_replace(' ', '&nbsp;', e($givenNamesFormatted)) !!}
                </div>

                <!-- Date of Birth Value (Directly below label, DD-MM-YYYY format matching sample) -->
                <div data-slip-val="dob" class="absolute font-bold text-black tracking-tight select-text block"
                    style="left: 28.2%; top: 57.0%; width: auto; font-size: clamp(11.5px, 2.2cqw, 16px); line-height: 1; overflow: visible; white-space: nowrap !important; word-break: keep-all !important;">
                    {{ $dobNumeric ?? $val('date_of_birth', $val('birthdate')) }}
                </div>

                <!-- Mask ISSUE DATE from improve.jpg so it matches official sample -->
                <div class="absolute bg-white pointer-events-none"
                    style="left: 69.0%; top: 55.0%; width: 25.0%; height: 8.0%;"></div>

                <!-- 2D Barcode (QR Code) (Cleanly below NGA, square, matching official sample size & position) -->
                <div class="absolute overflow-hidden bg-white select-none flex items-center justify-center p-0.5"
                    style="left: 69.5%; top: 25.5%; width: 21.0%; aspect-ratio: 1 / 1;">
                    <div id="qrcode-improve-target" class="w-full h-full flex items-center justify-center">
                        <svg viewBox="0 0 33 33" class="w-full h-full" shape-rendering="crispEdges">
                            <rect width="33" height="33" fill="#ffffff" />
                            <rect x="0" y="0" width="7" height="7" fill="#000000" />
                            <rect x="1" y="1" width="5" height="5" fill="#ffffff" />
                            <rect x="2" y="2" width="3" height="3" fill="#000000" />
                            <rect x="26" y="0" width="7" height="7" fill="#000000" />
                            <rect x="27" y="1" width="5" height="5" fill="#ffffff" />
                            <rect x="28" y="2" width="3" height="3" fill="#000000" />
                            <rect x="0" y="26" width="7" height="7" fill="#000000" />
                            <rect x="1" y="27" width="5" height="5" fill="#ffffff" />
                            <rect x="2" y="28" width="3" height="3" fill="#000000" />
                            <rect x="20" y="20" width="5" height="5" fill="#000000" />
                            <rect x="21" y="21" width="3" height="3" fill="#ffffff" />
                            <rect x="22" y="22" width="1" height="1" fill="#000000" />
                        </svg>
                    </div>
                </div>

                <!-- National Identification Number (NIN) (Centered, bold spaced digits: 4232  126  8596) -->
                <div data-slip-val="nin" class="absolute font-black text-black select-text overflow-visible text-center block"
                    style="left: 0; right: 0; top: 79.0%; font-size: clamp(26px, 5.2cqw, 36px); line-height: 1; white-space: nowrap !important; word-break: keep-all !important;">
                    <span class="font-sans font-black tracking-tight whitespace-nowrap">{{ $p1 }}&nbsp;&nbsp;&nbsp;&nbsp;{{ $p2 }}&nbsp;&nbsp;&nbsp;&nbsp;{{ $p3 }}</span>
                </div>
            </div>
        </div>

        <!-- BACK CARD (Upside Down Back directly beneath Front, identical width & aspect ratio) -->
        <div class="relative w-full overflow-hidden select-none bg-white"
            style="aspect-ratio: 1367 / 876;">
            <!-- Official Upside Down Back of Card Template -->
            <img src="{{ $backBgSrc }}"
                alt="Improved NIN Slip Back"
                class="w-full h-full object-fill pointer-events-none select-none">
        </div>
    </div>
</div>
