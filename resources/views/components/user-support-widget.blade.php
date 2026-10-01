<!-- ==========================================
     WHATSAPP-STYLE LIVE CUSTOMER SUPPORT & CALLS WIDGET
     ========================================== -->
    <!-- WhatsApp-Style Support Chat & Calling Modal / Drawer (Triggered Exclusively from Sidebar Live Chat Item) -->
    <div id="support-modal-backdrop"
         onclick="toggleSupportChat(false)"
         class="fixed inset-0 z-50 bg-slate-900/60 backdrop-blur-xs transition-opacity duration-300 opacity-0 pointer-events-none">
    </div>

    <div id="support-modal-panel"
         class="fixed inset-y-0 right-0 sm:top-auto sm:bottom-6 sm:right-6 sm:inset-y-auto sm:h-[620px] w-full sm:w-[420px] z-50 bg-white dark:bg-slate-900 sm:rounded-3xl shadow-2xl border border-slate-200/80 dark:border-slate-800 flex flex-col transform translate-y-full sm:translate-y-0 sm:scale-90 sm:opacity-0 pointer-events-none transition-all duration-300 ease-out overflow-hidden">

        <!-- A. WhatsApp Header: Attending Admin / Support Name & Online Status -->
        <div class="px-4 py-3.5 bg-[#075E54] text-white flex items-center justify-between gap-3 shadow-md flex-shrink-0">
            <div class="flex items-center gap-3 min-w-0">
                <!-- Back / Close (Mobile) -->
                <button type="button"
                        onclick="toggleSupportChat(false)"
                        class="p-1 rounded-full text-white/80 hover:text-white hover:bg-white/10 transition">
                    <svg class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7" />
                    </svg>
                </button>

                <!-- Avatar with Online Dot -->
                <div class="relative flex-shrink-0">
                    <div id="chat-header-avatar" class="w-10 h-10 rounded-full bg-[#128C7E] text-white text-sm font-bold flex items-center justify-center ring-2 ring-white/30">
                        S
                    </div>
                    <span id="chat-header-dot" class="absolute bottom-0 right-0 w-3 h-3 rounded-full bg-slate-400 ring-2 ring-[#075E54]"></span>
                </div>

                <!-- Name Side: Admin Name & WhatsApp Presence Format -->
                <div class="min-w-0">
                    <div class="flex items-center gap-1.5">
                        <h3 id="chat-header-name" class="text-sm font-bold text-white truncate">
                            Customer Support Desk
                        </h3>
                        <!-- Verified Staff Badge -->
                        <svg id="chat-header-verified" class="hidden w-3.5 h-3.5 text-emerald-300 flex-shrink-0" fill="currentColor" viewBox="0 0 20 20">
                            <path fill-rule="evenodd" d="M6.267 3.455a3.066 3.066 0 001.745-.723 3.066 3.066 0 013.976 0 3.066 3.066 0 001.745.723 3.066 3.066 0 012.812 2.812c.051.643.304 1.254.723 1.745a3.066 3.066 0 010 3.976 3.066 3.066 0 00-.723 1.745 3.066 3.066 0 01-2.812 2.812 3.066 3.066 0 00-1.745.723 3.066 3.066 0 01-3.976 0 3.066 3.066 0 00-1.745-.723 3.066 3.066 0 01-2.812-2.812 3.066 3.066 0 00-.723-1.745 3.066 3.066 0 010-3.976 3.066 3.066 0 00.723-1.745 3.066 3.066 0 012.812-2.812zm7.44 5.252a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd" />
                        </svg>
                    </div>
                    <!-- Presence status: "online" or "offline (last seen ...)" -->
                    <div id="chat-header-status" class="text-[11px] text-emerald-200/90 font-medium truncate">
                        Checking status...
                    </div>
                </div>
            </div>

            <!-- WhatsApp Voice Call Action Button -->
            <div class="flex items-center gap-1">
                <button type="button"
                        onclick="initiateUserSupportCall()"
                        id="header-call-btn"
                        title="Voice Call Support"
                        class="p-2 rounded-full text-white/90 hover:text-white hover:bg-white/10 transition cursor-pointer">
                    <!-- Call Handset Icon -->
                    <svg class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 5a2 2 0 012-2h3.28a1 1 0 01.948.684l1.498 4.493a1 1 0 01-.502 1.21l-2.257 1.13a11.042 11.042 0 005.516 5.516l1.13-2.257a1 1 0 011.21-.502l4.493 1.498a1 1 0 01.684.949V19a2 2 0 01-2 2h-1C9.716 21 3 14.284 3 6V5z" />
                    </svg>
                </button>
                <button type="button"
                        onclick="toggleSupportChat(false)"
                        title="Close"
                        class="p-2 rounded-full text-white/90 hover:text-white hover:bg-white/10 transition cursor-pointer">
                    <svg class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
                    </svg>
                </button>
            </div>
        </div>

        <!-- Attending Admin Notification Banner (when claimed) -->
        <div id="attending-admin-banner" class="hidden px-4 py-2 bg-emerald-50 dark:bg-emerald-950/40 border-b border-emerald-100 dark:border-emerald-800/60 text-xs text-emerald-800 dark:text-emerald-300 flex items-center justify-between">
            <div class="flex items-center gap-2">
                <span class="w-2 h-2 rounded-full bg-emerald-500 animate-pulse"></span>
                <span>You are currently chatting with <strong id="banner-admin-name">Support Staff</strong></span>
            </div>
        </div>

        <!-- B. WhatsApp Chat Stream Body -->
        <div id="user-chat-messages" class="flex-1 overflow-y-auto p-4 space-y-3 bg-[#efeae2] dark:bg-slate-950/90 text-slate-800 dark:text-slate-100">
            <!-- WhatsApp Date Stamp -->
            <div class="flex justify-center my-1">
                <span class="px-3 py-1 rounded-lg bg-white/80 dark:bg-slate-800/80 text-[10px] font-bold text-slate-500 dark:text-slate-400 shadow-2xs">
                    TODAY
                </span>
            </div>

            <div id="chat-loading-placeholder" class="text-center py-6 text-xs text-slate-400">
                Connecting to live support desk...
            </div>
        </div>

        <!-- C. WhatsApp Input Form -->
        <div class="p-3 bg-slate-100 dark:bg-slate-900 border-t border-slate-200 dark:border-slate-800 flex items-center gap-2 flex-shrink-0">
            <input type="text"
                   id="user-support-input"
                   placeholder="Type a message..."
                   onkeydown="if(event.key === 'Enter') sendUserSupportMessage()"
                   class="flex-1 px-4 py-2.5 rounded-full bg-white dark:bg-slate-800 border border-slate-300 dark:border-slate-700 text-xs sm:text-sm text-slate-900 dark:text-white focus:outline-hidden focus:ring-2 focus:ring-emerald-500 shadow-2xs" />

            <button type="button"
                    onclick="sendUserSupportMessage()"
                    id="user-support-send-btn"
                    class="w-10 h-10 rounded-full bg-emerald-600 hover:bg-emerald-500 text-white flex items-center justify-center transition shadow-md shadow-emerald-600/30 cursor-pointer">
                <svg class="w-4 h-4 transform rotate-90" fill="currentColor" viewBox="0 0 20 20">
                    <path d="M10.894 2.553a1 1 0 00-1.788 0l-7 14a1 1 0 001.169 1.409l5-1.429A1 1 0 009 15.571V11a1 1 0 112 0v4.571a1 1 0 00.725.962l5 1.428a1 1 0 001.17-1.408l-7-14z" />
                </svg>
            </button>
        </div>
    </div>

    <!-- 3. WhatsApp-Style Voice Call Screen Overlay with Loudspeaker Sound Engine -->
    <div id="user-call-overlay" class="fixed inset-0 z-50 flex items-center justify-center p-4 bg-slate-950/85 backdrop-blur-md hidden">
        <div class="w-full max-w-sm rounded-3xl bg-gradient-to-b from-[#075E54] to-slate-900 text-white p-8 text-center space-y-5 shadow-2xl border border-white/10 animate-fade-in">
            <!-- Calling status header -->
            <div>
                <div class="inline-flex items-center gap-1.5 px-3 py-1 rounded-full text-[10px] font-extrabold uppercase tracking-widest bg-emerald-500/20 text-emerald-300 border border-emerald-400/30 mb-1">
                    <svg class="w-3.5 h-3.5 text-emerald-300 animate-pulse" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 5a2 2 0 012-2h3.28a1 1 0 01.948.684l1.498 4.493a1 1 0 01-.502 1.21l-2.257 1.13a11.042 11.042 0 005.516 5.516l1.13-2.257a1 1 0 011.21-.502l4.493 1.498a1 1 0 01.684.949V19a2 2 0 01-2 2h-1C9.716 21 3 14.284 3 6V5z" />
                    </svg>
                    <span>Voice Call Active</span>
                </div>
                <div id="user-call-timer" class="text-xs font-mono text-emerald-200 mt-1">
                    00:00
                </div>
            </div>

            <!-- Pulsing Avatar & Audio Indicator -->
            <div class="relative w-28 h-28 mx-auto my-2">
                <div id="user-call-pulsing-ring" class="absolute inset-0 rounded-full bg-emerald-500/20 animate-ping"></div>
                <div id="user-call-avatar" class="relative w-28 h-28 rounded-full bg-emerald-600 text-white text-3xl font-extrabold flex items-center justify-center ring-4 ring-emerald-400/40 shadow-xl">
                    S
                </div>
            </div>

            <!-- Speaking Soundwave Equalizer (Visible when admin speaks) -->
            <div id="user-call-equalizer" class="hidden flex items-center justify-center gap-1.5 my-2">
                <span class="w-1.5 h-4 bg-emerald-400 rounded-full animate-bounce"></span>
                <span class="w-1.5 h-7 bg-emerald-300 rounded-full animate-bounce [animation-delay:0.15s]"></span>
                <span class="w-1.5 h-5 bg-emerald-400 rounded-full animate-bounce [animation-delay:0.3s]"></span>
                <span class="w-1.5 h-8 bg-emerald-200 rounded-full animate-bounce [animation-delay:0.1s]"></span>
                <span class="w-1.5 h-4 bg-emerald-400 rounded-full animate-bounce [animation-delay:0.25s]"></span>
            </div>

            <!-- Attending Admin Name & Role -->
            <div>
                <h3 id="user-call-admin-name" class="text-xl font-bold text-white tracking-tight">
                    Customer Support
                </h3>
                <p id="user-call-admin-role" class="text-xs font-medium text-emerald-200 mt-1">
                    Connecting to available staff...
                </p>
                <div id="user-call-status-label" class="mt-2 inline-flex items-center gap-1.5 px-3 py-1 rounded-full text-xs font-semibold bg-emerald-500/20 text-emerald-300">
                    <span class="w-2 h-2 rounded-full bg-emerald-400 animate-pulse"></span>
                    <span>Ringing with Loudspeaker...</span>
                </div>
            </div>

            <!-- Call Controls -->
            <div class="flex items-center justify-center gap-5 pt-3">
                <!-- Mute Microphone Button -->
                <button type="button"
                        onclick="toggleUserMuteCall()"
                        id="user-call-mute-btn"
                        title="Toggle Microphone"
                        class="w-12 h-12 rounded-full bg-white/10 hover:bg-white/20 text-white flex items-center justify-center transition cursor-pointer">
                    <svg id="user-mic-icon" class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 11a7 7 0 01-7 7m0 0a7 7 0 01-7-7m7 7v4m0 0H8m4 0h4m-4-8a3 3 0 01-3-3V5a3 3 0 116 0v6a3 3 0 01-3 3z" />
                    </svg>
                </button>

                <!-- Loudspeaker Audio Output Toggle -->
                <button type="button"
                        onclick="toggleLoudspeaker()"
                        id="user-call-speaker-btn"
                        title="Toggle Loudspeaker Output"
                        class="w-12 h-12 rounded-full bg-white/10 hover:bg-white/20 text-emerald-300 flex items-center justify-center transition cursor-pointer">
                    <svg class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15.536 8.464a5 5 0 010 7.072m2.828-9.9a9 9 0 010 12.728M5.586 15H4a1 1 0 01-1-1v-4a1 1 0 011-1h1.586l4.707-4.707C10.923 3.663 12 4.109 12 5v14c0 .891-1.077 1.337-1.707.707L5.586 15z" />
                    </svg>
                </button>

                <!-- End Call Button (Red Phone) -->
                <button type="button"
                        onclick="endUserSupportCall()"
                        title="End Call"
                        class="w-14 h-14 rounded-full bg-red-600 hover:bg-red-500 text-white flex items-center justify-center shadow-lg shadow-red-600/40 transition hover:scale-110 active:scale-95 cursor-pointer">
                    <svg class="w-6 h-6 transform rotate-135" fill="currentColor" viewBox="0 0 20 20">
                        <path d="M2 3a1 1 0 011-1h2.153a1 1 0 01.986.836l.74 4.435a1 1 0 01-.54 1.06l-1.548.773a11.037 11.037 0 006.105 6.105l.774-1.548a1 1 0 011.059-.54l4.435.74a1 1 0 01.836.986V17a1 1 0 01-1 1h-2C7.82 18 2 12.18 2 4V3z" />
                    </svg>
                </button>
            </div>

            <!-- Test / Sandbox Pick Up Helper -->
            <div class="pt-1 border-t border-white/10">
                <button type="button"
                        id="simulate-admin-answer-btn"
                        onclick="simulateAdminAnswerCall()"
                        class="px-3 py-1 rounded-xl text-[11px] font-bold text-amber-200 bg-amber-500/20 hover:bg-amber-500/30 border border-amber-400/30 transition cursor-pointer">
                    Simulate Admin Pick Up (Test)
                </button>
            </div>
        </div>
    </div>

