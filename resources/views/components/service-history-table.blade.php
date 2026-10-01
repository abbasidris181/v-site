@props(['requests', 'showService' => true, 'service' => null])

@php
    $slug = $service->slug ?? ($requests->first()?->service?->slug ?? '');
    $isIpeService = in_array($slug, ['ipe-clearing', 'modification-ipe']);
@endphp

<div class="rounded-2xl bg-white dark:bg-slate-900 border border-slate-200/80 dark:border-slate-800 shadow-sm dark:shadow-xl overflow-hidden transition-colors">
    <div class="p-5 sm:p-6 border-b border-slate-100 dark:border-slate-800 flex flex-col sm:flex-row sm:items-center justify-between gap-3">
        <div>
            <h3 class="text-base font-bold text-slate-900 dark:text-white tracking-tight">Verifications</h3>
        </div>
        <span class="text-xs font-medium text-slate-600 dark:text-slate-400 bg-slate-100 dark:bg-slate-800 px-3 py-1 rounded-lg border border-slate-200 dark:border-slate-700 self-start sm:self-auto">
            Total Records: {{ $requests instanceof \Illuminate\Pagination\AbstractPaginator ? $requests->total() : count($requests) }}
        </span>
    </div>

    <div class="overflow-x-auto">
        <table class="w-full text-left text-xs text-slate-600 dark:text-slate-300">
            <thead class="bg-slate-50 dark:bg-slate-950/60 text-slate-500 dark:text-slate-400 uppercase tracking-wider text-[10px] border-b border-slate-200 dark:border-slate-800">
                @if($isIpeService)
                    <tr>
                        <th class="py-3.5 px-4 font-semibold">Reference</th>
                        <th class="py-3.5 px-4 font-semibold">Batch ID</th>
                        @if($showService)
                            <th class="py-3.5 px-4 font-semibold">Service</th>
                        @endif
                        <th class="py-3.5 px-4 font-semibold">Old Tracking ID</th>
                        <th class="py-3.5 px-4 font-semibold text-right">Amount</th>
                        <th class="py-3.5 px-4 font-semibold text-center">Status</th>
                        <th class="py-3.5 px-4 font-semibold">Reply</th>
                        <th class="py-3.5 px-4 font-semibold">Date & Time</th>
                    </tr>
                @else
                    <tr>
                        <th class="py-3.5 px-4 font-semibold">Reference</th>
                        @if($showService)
                            <th class="py-3.5 px-4 font-semibold">Service</th>
                        @endif
                        <th class="py-3.5 px-4 font-semibold">Input</th>
                        <th class="py-3.5 px-4 font-semibold text-right">Amount</th>
                        <th class="py-3.5 px-4 font-semibold text-center">Status</th>
                        <th class="py-3.5 px-4 font-semibold">Date & Time</th>
                        <th class="py-3.5 px-4 font-semibold text-right">Action</th>
                    </tr>
                @endif
            </thead>
            <tbody class="divide-y divide-slate-100 dark:divide-slate-800/60 font-medium">
                @forelse ($requests as $item)
                    <tr class="hover:bg-slate-50/80 dark:hover:bg-slate-800/40 transition-colors">
                        <!-- Reference -->
                        <td class="py-3 px-4 text-slate-800 dark:text-slate-200 whitespace-nowrap font-mono text-xs font-semibold">
                            {{ $item->reference }}
                        </td>

                        @if($isIpeService)
                            <!-- Batch ID (8-digit Integer) -->
                            <td class="py-3 px-4 text-slate-700 dark:text-slate-300 whitespace-nowrap">
                                <button type="button"
                                        onclick="openBatchInspector('{{ $item->batch?->batch_reference ?? ($item->batch_id ?? $item->reference) }}', '{{ $item->batch_number }}')"
                                        class="inline-flex items-center gap-1.5 px-2.5 py-1 rounded-lg bg-indigo-50 hover:bg-indigo-100 dark:bg-indigo-950/50 dark:hover:bg-indigo-900/60 text-indigo-700 dark:text-indigo-300 border border-indigo-200 dark:border-indigo-800 font-mono text-xs font-bold transition-all shadow-sm hover:shadow active:scale-95 cursor-pointer group"
                                        title="View Batch Report">
                                    <svg class="w-3.5 h-3.5 text-indigo-500 group-hover:text-indigo-600 dark:text-indigo-400 transition-transform group-hover:scale-110" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"></path>
                                    </svg>
                                    <span>{{ $item->batch_number }}</span>
                                </button>
                            </td>
                        @endif

                        <!-- Service Name (conditional) -->
                        @if($showService)
                            <td class="py-3 px-4 text-slate-900 dark:text-white font-semibold whitespace-nowrap">
                                {{ $item->service->name }}
                            </td>
                        @endif

                        <!-- Input / Old Tracking ID -->
                        <td class="py-3 px-4 text-slate-700 dark:text-slate-300 font-mono">
                            {{ $item->tracking_input }}
                        </td>

                        <!-- Amount -->
                        <td class="py-3 px-4 text-right text-emerald-600 dark:text-emerald-400 font-bold whitespace-nowrap">
                            {{ $item->formatted_amount }}
                        </td>

                        <!-- Status Badge -->
                        <td class="py-3 px-4 text-center whitespace-nowrap">
                            @if($item->status === 'completed')
                                <span class="inline-flex items-center gap-1 text-[10px] font-semibold text-emerald-700 dark:text-emerald-400 bg-emerald-50 dark:bg-emerald-500/10 px-2.5 py-0.5 rounded-full border border-emerald-200 dark:border-emerald-500/20">
                                    <span class="w-1.5 h-1.5 rounded-full bg-emerald-500"></span>
                                    Completed
                                </span>
                            @elseif($item->status === 'processing')
                                <span class="inline-flex items-center gap-1 text-[10px] font-semibold text-indigo-700 dark:text-indigo-400 bg-indigo-50 dark:bg-indigo-500/10 px-2.5 py-0.5 rounded-full border border-indigo-200 dark:border-indigo-500/20">
                                    <span class="w-1.5 h-1.5 rounded-full bg-indigo-500 animate-pulse"></span>
                                    Processing
                                </span>
                            @elseif($item->status === 'failed')
                                <span class="inline-flex items-center gap-1 text-[10px] font-semibold text-red-700 dark:text-red-400 bg-red-50 dark:bg-red-500/10 px-2.5 py-0.5 rounded-full border border-red-200 dark:border-red-500/20">
                                    <span class="w-1.5 h-1.5 rounded-full bg-red-500"></span>
                                    Failed
                                </span>
                            @else
                                <span class="inline-flex items-center gap-1 text-[10px] font-semibold text-amber-700 dark:text-amber-400 bg-amber-50 dark:bg-amber-500/10 px-2.5 py-0.5 rounded-full border border-amber-200 dark:border-amber-500/20">
                                    <span class="w-1.5 h-1.5 rounded-full bg-amber-500"></span>
                                    Pending
                                </span>
                            @endif
                        </td>

                        @if($isIpeService)
                            <!-- Reply -->
                            <td class="py-3 px-4 text-slate-700 dark:text-slate-300 max-w-[220px]" title="{{ $item->admin_notes ?? ($item->rejection_reason ?? ($item->result_payload['resolution_reference'] ?? '')) }}">
                                @if($item->status === 'failed' && $item->rejection_reason)
                                    <span class="text-red-600 dark:text-red-400 text-xs">{{ $item->rejection_reason }}</span>
                                @elseif($item->admin_notes)
                                    <span class="text-slate-800 dark:text-slate-200 text-xs">{{ $item->admin_notes }}</span>
                                @elseif(!empty($item->result_payload['resolution_reference']))
                                    <span class="font-mono text-emerald-600 dark:text-emerald-400 text-xs">{{ $item->result_payload['resolution_reference'] }}</span>
                                @elseif($item->status === 'completed')
                                    <span class="text-emerald-600 dark:text-emerald-400 text-xs font-medium">Cleared</span>
                                @else
                                    <span class="text-slate-400 dark:text-slate-500">—</span>
                                @endif
                            </td>
                        @endif

                        <!-- Date -->
                        <td class="py-3 px-4 text-slate-500 dark:text-slate-400 whitespace-nowrap">
                            {{ $item->created_at->format('M d, Y h:i A') }}
                        </td>

                        @if(! $isIpeService)
                            <!-- Action (View Slip) -->
                            <td class="py-3 px-4 text-right whitespace-nowrap">
                                @if($item->status === 'completed' && !empty($item->result_payload))
                                    <a href="{{ route('services.slip', $item->reference) }}" target="_blank"
                                       class="inline-flex items-center gap-1.5 px-2.5 py-1 rounded-lg bg-emerald-600 hover:bg-emerald-500 text-white font-semibold text-xs transition-all shadow-xs hover:shadow active:scale-95 cursor-pointer"
                                       title="View & Print Official Slip">
                                        <svg class="w-3.5 h-3.5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 17h2a2 2 0 002-2v-4a2 2 0 00-2-2H5a2 2 0 00-2 2v4a2 2 0 002 2h2m2 4h6a2 2 0 002-2v-4a2 2 0 00-2-2H9a2 2 0 00-2 2v4a2 2 0 002 2zm8-12V5a2 2 0 00-2-2H9a2 2 0 00-2 2v4h10z" />
                                        </svg>
                                        <span>View Slip</span>
                                    </a>
                                @else
                                    <span class="text-slate-400 dark:text-slate-600 text-xs">—</span>
                                @endif
                            </td>
                        @endif
                    </tr>
                @empty
                    <tr>
                        <td colspan="{{ $isIpeService ? ($showService ? 8 : 7) : ($showService ? 7 : 6) }}" class="py-8 text-center text-slate-500 dark:text-slate-400">
                            No Verificaions Yet!
                        </td>
                    </tr>
                @endforelse
            </tbody>
        </table>
    </div>

    @if($requests instanceof \Illuminate\Pagination\AbstractPaginator && $requests->hasPages())
        <div class="p-4 border-t border-slate-100 dark:border-slate-800">
            {{ $requests->links() }}
        </div>
    @endif
