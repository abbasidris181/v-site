<div class="relative inline-block text-left" id="theme-dropdown-container">
    <!-- Theme Toggle Trigger Button -->
    <button type="button"
            onclick="toggleThemeDropdown(event)"
            id="theme-toggle"
            data-action="toggleTheme()"
            title="Theme: Light, Dark, or System (Click to choose)"
            aria-label="Theme mode selection"
            aria-haspopup="true"
            class="p-2 rounded-xl bg-slate-100 hover:bg-slate-200 dark:bg-slate-800 dark:hover:bg-slate-700 text-slate-600 dark:text-slate-300 border border-slate-200/80 dark:border-slate-700/80 transition-all duration-200 cursor-pointer flex items-center justify-center shadow-xs active:scale-95 group">
        
        <!-- Sun Icon (Light Mode Active) -->
        <svg id="theme-icon-light" class="w-4 h-4 text-amber-500 hidden transition-transform group-hover:rotate-45" fill="none" viewBox="0 0 24 24" stroke="currentColor">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 3v1m0 16v1m9-9h-1M4 12H3m15.364 6.364l-.707-.707M6.343 6.343l-.707-.707m12.728 0l-.707.707M6.343 17.657l-.707.707M16 12a4 4 0 11-8 0 4 4 0 018 0z" />
        </svg>

        <!-- Moon Icon (Dark Mode Active) -->
        <svg id="theme-icon-dark" class="w-4 h-4 text-indigo-400 hidden transition-transform group-hover:-rotate-12" fill="none" viewBox="0 0 24 24" stroke="currentColor">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M20.354 15.354A9 9 0 018.646 3.646 9.003 9.003 0 0012 21a9.003 9.003 0 008.354-5.646z" />
        </svg>

        <!-- System / Desktop Icon (System Mode Active) -->
        <svg id="theme-icon-system" class="w-4 h-4 text-slate-600 dark:text-slate-300 hidden transition-transform group-hover:scale-110" fill="none" viewBox="0 0 24 24" stroke="currentColor">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9.75 17L9 20l-1 1h8l-1-1-.75-3M3 13h18M5 17h14a2 2 0 002-2V5a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z" />
        </svg>
    </button>

    <!-- Modern Dropdown Menu: Light, Dark, System -->
    <div id="theme-dropdown-menu"
         class="hidden absolute right-0 mt-2 w-44 rounded-2xl bg-white dark:bg-slate-900 border border-slate-200/90 dark:border-slate-800 shadow-2xl py-1.5 z-50 text-xs transition-all backdrop-blur-md origin-top-right">
        
        <div class="px-3 py-1.5 text-[10px] font-bold uppercase tracking-wider text-slate-400 dark:text-slate-500 border-b border-slate-100 dark:border-slate-800/80 mb-1 flex items-center justify-between">
            <span>Theme Mode</span>
            <span id="current-theme-badge" class="font-semibold text-[9px] lowercase bg-slate-100 dark:bg-slate-800 px-1.5 py-0.5 rounded-md text-slate-500 dark:text-slate-400">system</span>
        </div>

        <!-- 1. Light Option -->
        <button type="button" 
                onclick="setTheme('light')"
                id="theme-opt-light"
                class="w-full flex items-center justify-between px-3 py-2 text-slate-700 dark:text-slate-300 hover:bg-slate-100 dark:hover:bg-slate-800 transition-colors font-medium cursor-pointer">
            <span class="flex items-center gap-2.5">
                <svg class="w-4 h-4 text-amber-500 flex-shrink-0" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 3v1m0 16v1m9-9h-1M4 12H3m15.364 6.364l-.707-.707M6.343 6.343l-.707-.707m12.728 0l-.707.707M6.343 17.657l-.707.707M16 12a4 4 0 11-8 0 4 4 0 018 0z" />
                </svg>
                <span>Light</span>
            </span>
            <svg class="w-3.5 h-3.5 text-emerald-500 hidden checkmark-icon" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="3">
                <path stroke-linecap="round" stroke-linejoin="round" d="M5 13l4 4L19 7" />
            </svg>
        </button>

        <!-- 2. Dark Option -->
        <button type="button" 
                onclick="setTheme('dark')"
                id="theme-opt-dark"
                class="w-full flex items-center justify-between px-3 py-2 text-slate-700 dark:text-slate-300 hover:bg-slate-100 dark:hover:bg-slate-800 transition-colors font-medium cursor-pointer">
            <span class="flex items-center gap-2.5">
                <svg class="w-4 h-4 text-indigo-400 flex-shrink-0" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M20.354 15.354A9 9 0 018.646 3.646 9.003 9.003 0 0012 21a9.003 9.003 0 008.354-5.646z" />
                </svg>
                <span>Dark</span>
            </span>
            <svg class="w-3.5 h-3.5 text-emerald-500 hidden checkmark-icon" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="3">
                <path stroke-linecap="round" stroke-linejoin="round" d="M5 13l4 4L19 7" />
            </svg>
        </button>

        <!-- 3. System Option -->
        <button type="button" 
                onclick="setTheme('system')"
                id="theme-opt-system"
                class="w-full flex items-center justify-between px-3 py-2 text-slate-700 dark:text-slate-300 hover:bg-slate-100 dark:hover:bg-slate-800 transition-colors font-medium cursor-pointer">
            <span class="flex items-center gap-2.5">
                <svg class="w-4 h-4 text-slate-500 dark:text-slate-400 flex-shrink-0" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9.75 17L9 20l-1 1h8l-1-1-.75-3M3 13h18M5 17h14a2 2 0 002-2V5a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z" />
                </svg>
                <span>System</span>
            </span>
            <svg class="w-3.5 h-3.5 text-emerald-500 hidden checkmark-icon" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="3">
                <path stroke-linecap="round" stroke-linejoin="round" d="M5 13l4 4L19 7" />
            </svg>
        </button>
    </div>
