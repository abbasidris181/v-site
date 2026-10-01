@extends('layouts.admin')

@section('title', 'Internal Staff Communications & Calls')

@section('content')
<div class="space-y-6">
    <!-- Top Header -->
    <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-4">
        <div>
            <div class="flex items-center gap-2 text-xs font-semibold text-indigo-600 dark:text-indigo-400 uppercase tracking-widest">
                <span class="w-2 h-2 rounded-full bg-emerald-500 animate-pulse"></span>
                <span>Intercom Network Online</span>
            </div>
            <h1 class="text-2xl sm:text-3xl font-extrabold text-slate-900 dark:text-white tracking-tight mt-1">
                Internal Staff Communications
            </h1>
            <p class="text-xs sm:text-sm text-slate-500 dark:text-slate-400 mt-1">
                Secure real-time staff channels, direct colleague messaging, and internal voice calls.
            </p>
        </div>

        <div class="flex items-center gap-3">
            <span class="px-3 py-1.5 rounded-xl text-xs font-bold bg-indigo-50 dark:bg-indigo-500/10 text-indigo-700 dark:text-indigo-300 border border-indigo-200/80 dark:border-indigo-500/20 shadow-xs">
                Logged in as: {{ auth()->user()->full_name }} ({{ auth()->user()->roles->pluck('name')->first() }})
            </span>
        </div>
    </div>

    <!-- Main Communications Grid (Left: Channels/Directory, Right: Conversation & Calls) -->
    <div class="grid grid-cols-1 lg:grid-cols-12 gap-6 items-start">
        
        <!-- ==========================================
             LEFT SIDE: CHANNELS & SUPPORT INQUIRIES
             ========================================== -->
        <div class="lg:col-span-4 space-y-6">
            
            <!-- 1. Channels Card -->
            <div class="rounded-2xl bg-white dark:bg-slate-900 border border-slate-200/80 dark:border-slate-800 p-5 shadow-sm space-y-4">
                <div class="flex items-center justify-between border-b border-slate-100 dark:border-slate-800 pb-3">
                    <h3 class="text-xs font-bold uppercase tracking-wider text-slate-500 dark:text-slate-400 flex items-center gap-2">
                        <svg class="w-4 h-4 text-indigo-600 dark:text-indigo-400" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 20l4-16m2 16l4-16M6 9h14M4 15h14" />
                        </svg>
                        <span>Staff Channels</span>
                    </h3>
                    <span class="text-[10px] font-bold px-2 py-0.5 rounded-full bg-slate-100 dark:bg-slate-800 text-slate-600 dark:text-slate-300">
                        {{ $channels->count() }} active
                    </span>
                </div>

                <div class="space-y-1">
                    @foreach($channels as $channel)
                        @php
                            $isCurrent = $activeRoom && $activeRoom->id === $channel->id;
                            $unread = $channel->unreadCountForUser(auth()->user());
                        @endphp
                        <a href="{{ route('admin.chat.index', ['room_id' => $channel->id]) }}"
                           class="flex items-center justify-between px-3.5 py-2.5 rounded-xl transition-all cursor-pointer {{ $isCurrent ? 'bg-indigo-600 text-white shadow-md shadow-indigo-600/20 font-bold' : 'text-slate-700 dark:text-slate-300 hover:bg-slate-100 dark:hover:bg-slate-800/60' }}">
                            <div class="flex items-center gap-2.5 min-w-0">
                                <span class="text-base font-extrabold {{ $isCurrent ? 'text-indigo-200' : 'text-indigo-500' }}">#</span>
                                <span class="text-xs font-bold truncate">{{ $channel->name }}</span>
                            </div>
                            @if($unread > 0 && ! $isCurrent)
                                <span class="px-2 py-0.5 rounded-full text-[10px] font-extrabold bg-indigo-600 text-white">
                                    {{ $unread }}
                                </span>
                            @endif
                        </a>
                    @endforeach
                </div>
            </div>

            <!-- 2. Customer Support Inquiries -->
            <div class="rounded-2xl bg-white dark:bg-slate-900 border border-slate-200/80 dark:border-slate-800 p-5 shadow-sm space-y-4">
                <div class="flex items-center justify-between border-b border-slate-100 dark:border-slate-800 pb-3">
                    <h3 class="text-xs font-bold uppercase tracking-wider text-slate-500 dark:text-slate-400 flex items-center gap-2">
                        <svg class="w-4 h-4 text-emerald-600 dark:text-emerald-400" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M18.364 5.636l-3.536 3.536m0 5.656l3.536 3.536M9.172 9.172L5.636 5.636m3.536 9.192l-3.536 3.536M21 12a9 9 0 11-18 0 9 9 0 0118 0zm-5 0a4 4 0 11-8 0 4 4 0 018 0z" />
                        </svg>
                        <span>Customer Support Desk</span>
                    </h3>
                    <span class="text-[10px] font-bold px-2 py-0.5 rounded-full bg-emerald-50 dark:bg-emerald-500/10 text-emerald-700 dark:text-emerald-300">
                        {{ $supportRooms->count() }} tickets
                    </span>
                </div>

                <div class="space-y-1 max-h-72 overflow-y-auto pr-1">
                    @forelse($supportRooms as $sRoom)
                        @php
                            $isCurrent = $activeRoom && $activeRoom->id === $sRoom->id;
                            $unread = $sRoom->unreadCountForUser(auth()->user());
                            $cust = $sRoom->users->firstWhere(fn ($u) => ! $u->hasAdminBackendAccess());
                            $isAttending = $sRoom->assigned_staff_id === auth()->id();
                        @endphp
                        <a href="{{ route('admin.chat.index', ['room_id' => $sRoom->id]) }}"
                           class="block p-2.5 rounded-xl transition-all cursor-pointer {{ $isCurrent ? 'bg-emerald-600 text-white shadow-md shadow-emerald-600/20 font-bold' : 'text-slate-700 dark:text-slate-300 hover:bg-slate-100 dark:hover:bg-slate-800/60' }}">
                            <div class="flex items-center justify-between gap-2">
                                <span class="text-xs font-bold truncate">{{ $cust ? $cust->full_name : $sRoom->name }}</span>
                                @if($unread > 0 && ! $isCurrent)
                                    <span class="px-1.5 py-0.2 rounded-full text-[10px] font-extrabold bg-emerald-500 text-white">
                                        {{ $unread }}
                                    </span>
                                @endif
                            </div>
                            <div class="flex items-center justify-between text-[10px] mt-1 {{ $isCurrent ? 'text-emerald-100' : 'text-slate-400' }}">
                                <span>{{ $cust?->email ?? 'User' }}</span>
                                @if($sRoom->assigned_staff_id)
                                    <span>{{ $isAttending ? 'You are attending' : 'Attending: ' . ($sRoom->assignedStaff?->first_name ?? 'Staff') }}</span>
                                @else
                                    <span class="font-bold text-amber-500">Unassigned</span>
                                @endif
                            </div>
                        </a>
                    @empty
                        <p class="text-xs text-slate-400 text-center py-4">No active customer inquiries.</p>
                    @endforelse
                </div>
            </div>
        </div>

        <!-- ==========================================
             RIGHT SIDE: ACTIVE CONVERSATION STREAM
             ========================================== -->
        <div class="lg:col-span-8 space-y-4">
            @if($activeRoom)
                <div class="rounded-2xl bg-white dark:bg-slate-900 border border-slate-200/80 dark:border-slate-800 shadow-sm flex flex-col h-[650px] overflow-hidden">
                    
                    <!-- Room Header -->
                    <div class="px-6 py-4 border-b border-slate-200/80 dark:border-slate-800 bg-slate-50/50 dark:bg-slate-950/40 flex items-center justify-between gap-4">
                        <div class="flex items-center gap-3">
                            <div class="w-10 h-10 rounded-xl {{ $activeRoom->is_support ? 'bg-emerald-50 dark:bg-emerald-500/10 text-emerald-600 dark:text-emerald-400 border-emerald-200/60 dark:border-emerald-500/20' : 'bg-indigo-50 dark:bg-indigo-500/10 text-indigo-600 dark:text-indigo-400 border-indigo-200/60 dark:border-indigo-500/20' }} flex items-center justify-center font-extrabold text-base border shadow-xs">
                                {{ $activeRoom->is_support ? '🎧' : ($activeRoom->type === 'channel' ? '#' : '@') }}
                            </div>
                            <div>
                                <h2 class="text-base font-extrabold text-slate-900 dark:text-white">
                                    {{ $activeRoom->getDisplayNameForUser(auth()->user()) }}
                                </h2>
                                <span class="text-xs text-slate-500 dark:text-slate-400">
                                    @if($activeRoom->is_support)
                                        Customer Live Support Desk
                                        @if($activeRoom->assignedStaff)
                                            • Attending: <strong class="text-emerald-600 dark:text-emerald-400">{{ $activeRoom->assignedStaff->full_name }}</strong>
                                        @else
                                            • <strong class="text-amber-500">Unassigned Ticket</strong>
                                        @endif
                                    @elseif($activeRoom->type === 'channel')
                                        Shared Staff &amp; Admin Channel
                                    @else
                                        Direct Confidential Intercom
                                    @endif
                                </span>
                            </div>
                        </div>

                        <div class="flex items-center gap-2">
                            @if($activeRoom->is_support && ! $activeRoom->assigned_staff_id)
                                <button type="button"
                                        onclick="claimActiveSupportRoom({{ $activeRoom->id }})"
                                        class="px-3.5 py-2 rounded-xl text-xs font-bold text-white bg-indigo-600 hover:bg-indigo-500 shadow-sm transition cursor-pointer">
                                    Claim &amp; Attend User
                                </button>
                            @endif

                            @if($activeRoom->is_support)
                                @php
                                    $targetCustomer = $activeRoom->users->firstWhere(fn ($u) => ! $u->hasAdminBackendAccess());
                                @endphp
                                @if($targetCustomer)
                                    <button type="button"
                                            onclick="startInternalCall({{ $targetCustomer->id }}, '{{ addslashes($targetCustomer->full_name) }}', 'Customer')"
                                            class="inline-flex items-center gap-2 px-3.5 py-2 rounded-xl text-xs font-bold text-white bg-emerald-600 hover:bg-emerald-500 shadow-sm transition cursor-pointer">
                                        <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 5a2 2 0 012-2h3.28a1 1 0 01.948.684l1.498 4.493a1 1 0 01-.502 1.21l-2.257 1.13a11.042 11.042 0 005.516 5.516l1.13-2.257a1 1 0 011.21-.502l4.493 1.498a1 1 0 01.684.949V19a2 2 0 01-2 2h-1C9.716 21 3 14.284 3 6V5z" />
                                        </svg>
                                        <span>Call Customer</span>
                                    </button>
                                @endif
                            @elseif($activeRoom->type === 'direct')
                                @php
                                    $targetColleague = $activeRoom->users->firstWhere('id', '!=', auth()->id());
                                @endphp
                                @if($targetColleague)
                                    <button type="button"
                                            onclick="startInternalCall({{ $targetColleague->id }}, '{{ addslashes($targetColleague->full_name) }}', '{{ addslashes($targetColleague->roles->pluck('name')->first() ?? 'Staff') }}')"
                                            class="inline-flex items-center gap-2 px-3.5 py-2 rounded-xl text-xs font-bold text-white bg-emerald-600 hover:bg-emerald-500 shadow-sm transition cursor-pointer">
                                        <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 5a2 2 0 012-2h3.28a1 1 0 01.948.684l1.498 4.493a1 1 0 01-.502 1.21l-2.257 1.13a11.042 11.042 0 005.516 5.516l1.13-2.257a1 1 0 011.21-.502l4.493 1.498a1 1 0 01.684.949V19a2 2 0 01-2 2h-1C9.716 21 3 14.284 3 6V5z" />
                                        </svg>
                                        <span>Call {{ $targetColleague->first_name }}</span>
                                    </button>
                                @endif
                            @endif
                        </div>
                    </div>

                    <!-- Messages Scrollable Body -->
                    <div id="messages-container" class="flex-1 overflow-y-auto p-6 space-y-4">
                        @forelse($activeRoom->messages as $msg)
                            @php
                                $isMe = $msg->user_id === auth()->id();
                                $senderRole = $msg->user->roles->pluck('name')->first() ?? 'Staff';
                            @endphp
                            <div class="flex items-start gap-3 {{ $isMe ? 'flex-row-reverse' : '' }}" data-message-id="{{ $msg->id }}">
                                <div class="w-8 h-8 rounded-full bg-slate-200 dark:bg-slate-700 text-slate-700 dark:text-slate-200 text-xs font-bold flex items-center justify-center flex-shrink-0">
                                    {{ strtoupper(substr($msg->user->first_name, 0, 1) . substr($msg->user->surname, 0, 1)) }}
                                </div>
                                <div class="max-w-lg space-y-1">
                                    <div class="flex items-center gap-2 text-[11px] {{ $isMe ? 'justify-end' : '' }}">
                                        <span class="font-bold text-slate-900 dark:text-white">{{ $isMe ? 'You' : $msg->user->full_name }}</span>
                                        <span class="px-1.5 py-0.2 rounded-md font-semibold text-[10px] bg-slate-100 dark:bg-slate-800 text-indigo-700 dark:text-indigo-400 border border-slate-200/60 dark:border-slate-700">
                                            {{ $senderRole }}
                                        </span>
                                        <span class="text-slate-400">{{ $msg->created_at->format('h:i A') }}</span>
                                    </div>
                                    <div class="p-3.5 rounded-2xl text-xs leading-relaxed {{ $isMe ? 'bg-indigo-600 text-white rounded-tr-xs' : 'bg-slate-100 dark:bg-slate-800 text-slate-900 dark:text-slate-100 rounded-tl-xs' }}">
                                        {{ $msg->message }}
                                    </div>
                                </div>
                            </div>
                        @empty
                            <div class="text-center py-16 space-y-2 text-slate-400">
                                <p class="text-xs">No messages yet in this room.</p>
                                <p class="text-[11px]">Send a greeting or update below to start collaborating.</p>
                            </div>
                        @endforelse
                    </div>

                    <!-- Message Composer -->
                    <div class="p-4 border-t border-slate-200/80 dark:border-slate-800 bg-white dark:bg-slate-900">
                        <form id="chat-form" onsubmit="handleSendMessage(event)" class="flex items-center gap-3">
                            <input type="text"
                                   id="message-input"
                                   name="message"
                                   required
                                   autocomplete="off"
                                   placeholder="Type internal message to {{ $activeRoom->name ?? 'colleagues' }}..."
                                   class="flex-1 px-4 py-3 rounded-xl border border-slate-300 dark:border-slate-700 bg-white dark:bg-slate-800 text-slate-900 dark:text-white placeholder-slate-400 dark:placeholder-slate-500 text-xs focus:border-indigo-500 focus:ring-2 focus:ring-indigo-500/20">
                            <button type="submit"
                                    id="send-btn"
                                    class="px-5 py-3 rounded-xl text-xs font-bold text-white bg-indigo-600 hover:bg-indigo-500 shadow-md shadow-indigo-600/20 transition cursor-pointer flex items-center gap-2">
                                <span>Send</span>
                                <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M14 5l7 7m0 0l-7 7m7-7H3" />
                                </svg>
                            </button>
                        </form>
                    </div>

                </div>
            @else
                <div class="p-12 text-center rounded-2xl bg-white dark:bg-slate-900 border border-slate-200 dark:border-slate-800">
                    <p class="text-sm text-slate-500">Select a channel or staff member from the left to start communicating.</p>
                </div>
            @endif
        </div>

    </div>
