@props(['service', 'size' => 'md'])

@php
    $slug = $service->slug ?? '';
    $isBvn = str_contains($slug, 'bvn');

    $dimensions = match($size) {
        'xs' => 'w-6 h-6 rounded-md',
        'sm' => 'w-8 h-8 rounded-lg',
        'inline' => 'w-7 h-7 sm:w-8 sm:h-8 rounded-lg',
        'lg' => 'w-12 h-12 rounded-xl',
        'card' => 'w-[52px] h-[52px] min-w-[52px] min-h-[52px] sm:w-[74px] sm:h-[74px] sm:min-w-[74px] sm:min-h-[74px] rounded-xl sm:rounded-2xl',
        default => 'w-10 h-10 rounded-xl',
    };

    $containerBg = match($size) {
        'card', 'inline', 'sm' => $isBvn
            ? 'bg-[#ebedf7] dark:bg-slate-800 border border-[#d8def2] dark:border-slate-700/60 shadow-xs'
            : 'bg-[#e6f4f6] dark:bg-slate-800 border border-[#d2edf0] dark:border-slate-700/60 shadow-xs',
        default => 'bg-slate-800/90 border border-slate-700/80 shadow-sm text-emerald-400 group-hover:border-emerald-500/50 group-hover:bg-emerald-500/10 group-hover:text-emerald-300',
    };

    $iconDimensions = match($size) {
        'xs' => 'w-3.5 h-3.5',
        'sm' => 'w-4 h-4',
        'inline' => 'w-4 h-4 sm:w-4.5 sm:h-4.5',
        'lg' => 'w-6 h-6',
        'card' => 'w-6 h-6 sm:w-8 sm:h-8',
        default => 'w-5 h-5',
    };

    // Check if custom image exists in public/images/services/{slug}.*
    $imagePath = null;
    $possibleExtensions = ['svg', 'png', 'webp', 'jpg'];
    foreach ($possibleExtensions as $ext) {
        if (file_exists(public_path("images/services/{$slug}.{$ext}"))) {
            $imagePath = asset("images/services/{$slug}.{$ext}");
            break;
        }
    }
@endphp

<div {{ $attributes->merge(['class' => "$dimensions $containerBg flex items-center justify-center flex-shrink-0 transition-all"]) }}>
    @if($imagePath)
        <img src="{{ $imagePath }}" alt="{{ $service->name }}" class="{{ $size === 'card' ? 'max-w-[38px] max-h-[30px] sm:max-w-[54px] sm:max-h-[44px]' : (($size === 'inline' || $size === 'sm') ? 'max-w-[20px] max-h-[20px] sm:max-w-[22px] sm:max-h-[22px]' : 'w-full h-full p-1.5') }} object-contain">
    @else
        @switch($slug)
            @case('nin-verification')
            @case('nin-verification-2')
            @case('nin-verification-3')
                <!-- NIN Identity Card Icon -->
                <svg class="{{ $iconDimensions }}" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.8" d="M10 6H5a2 2 0 00-2 2v9a2 2 0 002 2h14a2 2 0 002-2V8a2 2 0 00-2-2h-5m-4 0V5a2 2 0 114 0v1m-4 0a2 2 0 104 0m-5 8a2 2 0 100-4 2 2 0 000 4zm0 0c1.306 0 2.417.835 2.83 2M9 14a3.001 3.001 0 00-2.83 2M15 11h3m-3 4h2" />
                </svg>
                @break

            @case('bvn-verification')
                <!-- BVN Bank / Financial Shield Icon -->
                <svg class="{{ $iconDimensions }}" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.8" d="M9 12l2 2 4-4m5.618-4.016A11.955 11.955 0 0112 2.944a11.955 11.955 0 01-8.618 3.04A12.02 12.02 0 003 9c0 5.591 3.824 10.29 9 11.622 5.176-1.332 9-6.03 9-11.622 0-1.042-.133-2.052-.382-3.016z" />
                </svg>
                @break

            @case('ipe-clearing')
                <!-- IPE Passport / Enrollment Document Clearing Icon -->
                <svg class="{{ $iconDimensions }}" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.8" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z" />
                </svg>
                @break

            @case('nin-validation')
                <!-- NIN Validation / Fingerprint / Checkmark Icon -->
                <svg class="{{ $iconDimensions }}" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.8" d="M9 12l2 2 4-4M7.835 4.697a3.42 3.42 0 001.946-.806 3.42 3.42 0 014.438 0 3.42 3.42 0 001.946.806 3.42 3.42 0 013.138 3.138 3.42 3.42 0 00.806 1.946 3.42 3.42 0 010 4.438 3.42 3.42 0 00-.806 1.946 3.42 3.42 0 01-3.138 3.138 3.42 3.42 0 00-1.946.806 3.42 3.42 0 01-4.438 0 3.42 3.42 0 00-1.946-.806 3.42 3.42 0 01-3.138-3.138 3.42 3.42 0 00-.806-1.946 3.42 3.42 0 010-4.438 3.42 3.42 0 00.806-1.946 3.42 3.42 0 013.138-3.138z" />
                </svg>
                @break

            @case('modification-ipe')
                <!-- Modification IPE / Edit Records Icon -->
                <svg class="{{ $iconDimensions }}" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.8" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z" />
                </svg>
                @break

            @case('bvn-retrieval')
                <!-- BVN Retrieval / Search by Phone Icon -->
                <svg class="{{ $iconDimensions }}" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.8" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0zM10 7v3m0 0v3m0-3h3m-3 0H7" />
                </svg>
                @break

            @case('self-service-delinking')
                <!-- Delinking / Unlink Icon -->
                <svg class="{{ $iconDimensions }}" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.8" d="M13.828 10.172a4 4 0 00-5.656 0l-4 4a4 4 0 105.656 5.656l1.102-1.101m-.758-4.899a4 4 0 005.656 0l4-4a4 4 0 00-5.656-5.656l-1.1 1.1" />
                </svg>
                @break

            @case('personalization')
                <!-- Personalization / Identification Badge & Profile Icon -->
                <svg class="{{ $iconDimensions }}" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.8" d="M5.121 17.804A13.937 13.937 0 0112 16c2.5 0 4.847.655 6.879 1.804M15 10a3 3 0 11-6 0 3 3 0 016 0zm6 2a9 9 0 11-18 0 9 9 0 0118 0z" />
                </svg>
                @break

            @default
                <!-- Default Service Sparkle Icon -->
                <svg class="{{ $iconDimensions }}" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.8" d="M13 10V3L4 14h7v7l9-11h-7z" />
                </svg>
        @endswitch
    @endif
</div>
