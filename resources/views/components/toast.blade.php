<!-- ==========================================
     GLOBAL TOAST NOTIFICATION COMPONENT
     Displays floating toast notifications for Successful / Failed operations
     ========================================== -->
<div id="toast-container"
     class="fixed top-5 right-5 z-[9999] flex flex-col gap-3 max-w-sm sm:max-w-md w-full pointer-events-none px-4 sm:px-0"
     aria-live="polite"
     aria-atomic="true">
</div>

<!-- Reusable Toast Template -->
<template id="toast-template">
    <div class="toast-item pointer-events-auto rounded-2xl bg-white/95 dark:bg-slate-900/95 backdrop-blur-md border shadow-2xl overflow-hidden transform translate-x-12 opacity-0 transition-all duration-300 ease-out group"
         role="alert">
        <div class="p-4 sm:p-4.5 flex items-start gap-3.5">
            <!-- Icon Container -->
            <div class="toast-icon-wrap w-10 h-10 rounded-xl flex items-center justify-center flex-shrink-0 shadow-xs">
            </div>

            <!-- Content Area -->
            <div class="flex-1 min-w-0 pt-0.5">
                <div class="flex items-center gap-2 mb-1">
                    <span class="toast-badge px-2.5 py-0.5 rounded-full text-[10px] font-extrabold uppercase tracking-wider"></span>
                    <span class="toast-timestamp text-[10px] text-slate-400 dark:text-slate-500 font-medium">just now</span>
                </div>
                <h4 class="toast-title text-sm font-extrabold text-slate-900 dark:text-white leading-snug"></h4>
                <p class="toast-message text-xs text-slate-600 dark:text-slate-300 mt-1 leading-relaxed break-words"></p>
            </div>

            <!-- Close Button -->
            <button type="button"
                    class="toast-close-btn text-slate-400 hover:text-slate-700 dark:hover:text-slate-200 p-1.5 rounded-lg hover:bg-slate-100 dark:hover:bg-slate-800 transition cursor-pointer flex-shrink-0"
                    aria-label="Close notification">
                <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M6 18L18 6M6 6l12 12" />
                </svg>
            </button>
        </div>

        <!-- Progress Bar Indicator -->
        <div class="toast-progress-track h-1 w-full overflow-hidden">
            <div class="toast-progress-bar h-full w-full"></div>
        </div>
    </div>
</template>