</div>

@if($isIpeService)
    <!-- Batch Inspector Modal (Pop-up showing ONLY the Tracking IDs Report) -->
    <div id="batch_inspector_modal" class="fixed inset-0 z-50 hidden bg-slate-950/70 backdrop-blur-sm overflow-y-auto flex items-center justify-center p-4 sm:p-6" role="dialog" aria-modal="true">
        <div class="relative w-full max-w-2xl rounded-2xl bg-white dark:bg-slate-900 border border-slate-200 dark:border-slate-800 shadow-2xl overflow-hidden transition-all my-8">
            <!-- Modal Header -->
            <div class="px-6 py-4 border-b border-slate-100 dark:border-slate-800 flex items-center justify-between">
                <div class="flex items-center gap-2.5">
                    <div class="w-8 h-8 rounded-lg bg-indigo-50 dark:bg-indigo-950/60 text-indigo-600 dark:text-indigo-400 flex items-center justify-center border border-indigo-200 dark:border-indigo-800/60">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"></path>
                        </svg>
                    </div>
                    <div>
                        <h3 class="text-base font-bold text-slate-900 dark:text-white flex items-center gap-2">
                            <span>Batch Report</span>
                            <span class="text-slate-300 dark:text-slate-700">&bull;</span>
                            <span id="batch_modal_number" class="font-mono text-indigo-600 dark:text-indigo-400"></span>
                        </h3>
                    </div>
                </div>
                <button type="button" onclick="closeBatchInspector()" class="p-1.5 rounded-lg text-slate-400 hover:text-slate-600 dark:hover:text-slate-200 hover:bg-slate-100 dark:hover:bg-slate-800 transition-colors">
                    <span class="sr-only">Close</span>
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path>
                    </svg>
                </button>
            </div>

            <!-- Loading Spinner -->
            <div id="batch_modal_loading" class="p-12 text-center">
                <div class="inline-block animate-spin rounded-full h-8 w-8 border-4 border-indigo-500 border-t-transparent"></div>
                <p class="mt-3 text-xs text-slate-500 dark:text-slate-400 font-medium">Loading batch report...</p>
            </div>

            <!-- Error State -->
            <div id="batch_modal_error" class="hidden p-8 text-center">
                <div class="text-red-500 text-sm font-semibold mb-2">Failed to load batch report</div>
                <p id="batch_modal_error_msg" class="text-xs text-slate-500 dark:text-slate-400"></p>
            </div>

            <!-- Modal Content (Report Only) -->
            <div id="batch_modal_content" class="hidden p-6 space-y-4">
                <div class="flex items-center justify-between">
                    <span class="text-xs font-semibold text-slate-600 dark:text-slate-400">Tracking IDs Report</span>
                    <button type="button" onclick="copyBatchReportText()" id="batch_copy_btn" class="inline-flex items-center gap-1.5 px-3 py-1.5 rounded-lg text-xs font-semibold text-white bg-indigo-600 hover:bg-indigo-500 active:scale-95 transition-all shadow-sm cursor-pointer">
                        <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 16H6a2 2 0 01-2-2V6a2 2 0 012-2h8a2 2 0 012 2v2m-6 12h8a2 2 0 002-2v-8a2 2 0 00-2-2h-8a2 2 0 00-2 2v8a2 2 0 002 2z"></path>
                        </svg>
                        <span id="batch_copy_btn_text">Copy Report</span>
                    </button>
                </div>

                <div class="relative">
                    <textarea id="batch_report_textarea" readonly rows="14" class="w-full font-mono text-xs sm:text-sm leading-relaxed p-4 rounded-xl bg-slate-900 text-slate-100 border border-slate-800 focus:outline-none focus:ring-2 focus:ring-indigo-500 selection:bg-indigo-600 selection:text-white resize-y shadow-inner"></textarea>
                </div>
            </div>

            <!-- Modal Footer -->
            <div class="px-6 py-3.5 bg-slate-50 dark:bg-slate-950/60 border-t border-slate-100 dark:border-slate-800 flex justify-end">
                <button type="button" onclick="closeBatchInspector()" class="px-4 py-2 rounded-xl text-xs font-semibold text-slate-700 dark:text-slate-300 bg-white dark:bg-slate-800 border border-slate-200 dark:border-slate-700 hover:bg-slate-100 dark:hover:bg-slate-700 transition-colors cursor-pointer">
                    Close
                </button>
            </div>
        </div>
    </div>

    <script>
        function openBatchInspector(identifier, batchNumber) {
            const modal = document.getElementById('batch_inspector_modal');
            const loading = document.getElementById('batch_modal_loading');
            const error = document.getElementById('batch_modal_error');
            const content = document.getElementById('batch_modal_content');
            const numEl = document.getElementById('batch_modal_number');
            const textarea = document.getElementById('batch_report_textarea');

            if (!modal) return;

            modal.classList.remove('hidden');
            loading.classList.remove('hidden');
            error.classList.add('hidden');
            content.classList.add('hidden');
            numEl.textContent = batchNumber || identifier;

            fetch(`/services/batch/${encodeURIComponent(identifier)}`, {
                headers: {
                    'Accept': 'application/json',
                    'X-Requested-With': 'XMLHttpRequest'
                }
            })
            .then(res => {
                if (!res.ok) throw new Error('Network error or batch not found (' + res.status + ')');
                return res.json();
            })
            .then(data => {
                loading.classList.add('hidden');
                numEl.textContent = data.batch_number || batchNumber || identifier;
                textarea.value = data.report_text || '';
                content.classList.remove('hidden');
            })
            .catch(err => {
                loading.classList.add('hidden');
                const errMsgEl = document.getElementById('batch_modal_error_msg');
                if (errMsgEl) errMsgEl.textContent = err.message;
                error.classList.remove('hidden');
            });
        }

        function closeBatchInspector() {
            const modal = document.getElementById('batch_inspector_modal');
            if (modal) {
                modal.classList.add('hidden');
            }
        }

        function copyBatchReportText() {
            const textarea = document.getElementById('batch_report_textarea');
            const btnText = document.getElementById('batch_copy_btn_text');
            if (!textarea) return;

            textarea.select();
            navigator.clipboard.writeText(textarea.value).then(() => {
                if (btnText) {
                    btnText.textContent = 'Copied!';
                    setTimeout(() => {
                        btnText.textContent = 'Copy Report';
                    }, 2000);
                }
            }).catch(() => {
                document.execCommand('copy');
                if (btnText) {
                    btnText.textContent = 'Copied!';
                    setTimeout(() => {
                        btnText.textContent = 'Copy Report';
                    }, 2000);
                }
            });
        }

        // Close on escape key
        document.addEventListener('keydown', function (e) {
            if (e.key === 'Escape') {
                closeBatchInspector();
            }
        });

        // Close on outside backdrop click
        document.addEventListener('click', function (e) {
            const modal = document.getElementById('batch_inspector_modal');
            if (modal && e.target === modal) {
                closeBatchInspector();
            }
        });
    </script>
@endif


