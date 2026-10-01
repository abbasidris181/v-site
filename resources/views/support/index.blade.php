@extends('layouts.app')

@section('title', 'Help Desk & Support')

@section('content')
<div class="max-w-5xl mx-auto space-y-8">
    <!-- Breadcrumb -->
    <nav class="flex text-xs font-medium text-slate-500 dark:text-slate-400 space-x-2">
        <a href="{{ route('dashboard') }}" class="hover:text-slate-900 dark:hover:text-white transition-colors">Dashboard</a>
        <span>&rsaquo;</span>
        <span class="text-slate-900 dark:text-white font-semibold">Help & Support Desk</span>
    </nav>

    <!-- Contact Channels -->
    <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
        <!-- WhatsApp Support Desk -->
        <div class="rounded-2xl bg-white dark:bg-slate-900 border border-slate-200/80 dark:border-slate-800 p-6 shadow-sm hover:border-emerald-500/40 transition">
            <div class="w-12 h-12 rounded-xl bg-emerald-50 dark:bg-emerald-500/10 text-emerald-600 dark:text-emerald-400 flex items-center justify-center mb-4">
                <svg class="w-6 h-6" fill="currentColor" viewBox="0 0 24 24">
                    <path d="M12.031 6.172c-3.181 0-5.767 2.586-5.768 5.766-.001 1.298.38 2.27 1.019 3.287l-.711 2.598 2.664-.698c.97.529 1.769.789 2.796.789h.001c3.181 0 5.767-2.586 5.768-5.766 0-3.18-2.586-5.766-5.769-5.766zm3.376 8.204c-.14.394-.716.746-1.002.779-.276.033-.63.05-1.019-.074-.239-.077-.55-.187-.946-.359-1.68-.727-2.766-2.455-2.85-2.568-.084-.112-.686-.913-.686-1.742 0-.829.434-1.237.588-1.394.154-.158.337-.197.449-.197.112 0 .224.001.322.006.105.005.244-.04.382.29.14.337.478 1.164.52 1.25.042.085.07.185.014.298-.056.112-.084.183-.168.281-.084.098-.178.22-.253.295-.084.085-.172.177-.074.346.098.168.435.717.935 1.162.642.571 1.183.748 1.352.833.168.084.267.07.366-.042.098-.113.421-.491.534-.659.112-.169.224-.141.378-.084.154.056.974.46 1.143.544.168.084.281.127.323.197.042.07.042.408-.098.802zM12 2C6.477 2 2 6.477 2 12c0 1.89.525 3.66 1.438 5.168L2 22l4.981-1.309A9.957 9.957 0 0012 22c5.523 0 10-4.477 10-10S17.523 2 12 2zm0 18.182c-1.637 0-3.155-.494-4.428-1.341l-.317-.212-2.969.779.792-2.894-.207-.329A8.147 8.147 0 013.818 12c0-4.512 3.67-8.182 8.182-8.182 4.512 0 8.182 3.67 8.182 8.182 0 4.512-3.67 8.182-8.182 8.182z"/>
                </svg>
            </div>
            <h3 class="text-sm font-bold text-slate-900 dark:text-white">WhatsApp Live Chat</h3>
            <p class="text-xs text-slate-500 dark:text-slate-400 mt-1">Instant messaging with support officers for fast resolution.</p>
            @php
                $rawWhatsapp = !empty($contactWhatsapp) ? $contactWhatsapp : '+234 812 345 6789';
                $cleanWhatsappDigits = preg_replace('/[^0-9]/', '', $rawWhatsapp);
                if (str_starts_with($cleanWhatsappDigits, '0')) {
                    $cleanWhatsappDigits = '234' . substr($cleanWhatsappDigits, 1);
                }
                $whatsappUrl = 'https://wa.me/' . $cleanWhatsappDigits;
            @endphp
            <div class="mt-4">
                <a href="{{ $whatsappUrl }}" target="_blank" class="inline-flex items-center gap-1.5 text-xs font-bold text-emerald-600 dark:text-emerald-400 hover:underline">
                    <span>Chat on WhatsApp</span>
                    <span class="text-[11px] font-normal text-slate-500 dark:text-slate-400">({{ $rawWhatsapp }})</span>
                    <span>&rarr;</span>
                </a>
            </div>
        </div>

        <!-- Email Helpdesk -->
        <div class="rounded-2xl bg-white dark:bg-slate-900 border border-slate-200/80 dark:border-slate-800 p-6 shadow-sm hover:border-indigo-500/40 transition">
            <div class="w-12 h-12 rounded-xl bg-indigo-50 dark:bg-indigo-500/10 text-indigo-600 dark:text-indigo-400 flex items-center justify-center mb-4">
                <svg class="w-6 h-6" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 8l7.89 5.26a2 2 0 002.22 0L21 8M5 19h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z" />
                </svg>
            </div>
            <h3 class="text-sm font-bold text-slate-900 dark:text-white">Email Helpdesk</h3>
            <p class="text-xs text-slate-500 dark:text-slate-400 mt-1">Send tickets and audit documentation for complex inquiries.</p>
            @php
                $displayEmail = !empty($contactEmail) ? $contactEmail : 'support@vsite.ng';
            @endphp
            <div class="mt-4">
                <a href="mailto:{{ $displayEmail }}" class="inline-flex items-center gap-1.5 text-xs font-bold text-indigo-600 dark:text-indigo-400 hover:underline">
                    <span>{{ $displayEmail }}</span>
                    <span>&rarr;</span>
                </a>
            </div>
        </div>

        <!-- Operating Hours & SLA -->
        <div class="rounded-2xl bg-white dark:bg-slate-900 border border-slate-200/80 dark:border-slate-800 p-6 shadow-sm">
            <div class="w-12 h-12 rounded-xl bg-amber-50 dark:bg-amber-500/10 text-amber-600 dark:text-amber-400 flex items-center justify-center mb-4">
                <svg class="w-6 h-6" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z" />
                </svg>
            </div>
            <h3 class="text-sm font-bold text-slate-900 dark:text-white">Operational SLA</h3>
            <p class="text-xs text-slate-500 dark:text-slate-400 mt-1">Monday – Saturday: 8:00 AM – 8:00 PM WAT.</p>
            <div class="mt-4 flex flex-col gap-1 text-xs">
                <span class="font-medium text-amber-600 dark:text-amber-400">Manual Clearing turnaround: 15–45 mins.</span>
                @if(!empty($contactPhone))
                    <span class="text-slate-500 dark:text-slate-400">Phone: <a href="tel:{{ preg_replace('/[^0-9+]/', '', $contactPhone) }}" class="font-semibold text-slate-700 dark:text-slate-300 hover:underline">{{ $contactPhone }}</a></span>
                @endif
            </div>
        </div>
    </div>

    <!-- FAQ Accordion -->
    <div class="rounded-2xl bg-white dark:bg-slate-900 border border-slate-200/80 dark:border-slate-800 p-6 sm:p-8 shadow-sm">
        <div class="flex items-center justify-between mb-6">
            <h2 class="text-lg font-bold text-slate-900 dark:text-white">Frequently Asked Questions</h2>
            <span class="text-xs text-slate-400 font-medium">Clear answers to common questions</span>
        </div>

        <div class="space-y-4 text-sm">
            @forelse($faqs as $faq)
                <div class="p-5 rounded-2xl bg-slate-50/80 dark:bg-slate-800/40 border border-slate-100 dark:border-slate-800 transition hover:border-slate-200 dark:hover:border-slate-700">
                    <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-2">
                        <div class="font-bold text-slate-900 dark:text-white text-base">
                            {{ $faq->question }}
                        </div>
                        @if($faq->category && $faq->category !== 'General')
                            <span class="self-start sm:self-auto inline-flex items-center px-2.5 py-0.5 rounded-full text-[10px] font-semibold bg-indigo-50 dark:bg-indigo-500/10 text-indigo-700 dark:text-indigo-400 border border-indigo-200/80 dark:border-indigo-500/30">
                                {{ $faq->category }}
                            </span>
                        @endif
                    </div>
                    <div class="mt-2 text-xs text-slate-600 dark:text-slate-300 leading-relaxed whitespace-pre-line">
                        {{ $faq->answer }}
                    </div>
                </div>
            @empty
                <div class="py-8 text-center text-slate-400">
                    <p class="text-xs">No frequently asked questions available at the moment.</p>
                </div>
            @endforelse
        </div>
    </div>
</div>
@endsection