<script>
    (function() {
        const container = document.getElementById('toast-container');
        const template = document.getElementById('toast-template');

        window.showToast = function(options) {
            if (typeof options === 'string') {
                options = { message: options, type: 'info' };
            }

            const type = (options.type || 'info').toLowerCase();
            const isSuccess = type === 'success' || type === 'successful';
            const isFailed = type === 'failed' || type === 'error' || type === 'failure';

            const defaultTitle = isSuccess ? 'Successful' : (isFailed ? 'Failed' : 'Notice');
            const title = options.title || defaultTitle;
            let message = options.message || '';
            message = message.replace(/\s*Tracking Reference:[^.]*(\.|\b)\s*(Fee:[^.]*(\.|\b))?/gi, '').trim();
            if (!message && isSuccess) {
                message = 'Request submitted successfully!';
            }
            const duration = options.duration || (isFailed ? 6500 : 5000);

            if (!container || !template) return;

            const clone = template.content.cloneNode(true);
            const toastEl = clone.querySelector('.toast-item');
            const iconWrap = clone.querySelector('.toast-icon-wrap');
            const badge = clone.querySelector('.toast-badge');
            const titleEl = clone.querySelector('.toast-title');
            const messageEl = clone.querySelector('.toast-message');
            const closeBtn = clone.querySelector('.toast-close-btn');
            const progressBar = clone.querySelector('.toast-progress-bar');
            const progressTrack = clone.querySelector('.toast-progress-track');

            titleEl.textContent = title;
            messageEl.textContent = message;

            if (isSuccess) {
                toastEl.classList.add('border-emerald-500/30', 'dark:border-emerald-500/40', 'shadow-emerald-500/10');
                iconWrap.classList.add('bg-emerald-100', 'dark:bg-emerald-500/20', 'text-emerald-600', 'dark:text-emerald-400');
                iconWrap.innerHTML = `
                    <svg class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2.5">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M5 13l4 4L19 7" />
                    </svg>
                `;
                badge.classList.add('bg-emerald-100', 'dark:bg-emerald-500/20', 'text-emerald-700', 'dark:text-emerald-300');
                badge.textContent = 'Successful';
                progressBar.classList.add('bg-emerald-500');
                progressTrack.classList.add('bg-emerald-50', 'dark:bg-emerald-950/40');
            } else if (isFailed) {
                toastEl.classList.add('border-rose-500/30', 'dark:border-rose-500/40', 'shadow-rose-500/10');
                iconWrap.classList.add('bg-rose-100', 'dark:bg-rose-500/20', 'text-rose-600', 'dark:text-rose-400');
                iconWrap.innerHTML = `
                    <svg class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2.5">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M6 18L18 6M6 6l12 12" />
                    </svg>
                `;
                badge.classList.add('bg-rose-100', 'dark:bg-rose-500/20', 'text-rose-700', 'dark:text-rose-300');
                badge.textContent = 'Failed';
                progressBar.classList.add('bg-rose-500');
                progressTrack.classList.add('bg-rose-50', 'dark:bg-rose-950/40');
            } else {
                toastEl.classList.add('border-blue-500/30', 'dark:border-blue-500/40', 'shadow-blue-500/10');
                iconWrap.classList.add('bg-blue-100', 'dark:bg-blue-500/20', 'text-blue-600', 'dark:text-blue-400');
                iconWrap.innerHTML = `
                    <svg class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
                    </svg>
                `;
                badge.classList.add('bg-blue-100', 'dark:bg-blue-500/20', 'text-blue-700', 'dark:text-blue-300');
                badge.textContent = 'Notice';
                progressBar.classList.add('bg-blue-500');
                progressTrack.classList.add('bg-blue-50', 'dark:bg-blue-950/40');
            }

            container.appendChild(toastEl);

            // Animate In
            requestAnimationFrame(() => {
                toastEl.classList.remove('translate-x-12', 'opacity-0');
                toastEl.classList.add('translate-x-0', 'opacity-100');
            });

            // Timer & Progress Animation
            let startTime = Date.now();
            let remaining = duration;
            let timerId = null;
            let isPaused = false;

            const updateProgress = () => {
                if (!isPaused) {
                    const elapsed = Date.now() - startTime;
                    const percent = Math.max(0, 100 - (elapsed / duration) * 100);
                    progressBar.style.width = percent + '%';
                }
            };

            const intervalId = setInterval(updateProgress, 30);

            const removeToast = () => {
                clearInterval(intervalId);
                if (timerId) clearTimeout(timerId);
                toastEl.classList.remove('translate-x-0', 'opacity-100');
                toastEl.classList.add('translate-x-12', 'opacity-0', 'scale-95');
                setTimeout(() => {
                    if (toastEl.parentNode) toastEl.parentNode.removeChild(toastEl);
                }, 350);
            };

            const startTimer = () => {
                startTime = Date.now() - (duration - remaining);
                timerId = setTimeout(removeToast, remaining);
            };

            startTimer();

            // Pause on hover, resume on leave
            toastEl.addEventListener('mouseenter', () => {
                isPaused = true;
                clearTimeout(timerId);
                remaining -= (Date.now() - startTime);
            });

            toastEl.addEventListener('mouseleave', () => {
                isPaused = false;
                startTimer();
            });

            closeBtn.addEventListener('click', removeToast);
        };

        window.showSuccessToast = function(title, message, duration) {
            window.showToast({ type: 'success', title: title || 'Successful', message, duration });
        };

        window.showFailedToast = function(title, message, duration) {
            window.showToast({ type: 'failed', title: title || 'Failed', message, duration });
        };
    })();
</script>

<!-- Server Flash Messages Auto-Display -->
@if (session('success') || session('status'))
    <script>
        document.addEventListener('DOMContentLoaded', function() {
            window.showToast({
                type: 'success',
                title: 'Successful',
                message: @json(session('success') ?? session('status')),
                duration: 5500
            });
        });
    </script>
@endif

@if (session('error') || session('failed') || session('failure'))
    <script>
        document.addEventListener('DOMContentLoaded', function() {
            window.showToast({
                type: 'failed',
                title: 'Failed',
                message: @json(session('error') ?? session('failed') ?? session('failure')),
                duration: 6500
            });
        });
    </script>
@elseif ($errors->any())
    <script>
        document.addEventListener('DOMContentLoaded', function() {
            window.showToast({
                type: 'failed',
                title: 'Failed',
                message: @json($errors->first()),
                duration: 6500
            });
        });
    </script>
@endif