<!-- ==========================================
     CLIENT JAVASCRIPT ENGINE FOR SUPPORT CHAT & CALLS
     ========================================== -->
<script>
    let supportChatOpen = false;
    let activeSupportRoomId = null;
    let currentSupportCallId = null;
    let supportCallTimerInterval = null;
    let supportCallDurationSecs = 0;
    let supportLocalStream = null;
    let supportIsMuted = false;
    let supportPresencePollInterval = null;
    let supportMessagePollInterval = null;
    let supportCallPollInterval = null;

    // Toggle Chat Drawer / Modal
    function toggleSupportChat(open) {
        supportChatOpen = open;
        const backdrop = document.getElementById('support-modal-backdrop');
        const panel = document.getElementById('support-modal-panel');

        if (open) {
            backdrop.classList.remove('opacity-0', 'pointer-events-none');
            backdrop.classList.add('opacity-100');
            panel.classList.remove('translate-y-full', 'sm:scale-90', 'sm:opacity-0', 'pointer-events-none');
            panel.classList.add('translate-y-0', 'sm:scale-100', 'sm:opacity-100');
            loadSupportMessages();
            startMessagePolling();
        } else {
            backdrop.classList.add('opacity-0', 'pointer-events-none');
            backdrop.classList.remove('opacity-100');
            panel.classList.add('translate-y-full', 'sm:scale-90', 'sm:opacity-0', 'pointer-events-none');
            panel.classList.remove('translate-y-0', 'sm:scale-100', 'sm:opacity-100');
            stopMessagePolling();
        }
    }

    // WhatsApp Presence Update Handler
    function updateWhatsAppPresenceUI(presence) {
        const headerName = document.getElementById('chat-header-name');
        const headerStatus = document.getElementById('chat-header-status');
        const headerDot = document.getElementById('chat-header-dot');
        const headerAvatar = document.getElementById('chat-header-avatar');
        const verifiedBadge = document.getElementById('chat-header-verified');
        const adminBanner = document.getElementById('attending-admin-banner');
        const bannerAdminName = document.getElementById('banner-admin-name');

        // Sidebar elements (Exclusively handles chat trigger, presence, and badge)
        const sidebarDot = document.getElementById('sidebar-chat-status-dot');
        const sidebarText = document.getElementById('sidebar-chat-status-text');

        const isOnline = presence.is_online;
        const statusText = presence.status_text; // e.g. "online" or "offline (last seen ...)"
        const displayName = presence.name;

        // Header name & verified badge
        if (headerName) headerName.textContent = displayName;
        if (verifiedBadge) {
            if (presence.has_attending_admin) {
                verifiedBadge.classList.remove('hidden');
            } else {
                verifiedBadge.classList.add('hidden');
            }
        }

        // WhatsApp-format status text
        if (headerStatus) {
            if (isOnline) {
                headerStatus.innerHTML = '<span class="text-emerald-300 font-bold">online</span>';
            } else {
                headerStatus.innerHTML = `<span class="text-white/80">${statusText}</span>`;
            }
        }

        // Dot & avatar
        if (headerDot) {
            headerDot.className = `absolute bottom-0 right-0 w-3 h-3 rounded-full ring-2 ring-[#075E54] ${isOnline ? 'bg-emerald-400' : 'bg-slate-400'}`;
        }
        if (headerAvatar) {
            headerAvatar.textContent = presence.avatar_letter || 'S';
        }

        // Attending banner
        if (adminBanner && bannerAdminName) {
            if (presence.has_attending_admin) {
                bannerAdminName.textContent = presence.name;
                adminBanner.classList.remove('hidden');
            } else {
                adminBanner.classList.add('hidden');
            }
        }

        // Sidebar presence indicator & status text
        if (sidebarDot) {
            sidebarDot.className = `absolute -bottom-0.5 -right-0.5 w-2.5 h-2.5 rounded-full ring-2 ring-white dark:ring-slate-900 ${isOnline ? 'bg-emerald-500 animate-pulse' : 'bg-slate-400'}`;
        }
        if (sidebarText) {
            sidebarText.textContent = isOnline ? 'online' : 'offline';
            sidebarText.className = isOnline
                ? 'text-[10px] font-bold px-2 py-0.5 rounded-full bg-emerald-100 dark:bg-emerald-500/20 text-emerald-700 dark:text-emerald-400'
                : 'text-[10px] font-bold px-2 py-0.5 rounded-full bg-slate-100 dark:bg-slate-800 text-slate-500 dark:text-slate-400';
        }
    }

    // Fetch Status & Presence
    function pollSupportStatus() {
        fetch('/support/chat/status', {
            headers: { 'Accept': 'application/json' }
        })
        .then(res => res.json())
        .then(data => {
            activeSupportRoomId = data.room_id;
            updateWhatsAppPresenceUI(data.presence);

            const sidebarBadge = document.getElementById('sidebar-chat-unread-badge');
            if (sidebarBadge) {
                if (data.unread_count > 0) {
                    sidebarBadge.textContent = data.unread_count;
                    sidebarBadge.classList.remove('hidden');
                } else {
                    sidebarBadge.classList.add('hidden');
                }
            }
        })
        .catch(() => {});
    }

    // Load Support Messages
    function loadSupportMessages() {
        const container = document.getElementById('user-chat-messages');
        const placeholder = document.getElementById('chat-loading-placeholder');

        fetch('/support/chat/messages', {
            headers: { 'Accept': 'application/json' }
        })
        .then(res => res.json())
        .then(data => {
            if (placeholder) placeholder.remove();
            activeSupportRoomId = data.room_id;
            updateWhatsAppPresenceUI(data.presence);

            // Clear unread badge in sidebar
            const sidebarBadge = document.getElementById('sidebar-chat-unread-badge');
            if (sidebarBadge) sidebarBadge.classList.add('hidden');

            // Render messages
            if (data.messages && data.messages.length > 0) {
                container.querySelectorAll('[data-user-msg-id]').forEach(el => el.remove());
                data.messages.forEach(msg => appendUserSupportMessage(msg));
                scrollChatToBottom();
            } else {
                if (!container.querySelector('#empty-chat-note')) {
                    const emptyNote = `
                        <div id="empty-chat-note" class="text-center py-8 space-y-2">
                            <div class="w-12 h-12 mx-auto rounded-full bg-emerald-100 dark:bg-emerald-950/60 text-emerald-600 flex items-center justify-center">
                                <svg class="w-6 h-6" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 12h.01M12 12h.01M16 12h.01M21 12c0 4.418-4.03 8-9 8a9.863 9.863 0 01-4.255-.949L3 20l1.395-3.72C3.512 15.042 3 13.574 3 12c0-4.418 4.03-8 9-8s9 3.582 9 8z" />
                                </svg>
                            </div>
                            <h4 class="text-xs font-bold text-slate-700 dark:text-slate-200">Start a Conversation</h4>
                            <p class="text-[11px] text-slate-500 dark:text-slate-400 max-w-xs mx-auto">
                                Type a message below to connect with an administrator or support representative.
                            </p>
                        </div>
                    `;
                    container.insertAdjacentHTML('beforeend', emptyNote);
                }
            }
        })
        .catch(() => {});
    }

    // Append Message to WhatsApp Stream
    function appendUserSupportMessage(msg) {
        const container = document.getElementById('user-chat-messages');
        const emptyNote = document.getElementById('empty-chat-note');
        if (emptyNote) emptyNote.remove();

        const isMe = msg.is_current_user;
        const bubbleBg = isMe
            ? 'bg-[#d9fdd3] dark:bg-emerald-900/50 text-slate-900 dark:text-white rounded-tr-xs'
            : 'bg-white dark:bg-slate-800 text-slate-900 dark:text-white rounded-tl-xs';

        const senderLabel = isMe
            ? ''
            : `<span class="block text-[11px] font-bold text-emerald-700 dark:text-emerald-400 mb-0.5">${msg.user_name} (${msg.user_role})</span>`;

        const msgHtml = `
            <div class="flex flex-col ${isMe ? 'items-end' : 'items-start'}" data-user-msg-id="${msg.id}">
                <div class="max-w-[85%] sm:max-w-[75%] p-3 rounded-2xl shadow-xs text-xs sm:text-sm leading-relaxed ${bubbleBg}">
                    ${senderLabel}
                    <div class="break-words">${msg.message}</div>
                    <div class="flex items-center justify-end gap-1 mt-1 text-[10px] text-slate-400">
                        <span>${msg.created_at}</span>
                        ${isMe ? '<svg class="w-3.5 h-3.5 text-emerald-500" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7" /></svg>' : ''}
                    </div>
                </div>
            </div>
        `;
        container.insertAdjacentHTML('beforeend', msgHtml);
    }

    // Scroll Chat to Bottom
    function scrollChatToBottom() {
        const container = document.getElementById('user-chat-messages');
        if (container) {
            container.scrollTop = container.scrollHeight;
        }
    }

    // Send User Support Message
    function sendUserSupportMessage() {
        const input = document.getElementById('user-support-input');
        const text = input.value.trim();
        if (!text) return;

        input.value = '';

        fetch('/support/chat/messages', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content,
                'Accept': 'application/json'
            },
            body: JSON.stringify({ message: text })
        })
        .then(res => res.json())
        .then(data => {
            appendUserSupportMessage(data.message);
            updateWhatsAppPresenceUI(data.presence);
            scrollChatToBottom();
        });
    }

    // Polling Control
    function startMessagePolling() {
        stopMessagePolling();
        supportMessagePollInterval = setInterval(() => {
            if (!supportChatOpen) return;
            const container = document.getElementById('user-chat-messages');
            const lastMsgEl = container ? container.querySelector('[data-user-msg-id]:last-child') : null;
            const afterId = lastMsgEl ? lastMsgEl.getAttribute('data-user-msg-id') : 0;

            fetch(`/support/chat/messages?after_id=${afterId}`, {
                headers: { 'Accept': 'application/json' }
            })
            .then(res => res.json())
            .then(data => {
                updateWhatsAppPresenceUI(data.presence);
                if (data.messages && data.messages.length > 0) {
                    let added = false;
                    data.messages.forEach(msg => {
                        if (!container.querySelector(`[data-user-msg-id="${msg.id}"]`)) {
                            appendUserSupportMessage(msg);
                            added = true;
                        }
                    });
                    if (added) scrollChatToBottom();
                }
            })
            .catch(() => {});
        }, 2500);
    }

    function stopMessagePolling() {
        if (supportMessagePollInterval) {
            clearInterval(supportMessagePollInterval);
            supportMessagePollInterval = null;
        }
    }

    // ==========================================
    // LOUDSPEAKER CALLING RINGTONE SYNTHESIZER (Web Audio API)
    // Plays authentic telecom dual-frequency ringback sound (440Hz + 480Hz)
    // while ringing, and cleanly stops when admin answers or speaks!
    // ==========================================
    class SupportCallAudioEngine {
        constructor() {
            this.audioCtx = null;
            this.ringInterval = null;
            this.isPlaying = false;
            this.loudspeakerEnabled = true;
        }

        getAudioContext() {
            if (!this.audioCtx) {
                const AudioContextClass = window.AudioContext || window.webkitAudioContext;
                if (AudioContextClass) {
                    this.audioCtx = new AudioContextClass();
                }
            }
            if (this.audioCtx && this.audioCtx.state === 'suspended') {
                this.audioCtx.resume();
            }
            return this.audioCtx;
        }

        playRingbackBurst() {
            if (!this.isPlaying || !this.loudspeakerEnabled) return;
            const ctx = this.getAudioContext();
            if (!ctx) return;

            try {
                const now = ctx.currentTime;
                const osc1 = ctx.createOscillator();
                const osc2 = ctx.createOscillator();
                const gain = ctx.createGain();

                osc1.type = 'sine';
                osc2.type = 'sine';
                // Authentic telecom standard ringback tone frequencies (440 Hz + 480 Hz)
                osc1.frequency.setValueAtTime(440, now);
                osc2.frequency.setValueAtTime(480, now);

                // Smooth attack and decay envelope
                gain.gain.setValueAtTime(0.0001, now);
                gain.gain.exponentialRampToValueAtTime(0.18, now + 0.06);
                gain.gain.setValueAtTime(0.18, now + 1.55);
                gain.gain.exponentialRampToValueAtTime(0.0001, now + 1.75);

                osc1.connect(gain);
                osc2.connect(gain);
                gain.connect(ctx.destination);

                osc1.start(now);
                osc2.start(now);
                osc1.stop(now + 1.8);
                osc2.stop(now + 1.8);
            } catch (e) {
                console.warn('Ringback tone synthesis notice:', e);
            }
        }

        startRinging() {
            this.isPlaying = true;
            this.getAudioContext();
            this.playRingbackBurst();
            clearInterval(this.ringInterval);
            this.ringInterval = setInterval(() => {
                if (this.isPlaying) {
                    this.playRingbackBurst();
                }
            }, 3600);
        }

        stopRinging() {
            this.isPlaying = false;
            if (this.ringInterval) {
                clearInterval(this.ringInterval);
                this.ringInterval = null;
            }
        }

        playConnectedChime() {
            const ctx = this.getAudioContext();
            if (!ctx) return;
            try {
                const now = ctx.currentTime;
                const osc = ctx.createOscillator();
                const gain = ctx.createGain();

                osc.type = 'sine';
                osc.frequency.setValueAtTime(523.25, now); // C5
                osc.frequency.setValueAtTime(659.25, now + 0.12); // E5
                osc.frequency.setValueAtTime(783.99, now + 0.24); // G5

                gain.gain.setValueAtTime(0.001, now);
                gain.gain.exponentialRampToValueAtTime(0.2, now + 0.03);
                gain.gain.exponentialRampToValueAtTime(0.001, now + 0.55);

                osc.connect(gain);
                gain.connect(ctx.destination);
                osc.start(now);
                osc.stop(now + 0.58);
            } catch (e) {}
        }

        speakAdminGreeting(adminName) {
            if ('speechSynthesis' in window) {
                window.speechSynthesis.cancel();
                const name = adminName || 'Support Administrator';
                const text = `Hello! ${name} has picked up your call and is listening. How can I help you today?`;
                const utterance = new SpeechSynthesisUtterance(text);
                utterance.rate = 1.0;
                utterance.pitch = 1.0;
                utterance.volume = 1.0;

                const voices = window.speechSynthesis.getVoices();
                const preferredVoice = voices.find(v => (v.lang.startsWith('en') && (v.name.includes('Google') || v.name.includes('Natural') || v.name.includes('English'))));
                if (preferredVoice) utterance.voice = preferredVoice;

                window.speechSynthesis.speak(utterance);
            }
        }
    }

    const supportAudio = new SupportCallAudioEngine();
    let callIsActive = false;

    // ==========================================
    // USER SUPPORT VOICE CALLING ENGINE
    // ==========================================
    function initiateUserSupportCall() {
        const overlay = document.getElementById('user-call-overlay');
        const adminNameEl = document.getElementById('user-call-admin-name');
        const adminRoleEl = document.getElementById('user-call-admin-role');
        const statusLabel = document.getElementById('user-call-status-label');
        const avatarEl = document.getElementById('user-call-avatar');
        const pulseRing = document.getElementById('user-call-pulsing-ring');
        const equalizer = document.getElementById('user-call-equalizer');
        const simBtn = document.getElementById('simulate-admin-answer-btn');

        overlay.classList.remove('hidden');
        if (pulseRing) pulseRing.classList.remove('hidden');
        if (equalizer) equalizer.classList.add('hidden');
        if (simBtn) {
            simBtn.textContent = 'Simulate Admin Pick Up (Test)';
            simBtn.disabled = false;
            simBtn.classList.remove('hidden');
        }

        callIsActive = false;
        statusLabel.innerHTML = '<span class="w-2 h-2 rounded-full bg-emerald-400 animate-pulse"></span><span>Connecting Loudspeaker...</span>';

        // Start playing telephone calling sound immediately
        supportAudio.startRinging();

        // Request microphone
        if (navigator.mediaDevices && navigator.mediaDevices.getUserMedia) {
            navigator.mediaDevices.getUserMedia({ audio: true })
                .then(stream => { supportLocalStream = stream; })
                .catch(() => { /* continue in sandbox */ });
        }

        fetch('/support/chat/call', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content,
                'Accept': 'application/json'
            },
            body: JSON.stringify({ sdp_offer: 'simulated_user_audio_offer' })
        })
        .then(res => res.json())
        .then(data => {
            currentSupportCallId = data.call_id;
            updateWhatsAppPresenceUI(data.presence);

            if (data.presence.has_attending_admin) {
                adminNameEl.textContent = data.presence.name;
                adminRoleEl.textContent = 'Support Representative';
                avatarEl.textContent = data.presence.avatar_letter;
            } else {
                adminNameEl.textContent = 'Support Desk';
                adminRoleEl.textContent = 'Broadcasting call to online staff...';
                avatarEl.textContent = 'S';
            }

            statusLabel.innerHTML = '<span class="w-2 h-2 rounded-full bg-emerald-400 animate-pulse"></span><span>Ringing with Loudspeaker...</span>';
            startSupportCallTimer();
            startSupportCallPolling();
        });
    }

    function startSupportCallTimer() {
        supportCallDurationSecs = 0;
        clearInterval(supportCallTimerInterval);
        supportCallTimerInterval = setInterval(() => {
            supportCallDurationSecs++;
            const mins = String(Math.floor(supportCallDurationSecs / 60)).padStart(2, '0');
            const secs = String(supportCallDurationSecs % 60).padStart(2, '0');
            const timerEl = document.getElementById('user-call-timer');
            if (timerEl) timerEl.textContent = `${mins}:${secs}`;
        }, 1000);
    }

    function handleCallStatusUpdate(data) {
        const statusLabel = document.getElementById('user-call-status-label');
        const adminNameEl = document.getElementById('user-call-admin-name');
        const adminRoleEl = document.getElementById('user-call-admin-role');
        const avatarEl = document.getElementById('user-call-avatar');
        const pulseRing = document.getElementById('user-call-pulsing-ring');
        const equalizer = document.getElementById('user-call-equalizer');
        const simBtn = document.getElementById('simulate-admin-answer-btn');

        if (data.attending_admin) {
            adminNameEl.textContent = data.attending_admin.name;
            adminRoleEl.textContent = data.attending_admin.role || 'Support Representative';
            avatarEl.textContent = data.attending_admin.name.substr(0, 1).toUpperCase();
        }

        if (data.status === 'active') {
            // STOP CALLING SOUND IMMEDIATELY! Admin has picked up
            supportAudio.stopRinging();

            // Play connected chime and make admin speak once
            if (!callIsActive) {
                callIsActive = true;
                supportAudio.playConnectedChime();
                const name = (data.attending_admin && data.attending_admin.name) ? data.attending_admin.name : 'Support Administrator';
                setTimeout(() => {
                    supportAudio.speakAdminGreeting(name);
                }, 350);
            }

            statusLabel.innerHTML = '<span class="w-2.5 h-2.5 rounded-full bg-emerald-400 animate-pulse"></span><span class="text-emerald-300 font-bold">Connected • Admin Speaking</span>';
            if (pulseRing) pulseRing.classList.add('hidden');
            if (equalizer) equalizer.classList.remove('hidden');
            if (simBtn) simBtn.classList.add('hidden');
        } else if (data.status === 'ended' || data.status === 'declined' || data.status === 'missed') {
            supportAudio.stopRinging();
            if ('speechSynthesis' in window) window.speechSynthesis.cancel();
            statusLabel.innerHTML = `<span class="text-red-400 font-bold">Call ${data.status}</span>`;
            setTimeout(() => endUserSupportCall(), 1200);
        }
    }

    function startSupportCallPolling() {
        stopSupportCallPolling();
        supportCallPollInterval = setInterval(() => {
            if (!currentSupportCallId) return;

            fetch(`/support/chat/call/status?call_id=${currentSupportCallId}`, {
                headers: { 'Accept': 'application/json' }
            })
            .then(res => res.json())
            .then(data => {
                handleCallStatusUpdate(data);
            })
            .catch(() => {});
        }, 1800);
    }

    function stopSupportCallPolling() {
        if (supportCallPollInterval) {
            clearInterval(supportCallPollInterval);
            supportCallPollInterval = null;
        }
    }

    function toggleLoudspeaker() {
        supportAudio.loudspeakerEnabled = !supportAudio.loudspeakerEnabled;
        const speakerBtn = document.getElementById('user-call-speaker-btn');
        if (speakerBtn) {
            speakerBtn.classList.toggle('text-emerald-300', supportAudio.loudspeakerEnabled);
            speakerBtn.classList.toggle('text-slate-400', !supportAudio.loudspeakerEnabled);
            speakerBtn.classList.toggle('opacity-50', !supportAudio.loudspeakerEnabled);
        }
    }

    function toggleUserMuteCall() {
        supportIsMuted = !supportIsMuted;
        if (supportLocalStream) {
            supportLocalStream.getAudioTracks().forEach(track => { track.enabled = !supportIsMuted; });
        }
        const micIcon = document.getElementById('user-mic-icon');
        if (micIcon) micIcon.classList.toggle('text-red-500', supportIsMuted);
    }

    function simulateAdminAnswerCall() {
        if (!currentSupportCallId) return;
        const simBtn = document.getElementById('simulate-admin-answer-btn');
        if (simBtn) {
            simBtn.textContent = 'Connecting Admin...';
            simBtn.disabled = true;
        }

        fetch(`/support/chat/call/${currentSupportCallId}/simulate-answer`, {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content,
                'Accept': 'application/json'
            }
        })
        .then(res => res.json())
        .then(data => {
            if (data.status === 'active') {
                fetch(`/support/chat/call/status?call_id=${currentSupportCallId}`, {
                    headers: { 'Accept': 'application/json' }
                })
                .then(r => r.json())
                .then(callData => {
                    handleCallStatusUpdate(callData);
                });
            }
        })
        .catch(() => {});
    }

    function endUserSupportCall() {
        const callId = currentSupportCallId;
        currentSupportCallId = null;
        callIsActive = false;

        // Stop calling sound and any voice output
        supportAudio.stopRinging();
        if ('speechSynthesis' in window) {
            window.speechSynthesis.cancel();
        }

        stopSupportCallPolling();
        clearInterval(supportCallTimerInterval);

        if (supportLocalStream) {
            supportLocalStream.getTracks().forEach(t => t.stop());
            supportLocalStream = null;
        }

        document.getElementById('user-call-overlay').classList.add('hidden');
        const equalizer = document.getElementById('user-call-equalizer');
        if (equalizer) equalizer.classList.add('hidden');

        if (callId) {
            fetch(`/support/chat/call/${callId}/end`, {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content,
                    'Accept': 'application/json'
                }
            }).catch(() => {});
        }
    }

    // Initialize Presence on Page Load
    document.addEventListener('DOMContentLoaded', () => {
        pollSupportStatus();
        supportPresencePollInterval = setInterval(pollSupportStatus, 4000);
    });
</script>
