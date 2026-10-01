<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\InternalCallSession;
use App\Models\InternalChatMessage;
use App\Models\InternalChatRoom;
use App\Models\User;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class InternalChatController extends Controller
{
    /**
     * Enforce staff/admin authorization.
     */
    protected function authorizeStaffAccess(User $user): void
    {
        if (! $user->hasAdminBackendAccess()) {
            abort(403, 'Unauthorized. Internal Communications is reserved for Staff and Administrators.');
        }
    }

    /**
     * Main Chat Interface View.
     */
    public function index(Request $request): View
    {
        $user = $request->user();
        $this->authorizeStaffAccess($user);

        // Ensure user is attached to all public channels
        $channels = InternalChatRoom::where('type', 'channel')->get();
        foreach ($channels as $channel) {
            $channel->users()->syncWithoutDetaching([$user->id]);
        }

        // Fetch user's direct chat rooms
        $directRooms = $user->chatRooms()
            ->where('type', 'direct')
            ->with(['users', 'messages' => fn ($q) => $q->latest()->limit(1)])
            ->get();

        // Customer Support Inquiries
        $supportRooms = InternalChatRoom::where('is_support', true)
            ->with(['users', 'assignedStaff', 'messages' => fn ($q) => $q->latest()->limit(1)])
            ->latest('updated_at')
            ->get();

        // Other active staff/admins for direct messaging & calling
        $colleagues = User::whereHas('roles', function ($q) {
            $q->whereIn('slug', ['super_admin', 'admin', 'staff']);
        })
            ->where('id', '!=', $user->id)
            ->where('is_active', true)
            ->orderBy('first_name')
            ->get();

        // Selected room
        $selectedRoom = null;
        if ($request->has('room_id')) {
            $selectedRoom = InternalChatRoom::with(['messages.user'])->find($request->room_id);
        }

        if (! $selectedRoom) {
            $selectedRoom = $channels->first() ?? InternalChatRoom::with(['messages.user'])->first();
        }

        if ($selectedRoom) {
            $selectedRoom->users()->syncWithoutDetaching([$user->id]);
            // Update last read timestamp
            $selectedRoom->users()->updateExistingPivot($user->id, [
                'last_read_at' => now(),
            ]);
        }

        return view('admin.chat.index', [
            'user' => $user,
            'channels' => $channels,
            'directRooms' => $directRooms,
            'supportRooms' => $supportRooms,
            'colleagues' => $colleagues,
            'activeRoom' => $selectedRoom,
        ]);
    }

    /**
     * JSON: List rooms with unread counters for the current user.
     */
    public function rooms(Request $request): JsonResponse
    {
        $user = $request->user();
        $this->authorizeStaffAccess($user);

        $channels = InternalChatRoom::where('type', 'channel')
            ->with(['messages' => fn ($q) => $q->latest()->limit(1)])
            ->get()
            ->map(function ($room) use ($user) {
                return [
                    'id' => $room->id,
                    'name' => $room->name,
                    'type' => 'channel',
                    'unread_count' => $room->unreadCountForUser($user),
                    'last_message' => $room->messages->first()?->message,
                    'last_message_time' => $room->messages->first()?->created_at?->diffForHumans(),
                ];
            });

        $directs = $user->chatRooms()
            ->where('type', 'direct')
            ->with(['users', 'messages' => fn ($q) => $q->latest()->limit(1)])
            ->get()
            ->map(function ($room) use ($user) {
                $other = $room->users->firstWhere('id', '!=', $user->id);
                return [
                    'id' => $room->id,
                    'name' => $other ? $other->full_name : 'Direct Chat',
                    'other_user_id' => $other?->id,
                    'type' => 'direct',
                    'unread_count' => $room->unreadCountForUser($user),
                    'last_message' => $room->messages->first()?->message,
                    'last_message_time' => $room->messages->first()?->created_at?->diffForHumans(),
                ];
            });

        $supportRooms = InternalChatRoom::where('is_support', true)
            ->with(['users', 'assignedStaff', 'messages' => fn ($q) => $q->latest()->limit(1)])
            ->latest('updated_at')
            ->get()
            ->map(function ($room) use ($user) {
                $customer = $room->users->firstWhere(fn ($u) => ! $u->hasAdminBackendAccess());
                $assigned = $room->assignedStaff;
                return [
                    'id' => $room->id,
                    'name' => $customer ? $customer->full_name : ($room->name ?? 'Customer Inquiry'),
                    'customer_id' => $customer?->id,
                    'type' => 'support',
                    'assigned_staff_id' => $room->assigned_staff_id,
                    'assigned_staff_name' => $assigned?->full_name,
                    'is_assigned_to_me' => $room->assigned_staff_id === $user->id,
                    'unread_count' => $room->unreadCountForUser($user),
                    'last_message' => $room->messages->first()?->message,
                    'last_message_time' => $room->messages->first()?->created_at?->diffForHumans(),
                ];
            });

        return response()->json([
            'channels' => $channels,
            'direct_chats' => $directs,
            'support_rooms' => $supportRooms,
        ]);
    }

    /**
     * JSON: Fetch messages for a specific room (supports polling with after_id).
     */
    public function messages(Request $request, InternalChatRoom $room): JsonResponse
    {
        $user = $request->user();
        $this->authorizeStaffAccess($user);

        // Security check for direct rooms
        if ($room->type === 'direct' && ! $room->users()->where('users.id', $user->id)->exists()) {
            return response()->json(['error' => 'Unauthorized access to direct conversation.'], 403);
        }

        // If channel or support, ensure user is enrolled
        if ($room->type === 'channel' || $room->is_support) {
            $room->users()->syncWithoutDetaching([$user->id]);
        }

        // Auto-assign unassigned customer support room to this staff member
        if ($room->is_support && ! $room->assigned_staff_id) {
            $room->update(['assigned_staff_id' => $user->id]);
        }

        // Update read marker
        $room->users()->updateExistingPivot($user->id, [
            'last_read_at' => now(),
        ]);

        $query = $room->messages()->with('user.roles')->orderBy('created_at', 'asc');

        if ($request->filled('after_id')) {
            $query->where('id', '>', (int) $request->after_id);
        } else {
            $query->limit(100);
        }

        $messages = $query->get()->map(function ($msg) use ($user) {
            $senderRole = $msg->user->roles->pluck('name')->first() ?? 'Staff';
            return [
                'id' => $msg->id,
                'user_id' => $msg->user_id,
                'user_name' => $msg->user->full_name,
                'user_role' => $senderRole,
                'is_current_user' => $msg->user_id === $user->id,
                'message' => e($msg->message),
                'created_at' => $msg->created_at->format('h:i A'),
                'created_at_date' => $msg->created_at->format('M d, Y'),
            ];
        });

        return response()->json([
            'room_id' => $room->id,
            'room_name' => $room->getDisplayNameForUser($user),
            'is_support' => (bool) $room->is_support,
            'assigned_staff_id' => $room->assigned_staff_id,
            'messages' => $messages,
        ]);
    }

    /**
     * JSON: Send a message in a room.
     */
    public function sendMessage(Request $request, InternalChatRoom $room): JsonResponse
    {
        $user = $request->user();
        $this->authorizeStaffAccess($user);

        $validated = $request->validate([
            'message' => ['required', 'string', 'max:5000'],
        ]);

        // Enroll user if not already
        $room->users()->syncWithoutDetaching([$user->id]);

        // Auto-assign unassigned customer support room to this staff member
        if ($room->is_support && ! $room->assigned_staff_id) {
            $room->update(['assigned_staff_id' => $user->id]);
        }

        $message = InternalChatMessage::create([
            'room_id' => $room->id,
            'user_id' => $user->id,
            'message' => trim($validated['message']),
        ]);

        $room->touch();

        $room->users()->updateExistingPivot($user->id, [
            'last_read_at' => now(),
        ]);

        return response()->json([
            'status' => 'sent',
            'message' => [
                'id' => $message->id,
                'user_id' => $user->id,
                'user_name' => $user->full_name,
                'user_role' => $user->roles->pluck('name')->first() ?? 'Staff',
                'is_current_user' => true,
                'message' => e($message->message),
                'created_at' => $message->created_at->format('h:i A'),
            ],
        ], 201);
    }

    /**
     * JSON: Explicitly assign a support room to the current staff member.
     */
    public function claimSupport(Request $request, InternalChatRoom $room): JsonResponse
    {
        $user = $request->user();
        $this->authorizeStaffAccess($user);

        if (! $room->is_support) {
            return response()->json(['error' => 'This room is not a customer support ticket.'], 422);
        }

        $room->users()->syncWithoutDetaching([$user->id]);
        $room->update(['assigned_staff_id' => $user->id]);

        return response()->json([
            'status' => 'claimed',
            'room_id' => $room->id,
            'assigned_staff' => [
                'id' => $user->id,
                'name' => $user->full_name,
            ],
        ]);
    }

    /**
     * JSON: Start or retrieve a direct message room with a colleague.
     */
    public function startDirectChat(Request $request): JsonResponse
    {
        $user = $request->user();
        $this->authorizeStaffAccess($user);

        $request->validate([
            'colleague_id' => ['required', 'integer', 'exists:users,id'],
        ]);

        if ((int) $request->colleague_id === $user->id) {
            return response()->json(['error' => 'You cannot start a direct conversation with yourself.'], 422);
        }

        $colleague = User::findOrFail($request->colleague_id);
        if (! $colleague->hasAdminBackendAccess()) {
            return response()->json(['error' => 'You can only message staff or administrators.'], 422);
        }

        // Check if a direct room already exists between these 2 users
        $existingRoom = InternalChatRoom::where('type', 'direct')
            ->whereHas('users', fn ($q) => $q->where('users.id', $user->id))
            ->whereHas('users', fn ($q) => $q->where('users.id', $colleague->id))
            ->first();

        if (! $existingRoom) {
            $existingRoom = InternalChatRoom::create([
                'type' => 'direct',
                'name' => null,
                'created_by' => $user->id,
            ]);

            $existingRoom->users()->attach([
                $user->id => ['last_read_at' => now()],
                $colleague->id => ['last_read_at' => null],
            ]);
        }

        return response()->json([
            'room_id' => $existingRoom->id,
            'room_name' => $colleague->full_name,
            'colleague' => [
                'id' => $colleague->id,
                'name' => $colleague->full_name,
                'role' => $colleague->roles->pluck('name')->first() ?? 'Staff',
            ],
        ]);
    }

    /**
     * ==========================================
     * INTERNAL CALLS SIGNALLING & MANAGEMENT
     * ==========================================
     */

    /**
     * Initiate a new internal call to a colleague.
     */
    public function initiateCall(Request $request): JsonResponse
    {
        $user = $request->user();
        $this->authorizeStaffAccess($user);

        $validated = $request->validate([
            'receiver_id' => ['required', 'integer', 'exists:users,id'],
            'room_id' => ['nullable', 'integer', 'exists:internal_chat_rooms,id'],
            'sdp_offer' => ['nullable', 'string'],
        ]);

        if ((int) $validated['receiver_id'] === $user->id) {
            return response()->json(['error' => 'You cannot place a call to yourself.'], 422);
        }

        $receiver = User::findOrFail($validated['receiver_id']);
        if (! $receiver->hasAdminBackendAccess()) {
            return response()->json(['error' => 'Recipient must be a staff or admin member.'], 422);
        }

        // Terminate any stale ringing calls initiated by this user
        InternalCallSession::where('caller_id', $user->id)
            ->where('status', 'ringing')
            ->where('created_at', '<', now()->subMinutes(2))
            ->update(['status' => 'missed', 'ended_at' => now()]);

        // Create new call session
        $call = InternalCallSession::create([
            'caller_id' => $user->id,
            'receiver_id' => $receiver->id,
            'room_id' => $validated['room_id'] ?? null,
            'status' => 'ringing',
            'sdp_offer' => $validated['sdp_offer'] ?? null,
            'started_at' => now(),
            'ice_candidates' => [],
        ]);

        return response()->json([
            'status' => 'initiated',
            'call_id' => $call->id,
            'receiver' => [
                'id' => $receiver->id,
                'name' => $receiver->full_name,
                'role' => $receiver->roles->pluck('name')->first() ?? 'Staff',
            ],
        ], 201);
    }

    /**
     * Background Poller: Checks for incoming calls, call updates, and unread chats.
     */
    public function checkCalls(Request $request): JsonResponse
    {
        $user = $request->user();
        $this->authorizeStaffAccess($user);

        // 1. Incoming ringing call for this user OR incoming customer support call
        $incomingCall = InternalCallSession::where(function ($query) use ($user) {
                $query->where('receiver_id', $user->id)
                    ->orWhere(function ($sub) {
                        $sub->where('is_support_call', true)
                            ->whereNull('receiver_id');
                    });
            })
            ->where('caller_id', '!=', $user->id)
            ->where('status', 'ringing')
            ->where('created_at', '>=', now()->subSeconds(45))
            ->with(['caller.roles', 'room'])
            ->latest()
            ->first();

        $activeCallData = null;
        if ($incomingCall) {
            $isSupport = (bool) $incomingCall->is_support_call;
            $callerRole = $isSupport ? 'Customer' : ($incomingCall->caller->roles->pluck('name')->first() ?? 'Staff');
            $activeCallData = [
                'call_id' => $incomingCall->id,
                'type' => 'incoming',
                'is_support' => $isSupport,
                'status' => 'ringing',
                'caller' => [
                    'id' => $incomingCall->caller->id,
                    'name' => $incomingCall->caller->full_name,
                    'role' => $callerRole,
                ],
                'sdp_offer' => $incomingCall->sdp_offer,
                'created_at' => $incomingCall->created_at->toIso8601String(),
            ];
        }

        // 2. If client passed active_call_id, return its state (answer, candidates, or termination)
        $syncedCall = null;
        if ($request->filled('active_call_id')) {
            $call = InternalCallSession::with(['caller.roles', 'receiver.roles'])->find($request->active_call_id);
            if ($call && (in_array($user->id, [$call->caller_id, $call->receiver_id]) || ($call->is_support_call && ! $call->receiver_id))) {
                $syncedCall = [
                    'call_id' => $call->id,
                    'status' => $call->status,
                    'sdp_offer' => $call->sdp_offer,
                    'sdp_answer' => $call->sdp_answer,
                    'ice_candidates' => $call->ice_candidates ?? [],
                    'duration_seconds' => $call->duration_seconds,
                    'caller_name' => $call->caller->full_name,
                    'receiver_name' => $call->receiver?->full_name ?? 'Support Rep',
                ];
            }
        }

        // 3. Count total unread messages across all rooms for current user
        $totalUnread = 0;
        foreach ($user->chatRooms as $room) {
            $totalUnread += $room->unreadCountForUser($user);
        }

        return response()->json([
            'incoming_call' => $activeCallData,
            'synced_call' => $syncedCall,
            'total_unread' => $totalUnread,
        ]);
    }

    /**
     * Exchange WebRTC signaling data (SDP answer or ICE candidate).
     */
    public function signalCall(Request $request, InternalCallSession $call): JsonResponse
    {
        $user = $request->user();
        $this->authorizeStaffAccess($user);

        // If support call has no receiver yet, assign answering staff member
        if ($call->is_support_call && ! $call->receiver_id) {
            $call->update(['receiver_id' => $user->id]);
            if ($call->room && ! $call->room->assigned_staff_id) {
                $call->room->update(['assigned_staff_id' => $user->id]);
            }
        }

        if (! in_array($user->id, [$call->caller_id, $call->receiver_id])) {
            return response()->json(['error' => 'Unauthorized for this call session.'], 403);
        }

        // Handle SDP Answer from receiver
        if ($request->filled('sdp_answer')) {
            $call->update([
                'sdp_answer' => $request->sdp_answer,
                'status' => 'active',
            ]);
        }

        // Handle ICE Candidate
        if ($request->filled('ice_candidate')) {
            $existing = $call->ice_candidates ?? [];
            $existing[] = [
                'sender_id' => $user->id,
                'candidate' => $request->ice_candidate,
            ];
            $call->update(['ice_candidates' => $existing]);
        }

        return response()->json(['status' => 'signaled', 'call_status' => $call->status]);
    }

    /**
     * Update call status (accept, decline, end).
     */
    public function updateCallStatus(Request $request, InternalCallSession $call): JsonResponse
    {
        $user = $request->user();
        $this->authorizeStaffAccess($user);

        // If support call has no receiver yet, assign answering staff member
        if ($call->is_support_call && ! $call->receiver_id) {
            $call->update(['receiver_id' => $user->id]);
            if ($call->room && ! $call->room->assigned_staff_id) {
                $call->room->update(['assigned_staff_id' => $user->id]);
            }
        }

        if (! in_array($user->id, [$call->caller_id, $call->receiver_id])) {
            return response()->json(['error' => 'Unauthorized for this call session.'], 403);
        }

        $validated = $request->validate([
            'action' => ['required', 'in:accept,decline,end'],
        ]);

        $action = $validated['action'];

        if ($action === 'accept') {
            $call->update([
                'status' => 'active',
                'started_at' => now(),
            ]);
        } elseif ($action === 'decline') {
            $call->update([
                'status' => 'declined',
                'ended_at' => now(),
            ]);
        } elseif ($action === 'end') {
            $started = $call->started_at ?? $call->created_at;
            $duration = max(1, (int) now()->diffInSeconds($started));
            $call->update([
                'status' => 'ended',
                'ended_at' => now(),
                'duration_seconds' => $duration,
            ]);
        }

        return response()->json([
            'status' => 'updated',
            'call_status' => $call->status,
            'duration_seconds' => $call->duration_seconds,
        ]);
    }
}