</div>

<!-- ==========================================
     ACTIVE WEBRTC VOICE CALL OVERLAY
     ========================================== -->
<div id="active-call-overlay" class="fixed inset-0 z-50 flex items-center justify-center p-4 bg-slate-900/80 backdrop-blur-sm hidden">
    <div class="w-full max-w-md rounded-3xl bg-slate-900 border-2 border-indigo-500/40 p-8 text-center text-white space-y-6 shadow-2xl relative overflow-hidden">
        
        <!-- Subtle Glow Background -->
        <div class="absolute -top-24 -left-24 w-48 h-48 bg-indigo-500/20 rounded-full blur-3xl pointer-events-none"></div>
        <div class="absolute -bottom-24 -right-24 w-48 h-48 bg-emerald-500/20 rounded-full blur-3xl pointer-events-none"></div>

        <!-- Call Status Pill -->
        <div class="inline-flex items-center gap-2 px-3 py-1 rounded-full bg-slate-800 border border-slate-700 text-xs font-bold text-emerald-400">
            <span class="w-2 h-2 rounded-full bg-emerald-500 animate-ping"></span>
            <span id="call-status-label">Ringing Colleague...</span>
        </div>

        <!-- Caller Avatar Pulse -->
        <div class="relative w-28 h-28 mx-auto my-4">
            <div class="absolute inset-0 rounded-full bg-indigo-500/30 animate-ping duration-1000"></div>
            <div class="relative w-28 h-28 rounded-full bg-gradient-to-tr from-indigo-600 via-indigo-500 to-teal-400 flex items-center justify-center text-4xl font-extrabold shadow-xl shadow-indigo-600/40">
                <span id="call-avatar-letters">VC</span>
            </div>
        </div>

        <div>
            <h3 id="call-partner-name" class="text-xl font-extrabold tracking-tight">
                Colleague Name
            </h3>
            <p id="call-partner-role" class="text-xs font-semibold text-indigo-300 mt-0.5">
                Staff Member
            </p>
            <div id="call-duration-timer" class="text-2xl font-black text-white mt-3 font-mono">
                00:00
            </div>
        </div>

        <!-- Interactive Call Controls -->
        <div class="flex items-center justify-center gap-6 pt-4">
            <!-- Mute Toggle -->
            <button type="button"
                    id="mute-call-btn"
                    onclick="toggleMuteCall()"
                    class="p-4 rounded-2xl bg-slate-800 hover:bg-slate-700 text-slate-300 transition cursor-pointer border border-slate-700">
                <svg id="mic-icon" class="w-6 h-6" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 11a7 7 0 01-7 7m0 0a7 7 0 01-7-7m7 7v4m0 0H8m4 0h4m-4-8a3 3 0 01-3-3V5a3 3 0 116 0v6a3 3 0 01-3 3z" />
                </svg>
            </button>

            <!-- End Call (Hangup) -->
            <button type="button"
                    onclick="endActiveCall()"
                    class="p-4 rounded-2xl bg-red-600 hover:bg-red-500 text-white shadow-lg shadow-red-600/40 transition cursor-pointer">
                <svg class="w-6 h-6 rotate-135" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 5a2 2 0 012-2h3.28a1 1 0 01.948.684l1.498 4.493a1 1 0 01-.502 1.21l-2.257 1.13a11.042 11.042 0 005.516 5.516l1.13-2.257a1 1 0 011.21-.502l4.493 1.498a1 1 0 01.684.949V19a2 2 0 01-2 2h-1C9.716 21 3 14.284 3 6V5z" />
                </svg>
            </button>
        </div>
    </div>
