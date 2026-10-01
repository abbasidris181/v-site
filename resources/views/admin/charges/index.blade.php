@extends('layouts.admin')

@section('content')
<div class="space-y-6">



    <!-- ==========================================
         VALIDATION ERRORS
         ========================================== -->
    @if ($errors->any())
        <div class="p-4 rounded-2xl bg-red-50 dark:bg-red-500/10 border border-red-200 dark:border-red-500/30 text-sm text-red-800 dark:text-red-300 shadow-xs">
            <div class="font-bold flex items-center gap-2 mb-1">
                <svg class="w-5 h-5 text-red-500 flex-shrink-0" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4m0 4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
                </svg>
                <span>Please correct the errors before saving:</span>
            </div>
            <ul class="list-disc pl-7 space-y-1 text-xs">
                @foreach ($errors->all() as $error)
                    <li>{{ $error }}</li>
                @endforeach
            </ul>
        </div>
    @endif

    <!-- ==========================================
         PRICING & CHARGES FORM
         ========================================== -->
    <form method="POST" action="{{ route('admin.charges.update') }}" class="space-y-6">
        @csrf
        @method('PUT')

        @if(isset($activeServiceSlug) && $activeServiceSlug)
            <input type="hidden" name="service" value="{{ $activeServiceSlug }}">
        @endif

        <div class="rounded-2xl bg-white dark:bg-slate-900 border border-slate-200/80 dark:border-slate-800 shadow-sm overflow-hidden">
            <!-- Table Header Info Bar -->
            <div class="p-5 sm:p-6 border-b border-slate-100 dark:border-slate-800 bg-slate-50/50 dark:bg-slate-800/30 flex items-center justify-between">
                <div class="flex items-center gap-3">
                    <div class="w-10 h-10 rounded-xl bg-gradient-to-tr from-indigo-600 to-violet-600 text-white flex items-center justify-center font-bold text-sm shadow-md shadow-indigo-500/20">
                        ₦
                    </div>
                    <div>
                        <h2 class="text-base font-extrabold text-slate-900 dark:text-white">
                            Pricing &amp; Tariff Management
                        </h2>
                        <p class="text-xs text-slate-500 dark:text-slate-400 mt-0.5">
                            Manage live amounts, operational actions (enable/disable), and real-time status.
                        </p>
                    </div>
                </div>
                <span class="text-xs font-bold text-slate-500 dark:text-slate-400">
                    {{ $allServices->count() }} Services Listed
                </span>
            </div>

            <!-- Unified Pricing Table -->
            <div class="overflow-x-auto">
                <table class="min-w-full divide-y divide-slate-200 dark:divide-slate-800 text-left text-xs">
                    <thead class="bg-slate-50 dark:bg-slate-950/60 text-slate-500 dark:text-slate-400 uppercase font-bold text-xs tracking-wider">
                        <tr>
                            <th scope="col" class="px-3 py-3 sm:px-4 sm:py-3.5 text-center w-14">SN</th>
                            <th scope="col" class="px-3 py-3 sm:px-5 sm:py-3.5">NAME</th>
                            <th scope="col" class="px-3 py-3 sm:px-5 sm:py-3.5">CATEGORY</th>
                            <th scope="col" class="px-3 py-3 sm:px-5 sm:py-3.5 w-36 sm:w-44">AMOUNT</th>
                            <th scope="col" class="px-3 py-3 sm:px-5 sm:py-3.5 w-32 sm:w-36">ACTION</th>
                            <th scope="col" class="px-3 py-3 sm:px-5 sm:py-3.5 text-center w-28 sm:w-32">STATUS</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-slate-100 dark:divide-slate-800/80">
                        @foreach($allServices as $svc)
                            @php
                                $isServiceActive = old('services.' . $svc->id . '.action') !== null 
                                     ? old('services.' . $svc->id . '.action') === 'enable'
                                    : (bool)$svc->is_active;
                                $currentAction = $isServiceActive ? 'enable' : 'disable';
                            @endphp
                            <tr class="hover:bg-slate-50/80 dark:hover:bg-slate-800/40 transition-colors">
                                <!-- SN -->
                                <td class="px-3 py-3.5 sm:px-4 sm:py-4 text-center font-bold text-xs text-slate-500 dark:text-slate-400">
                                    {{ $loop->iteration }}
                                </td>

                                <!-- NAME -->
                                <td class="px-3 py-3.5 sm:px-5 sm:py-4 font-bold text-sm text-slate-900 dark:text-white">
                                    {{ $svc->name }}
                                </td>

                                <!-- CATEGORY -->
                                <td class="px-3 py-3.5 sm:px-5 sm:py-4 text-xs font-semibold text-slate-600 dark:text-slate-300">
                                    <span class="inline-flex items-center px-2.5 py-1 rounded-lg bg-slate-100 dark:bg-slate-800/70 text-slate-700 dark:text-slate-300 font-medium">
                                        {{ $svc->category->name ?? 'General' }}
                                    </span>
                                </td>

                                <!-- AMOUNT -->
                                <td class="px-3 py-3.5 sm:px-5 sm:py-4">
                                    <div class="relative w-full max-w-[140px] sm:max-w-[170px] rounded-xl shadow-xs">
                                        <div class="pointer-events-none absolute inset-y-0 left-0 flex items-center pl-2.5 sm:pl-3">
                                            <span class="text-slate-400 font-bold text-xs sm:text-sm">₦</span>
                                        </div>
                                        <input type="number"
                                               name="services[{{ $svc->id }}][price]"
                                               value="{{ old('services.' . $svc->id . '.price', number_format((float)$svc->price, 2, '.', '')) }}"
                                               step="0.01"
                                               min="0"
                                               required
                                               class="block w-full min-w-0 rounded-xl border border-slate-300 dark:border-slate-700 bg-white dark:bg-slate-800 py-1.5 sm:py-2 pl-6 sm:pl-7 pr-2.5 sm:pr-3 text-xs sm:text-sm font-bold text-slate-900 dark:text-white focus:border-indigo-500 focus:ring-2 focus:ring-indigo-500/20 transition">
                                    </div>
                                </td>

                                <!-- ACTION -->
                                <td class="px-3 py-3.5 sm:px-5 sm:py-4">
                                    <div class="relative w-full max-w-[120px] sm:max-w-[140px]">
                                        <select name="services[{{ $svc->id }}][action]"
                                                id="action-select-{{ $svc->id }}"
                                                data-service-id="{{ $svc->id }}"
                                                onchange="updateServiceStatus(this, '{{ $svc->id }}')"
                                                class="action-selector block w-full rounded-xl border border-slate-300 dark:border-slate-700 bg-white dark:bg-slate-800 py-1.5 sm:py-2 px-2.5 sm:px-3 text-xs sm:text-sm font-semibold text-slate-900 dark:text-white focus:border-indigo-500 focus:ring-2 focus:ring-indigo-500/20 transition cursor-pointer">
                                            <option value="enable" {{ $currentAction === 'enable' ? 'selected' : '' }}>Enable</option>
                                            <option value="disable" {{ $currentAction === 'disable' ? 'selected' : '' }}>Disable</option>
                                        </select>
                                    </div>
                                </td>

                                <!-- STATUS -->
                                <td class="px-3 py-3.5 sm:px-5 sm:py-4 text-center">
                                    <span id="status-display-{{ $svc->id }}"
                                          class="status-badge inline-flex items-center px-2.5 py-1 rounded-full text-xs font-bold transition-all {{ $currentAction === 'enable' ? 'bg-emerald-50 dark:bg-emerald-500/10 text-emerald-700 dark:text-emerald-400 border border-emerald-200 dark:border-emerald-500/20' : 'bg-red-50 dark:bg-red-500/10 text-red-700 dark:text-red-400 border border-red-200 dark:border-red-500/20' }}">
                                        {{ $currentAction === 'enable' ? 'Enable' : 'Disable' }}
                                    </span>
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        </div>

        <!-- Action Bar -->
        <div class="flex flex-col sm:flex-row items-stretch sm:items-center justify-between gap-3 p-4 rounded-2xl bg-white dark:bg-slate-900 border border-slate-200/80 dark:border-slate-800 shadow-sm">
            <div class="text-xs text-slate-500 dark:text-slate-400">
                All prices and operational status updates are applied immediately across customer transactions and wallet checkout deductions upon saving.
            </div>
            <button type="submit"
                    class="inline-flex items-center justify-center gap-2 px-6 py-2.5 rounded-xl text-xs sm:text-sm font-bold text-white bg-gradient-to-r from-indigo-600 via-indigo-600 to-violet-600 hover:from-indigo-500 hover:to-violet-500 shadow-md shadow-indigo-600/25 transition-all cursor-pointer">
                <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7" />
                </svg>
                <span>Save</span>
            </button>
        </div>
    </form>

</div>

<script>
    function updateServiceStatus(selectEl, serviceId) {
        const statusDisplay = document.getElementById('status-display-' + serviceId);
        if (!statusDisplay) return;

        if (selectEl.value === 'enable') {
            statusDisplay.textContent = 'Enable';
            statusDisplay.className = 'status-badge inline-flex items-center px-2.5 py-1 rounded-full text-xs font-bold transition-all bg-emerald-50 dark:bg-emerald-500/10 text-emerald-700 dark:text-emerald-400 border border-emerald-200 dark:border-emerald-500/20';
        } else {
            statusDisplay.textContent = 'Disable';
            statusDisplay.className = 'status-badge inline-flex items-center px-2.5 py-1 rounded-full text-xs font-bold transition-all bg-red-50 dark:bg-red-500/10 text-red-700 dark:text-red-400 border border-red-200 dark:border-red-500/20';
        }
    }
</script>
@endsection