</div>

<script>
    (function() {
        window.applyTheme = function(theme) {
            const mode = theme || localStorage.getItem('theme') || 'system';
            const systemPrefersDark = window.matchMedia && window.matchMedia('(prefers-color-scheme: dark)').matches;
            const shouldBeDark = mode === 'dark' || (mode === 'system' && systemPrefersDark);

            if (shouldBeDark) {
                document.documentElement.classList.add('dark');
            } else {
                document.documentElement.classList.remove('dark');
            }

            // Update active icon on the toggle button
            const iconLight = document.getElementById('theme-icon-light');
            const iconDark = document.getElementById('theme-icon-dark');
            const iconSystem = document.getElementById('theme-icon-system');

            if (iconLight) iconLight.classList.add('hidden');
            if (iconDark) iconDark.classList.add('hidden');
            if (iconSystem) iconSystem.classList.add('hidden');

            if (mode === 'light' && iconLight) {
                iconLight.classList.remove('hidden');
            } else if (mode === 'dark' && iconDark) {
                iconDark.classList.remove('hidden');
            } else if (iconSystem) {
                iconSystem.classList.remove('hidden');
            }

            // Update badge text
            const badge = document.getElementById('current-theme-badge');
            if (badge) {
                badge.textContent = mode;
            }

            // Update active state in the dropdown menu items
            ['light', 'dark', 'system'].forEach(opt => {
                const btn = document.getElementById('theme-opt-' + opt);
                if (btn) {
                    const check = btn.querySelector('.checkmark-icon');
                    if (opt === mode) {
                        btn.classList.add('bg-emerald-50', 'dark:bg-emerald-500/10', 'text-emerald-600', 'dark:text-emerald-400', 'font-bold');
                        btn.classList.remove('text-slate-700', 'dark:text-slate-300');
                        if (check) check.classList.remove('hidden');
                    } else {
                        btn.classList.remove('bg-emerald-50', 'dark:bg-emerald-500/10', 'text-emerald-600', 'dark:text-emerald-400', 'font-bold');
                        btn.classList.add('text-slate-700', 'dark:text-slate-300');
                        if (check) check.classList.add('hidden');
                    }
                }
            });
        };

        window.setTheme = function(theme) {
            localStorage.setItem('theme', theme);
            window.applyTheme(theme);
            window.closeThemeDropdown();
            
            if (window.showToast) {
                const labels = {
                    light: 'Light Mode',
                    dark: 'Dark Mode',
                    system: 'System Mode (Automatic)'
                };
                window.showToast({ 
                    type: 'info', 
                    title: 'Theme Preference', 
                    message: (labels[theme] || theme) + ' enabled.' 
                });
            }
        };

        window.toggleTheme = function() {
            // Cycle between: system -> light -> dark -> system
            const current = localStorage.getItem('theme') || 'system';
            const next = current === 'system' ? 'light' : (current === 'light' ? 'dark' : 'system');
            window.setTheme(next);
        };

        window.toggleThemeDropdown = function(e) {
            if (e) e.stopPropagation();
            const menu = document.getElementById('theme-dropdown-menu');
            if (menu) {
                menu.classList.toggle('hidden');
            }
        };

        window.closeThemeDropdown = function() {
            const menu = document.getElementById('theme-dropdown-menu');
            if (menu) {
                menu.classList.add('hidden');
            }
        };

        // Dismiss dropdown on outside click
        document.addEventListener('click', function(e) {
            const container = document.getElementById('theme-dropdown-container');
            if (container && !container.contains(e.target)) {
                window.closeThemeDropdown();
            }
        });

        // Dismiss dropdown on Escape key
        document.addEventListener('keydown', function(e) {
            if (e.key === 'Escape') {
                window.closeThemeDropdown();
            }
        });

        // Real-time listener for OS preference changes
        if (window.matchMedia) {
            window.matchMedia('(prefers-color-scheme: dark)').addEventListener('change', function() {
                const current = localStorage.getItem('theme') || 'system';
                if (current === 'system') {
                    window.applyTheme('system');
                }
            });
        }

        // Initial sync once DOM is ready
        if (document.readyState === 'loading') {
            document.addEventListener('DOMContentLoaded', function() {
                window.applyTheme(localStorage.getItem('theme') || 'system');
            });
        } else {
            window.applyTheme(localStorage.getItem('theme') || 'system');
        }
    })();
</script>