</div>

<script>
    const activeRoomId = {{ $activeRoom ? $activeRoom->id : 'null' }};
    let currentCallId = null;
    let callTimerInterval = null;
    let callDurationSecs = 0;
    let isMuted = false;
    let localStream = null;
    let peerConnection = null;

    // Scroll to bottom of message stream
    function scrollToBottom() {
        const container = document.getElementById('messages-container');
        if (container) {
            container.scrollTop = container.scrollHeight;
        }
    }

    // Send Message AJAX
    function handleSendMessage(e) {
        e.preventDefault();
        const input = document.getElementById('message-input');
        const text = input.value.trim();
        if (!text || !activeRoomId) return;

        input.value = '';

        fetch(`/admin/chat/rooms/${activeRoomId}/messages`, {
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
            if (data.message) {
                appendMessage(data.message);
                scrollToBottom();
            }
        })
        .catch(err => console.error('Failed to send message:', err));
    }

    function appendMessage(msg) {
        const container = document.getElementById('messages-container');
        if (!container) return;

        // Check if message already exists
        if (container.querySelector(`[data-message-id="${msg.id}"]`)) return;

        const isMe = msg.is_current_user;
        const msgHtml = `
            <div class="flex items-start gap-3 ${isMe ? 'flex-row-reverse' : ''}" data-message-id="${msg.id}">
                <div class="w-8 h-8 rounded-full bg-slate-200 dark:bg-slate-700 text-slate-700 dark:text-slate-200 text-xs font-bold flex items-center justify-center flex-shrink-0">
                    ${msg.user_name.substr(0, 2).toUpperCase()}
                </div>
                <div class="max-w-lg space-y-1">
                    <div class="flex items-center gap-2 text-[11px] ${isMe ? 'justify-end' : ''}">
                        <span class="font-bold text-slate-900 dark:text-white">${isMe ? 'You' : msg.user_name}</span>
                        <span class="px-1.5 py-0.2 rounded-md font-semibold text-[10px] bg-slate-100 dark:bg-slate-800 text-indigo-700 dark:text-indigo-400 border border-slate-200/60 dark:border-slate-700">
                            ${msg.user_role}
                        </span>
                        <span class="text-slate-400">${msg.created_at}</span>
                    </div>
                    <div class="p-3.5 rounded-2xl text-xs leading-relaxed ${isMe ? 'bg-indigo-600 text-white rounded-tr-xs' : 'bg-slate-100 dark:bg-slate-800 text-slate-900 dark:text-slate-100 rounded-tl-xs'}">
                        ${msg.message}
                    </div>
                </div>
            </div>
        `;
        container.insertAdjacentHTML('beforeend', msgHtml);
    }

    // Polling for new messages in the active room
    function pollRoomMessages() {
        if (!activeRoomId) return;

        const container = document.getElementById('messages-container');
        const lastMsgEl = container ? container.querySelector('[data-message-id]:last-child') : null;
        const afterId = lastMsgEl ? lastMsgEl.getAttribute('data-message-id') : 0;

        fetch(`/admin/chat/rooms/${activeRoomId}/messages?after_id=${afterId}`, {
            headers: { 'Accept': 'application/json' }
        })
        .then(res => res.json())
        .then(data => {
            if (data.messages && data.messages.length > 0) {
                let added = false;
                data.messages.forEach(msg => {
                    if (!container.querySelector(`[data-message-id="${msg.id}"]`)) {
                        appendMessage(msg);
                        added = true;
                    }
                });
                if (added) scrollToBottom();
            }
        })
        .catch(() => {});
    }

    // Start direct chat with colleague
    function openDirectChat(colleagueId) {
        fetch('/admin/chat/direct', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content,
                'Accept': 'application/json'
            },
            body: JSON.stringify({ colleague_id: colleagueId })
        })
        .then(res => res.json())
        .then(data => {
            if (data.room_id) {
                window.location.href = `/admin/chat?room_id=${data.room_id}`;
            }
        });
    }

    // Claim customer support inquiry
    function claimActiveSupportRoom(roomId) {
        fetch(`/admin/chat/rooms/${roomId}/claim`, {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content,
                'Accept': 'application/json'
            }
        })
        .then(res => res.json())
        .then(data => {
            if (data.status === 'claimed') {
                window.location.reload();
            }
        });
    }

    // ==========================================
    // INTERNAL VOICE CALLING LOGIC
    // ==========================================
    function startInternalCall(receiverId, receiverName, receiverRole) {
        const overlay = document.getElementById('active-call-overlay');
        const partnerName = document.getElementById('call-partner-name');
        const partnerRole = document.getElementById('call-partner-role');
        const statusLabel = document.getElementById('call-status-label');
        const avatarLetters = document.getElementById('call-avatar-letters');

        partnerName.textContent = receiverName;
        partnerRole.textContent = receiverRole;
        statusLabel.textContent = 'Calling...';
        avatarLetters.textContent = receiverName.substr(0, 2).toUpperCase();
        overlay.classList.remove('hidden');

        // Request local audio if available
        if (navigator.mediaDevices && navigator.mediaDevices.getUserMedia) {
            navigator.mediaDevices.getUserMedia({ audio: true })
                .then(stream => { localStream = stream; })
                .catch(() => { /* continue without audio device in test environments */ });
        }

        fetch('/admin/chat/calls/initiate', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content,
                'Accept': 'application/json'
            },
            body: JSON.stringify({
                receiver_id: receiverId,
                room_id: activeRoomId,
                sdp_offer: 'simulated_webrtc_offer_audio'
            })
        })
        .then(res => res.json())
        .then(data => {
            currentCallId = data.call_id;
            startCallTimer();
        });
    }

    function startCallTimer() {
        callDurationSecs = 0;
        clearInterval(callTimerInterval);
        callTimerInterval = setInterval(() => {
            callDurationSecs++;
            const mins = String(Math.floor(callDurationSecs / 60)).padStart(2, '0');
            const secs = String(callDurationSecs % 60).padStart(2, '0');
            const timerEl = document.getElementById('call-duration-timer');
            if (timerEl) timerEl.textContent = `${mins}:${secs}`;
        }, 1000);
    }

    function toggleMuteCall() {
        isMuted = !isMuted;
        if (localStream) {
            localStream.getAudioTracks().forEach(track => { track.enabled = !isMuted; });
        }
        const micIcon = document.getElementById('mic-icon');
        if (micIcon) {
            micIcon.classList.toggle('text-red-500', isMuted);
        }
    }

    function endActiveCall() {
        if (!currentCallId) {
            document.getElementById('active-call-overlay').classList.add('hidden');
            return;
        }

        fetch(`/admin/chat/calls/${currentCallId}/status`, {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content,
                'Accept': 'application/json'
            },
            body: JSON.stringify({ action: 'end' })
        }).finally(() => {
            clearInterval(callTimerInterval);
            if (localStream) {
                localStream.getTracks().forEach(track => track.stop());
                localStream = null;
            }
            document.getElementById('active-call-overlay').classList.add('hidden');
            currentCallId = null;
        });
    }

    // Check if page was loaded with an incoming active call parameter
    document.addEventListener('DOMContentLoaded', () => {
        scrollToBottom();
        setInterval(pollRoomMessages, 2500);

        const urlParams = new URLSearchParams(window.location.search);
        const incomingActiveCallId = urlParams.get('active_call');
        if (incomingActiveCallId) {
            currentCallId = incomingActiveCallId;
            const overlay = document.getElementById('active-call-overlay');
            const statusLabel = document.getElementById('call-status-label');
            statusLabel.textContent = 'Call Connected';
            overlay.classList.remove('hidden');
            startCallTimer();
        }
    });
</script>
@endsection
