<?php

namespace App\Http\Controllers;

use App\Models\InternalCallSession;
use App\Models\InternalChatMessage;
use App\Models\InternalChatRoom;
use App\Models\User;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class UserSupportChatController extends Controller
{
    /**
     * Get or create the authenticated user's support chat room.
     */
    protected function getOrCreateSupportRoom(User $user): InternalChatRoom
    {
        $room = InternalChatRoom::firstOrCreate(
            [
                'created_by' => $user->id,
                'is_support' => true,
            ],
            [
                'name' => 'Support: ' . $user->full_name,
                'type' => 'support',
            ]
        );

        $room->users()->syncWithoutDetaching([$user->id]);

        return $room->load('assignedStaff.roles');
    }

    /**
     * Build WhatsApp-styled presence payload for the user's support conversation.
     */
    protected function buildPresenceData(InternalChatRoom $room): array
    {
        $attending = $room->assignedStaff;

        if ($attending) {
            $isOnline = $attending->isOnline();
            $statusText = $isOnline
                ? 'online'
                : ($attending->last_seen_at ? 'last seen ' . $attending->last_seen_at->diffForHumans() : 'offline');

            return [
                'has_attending_admin' => true,
                'name' => $attending->full_name,
                'role_label' => 'Support Representative',
                'admin_id' => $attending->id,
                'is_online' => $isOnline,
                'status_text' => $statusText,
                'avatar_letter' => strtoupper(substr($attending->first_name ?: 'A', 0, 1)),
                'badge' => $isOnline ? 'online' : 'offline',
            ];
        }

        // No individual admin assigned yet - check if ANY admin/staff is online
        $anyAdminOnline = User::isAnyAdminOnline();
        $statusText = $anyAdminOnline ? 'online' : 'offline • staff away';

        return [
            'has_attending_admin' => false,
            'name' => 'Customer Support Desk',
            'role_label' => 'Live Support Team',
            'admin_id' => null,
            'is_online' => $anyAdminOnline,
            'status_text' => $statusText,
            'avatar_letter' => 'S',
            'badge' => $anyAdminOnline ? 'online' : 'offline',
        ];
    }

    /**
     * JSON: Return current live presence status and room info.
     */
    public function status(Request $request): JsonResponse
    {
        $user = $request->user();
        $room = $this->getOrCreateSupportRoom($user);
        $presence = $this->buildPresenceData($room);
        $unreadCount = $room->unreadCountForUser($user);

        return response()->json([
            'room_id' => $room->id,
            'presence' => $presence,
            'unread_count' => $unreadCount,
            'user' => [
                'id' => $user->id,
                'name' => $user->full_name,
            ],
        ]);
    }

    /**
     * JSON: Fetch messages for the user's support room.
     */
    public function messages(Request $request): JsonResponse
    {
        $user = $request->user();
        $room = $this->getOrCreateSupportRoom($user);

        // Update customer's read marker
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
            $isCustomer = ($msg->user_id === $user->id);
            $isStaff = $msg->user->hasAdminBackendAccess();
            $roleName = $isStaff ? ($msg->user->roles->first()?->name ?? 'Support Staff') : 'Customer';

            return [
                'id' => $msg->id,
                'user_id' => $msg->user_id,
                'user_name' => $isCustomer ? 'You' : $msg->user->full_name,
                'user_role' => $roleName,
                'is_current_user' => $isCustomer,
                'is_staff' => $isStaff,
                'message' => e($msg->message),
                'created_at' => $msg->created_at->format('h:i A'),
                'created_at_date' => $msg->created_at->format('M d, Y'),
            ];
        });

        // If an admin replied in this room, ensure assigned_staff_id is set
        if (! $room->assigned_staff_id) {
            $latestStaffMsg = $room->messages()
                ->where('user_id', '!=', $user->id)
                ->latest()
                ->first();

            if ($latestStaffMsg && $latestStaffMsg->user->hasAdminBackendAccess()) {
                $room->update(['assigned_staff_id' => $latestStaffMsg->user_id]);
                $room->load('assignedStaff.roles');
            }
        }

        return response()->json([
            'room_id' => $room->id,
            'presence' => $this->buildPresenceData($room),
            'messages' => $messages,
        ]);
    }

    /**
     * JSON: Send a support message from the customer.
     */
    public function sendMessage(Request $request): JsonResponse
    {
        $user = $request->user();
        $room = $this->getOrCreateSupportRoom($user);

        $validated = $request->validate([
            'message' => ['required', 'string', 'max:5000'],
        ]);

        $message = InternalChatMessage::create([
            'room_id' => $room->id,
            'user_id' => $user->id,
            'message' => trim($validated['message']),
        ]);

        // Touch room updated_at so it bumps to top for admins
        $room->touch();

        $room->users()->updateExistingPivot($user->id, [
            'last_read_at' => now(),
        ]);

        return response()->json([
            'status' => 'sent',
            'presence' => $this->buildPresenceData($room),
            'message' => [
                'id' => $message->id,
                'user_id' => $user->id,
                'user_name' => 'You',
                'user_role' => 'Customer',
                'is_current_user' => true,
                'is_staff' => false,
                'message' => e($message->message),
                'created_at' => $message->created_at->format('h:i A'),
            ],
        ], 201);
    }

    /**
     * JSON: Customer initiates a live audio call to Support.
     */
    public function initiateCall(Request $request): JsonResponse
    {
        $user = $request->user();
        $room = $this->getOrCreateSupportRoom($user);

        // Cancel any previous hanging ringing calls from this user
        InternalCallSession::where('caller_id', $user->id)
            ->where('status', 'ringing')
            ->update(['status' => 'missed', 'ended_at' => now()]);

        // If assigned staff exists, route directly to them; otherwise null receiver so any online staff can pick up
        $receiverId = $room->assigned_staff_id;

        $call = InternalCallSession::create([
            'caller_id' => $user->id,
            'receiver_id' => $receiverId,
            'room_id' => $room->id,
            'is_support_call' => true,
            'status' => 'ringing',
            'sdp_offer' => $request->sdp_offer ?? null,
            'started_at' => now(),
            'ice_candidates' => [],
        ]);

        return response()->json([
            'status' => 'initiated',
            'call_id' => $call->id,
            'presence' => $this->buildPresenceData($room),
        ], 201);
    }

    /**
     * JSON: Customer polls call status (checks if an admin answered or connected).
     */
    public function callStatus(Request $request): JsonResponse
    {
        $user = $request->user();

        $request->validate([
            'call_id' => ['required', 'integer', 'exists:internal_call_sessions,id'],
        ]);

        $call = InternalCallSession::with(['receiver.roles', 'room.assignedStaff.roles'])
            ->where('id', $request->call_id)
            ->where('caller_id', $user->id)
            ->firstOrFail();

        // If receiver was assigned during pick-up, ensure room assigns them
        if ($call->receiver_id && $call->room && ! $call->room->assigned_staff_id) {
            $call->room->update(['assigned_staff_id' => $call->receiver_id]);
            $call->room->load('assignedStaff.roles');
        }

        $attendingAdmin = null;
        if ($call->receiver) {
            $attendingAdmin = [
                'id' => $call->receiver->id,
                'name' => $call->receiver->full_name,
                'role' => 'Support Representative',
                'is_online' => $call->receiver->isOnline(),
            ];
        } elseif ($call->room && $call->room->assignedStaff) {
            $attendingAdmin = [
                'id' => $call->room->assignedStaff->id,
                'name' => $call->room->assignedStaff->full_name,
                'role' => 'Support Representative',
                'is_online' => $call->room->assignedStaff->isOnline(),
            ];
        }

        return response()->json([
            'call_id' => $call->id,
            'status' => $call->status,
            'sdp_offer' => $call->sdp_offer,
            'sdp_answer' => $call->sdp_answer,
            'ice_candidates' => $call->ice_candidates ?? [],
            'duration_seconds' => $call->duration_seconds,
            'attending_admin' => $attendingAdmin,
        ]);
    }

    /**
     * JSON: WebRTC signaling from customer (ICE candidate or offer update).
     */
    public function signalCall(Request $request, InternalCallSession $call): JsonResponse
    {
        $user = $request->user();

        if ($call->caller_id !== $user->id) {
            return response()->json(['error' => 'Unauthorized call session.'], 403);
        }

        if ($request->filled('sdp_offer')) {
            $call->update(['sdp_offer' => $request->sdp_offer]);
        }

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
     * JSON: Customer ends call.
     */
    public function endCall(Request $request, InternalCallSession $call): JsonResponse
    {
        $user = $request->user();

        if ($call->caller_id !== $user->id) {
            return response()->json(['error' => 'Unauthorized call session.'], 403);
        }

        $started = $call->started_at ?? $call->created_at;
        $duration = max(1, (int) now()->diffInSeconds($started));

        $call->update([
            'status' => 'ended',
            'ended_at' => now(),
            'duration_seconds' => $duration,
        ]);

        return response()->json([
            'status' => 'ended',
            'duration_seconds' => $duration,
        ]);
    }

    /**
     * JSON: Helper for local testing / sandbox to simulate admin picking up the call.
     */
    public function simulateAnswer(Request $request, InternalCallSession $call): JsonResponse
    {
        $user = $request->user();

        if ($call->caller_id !== $user->id) {
            return response()->json(['error' => 'Unauthorized call session.'], 403);
        }

        if ($call->status !== 'ringing') {
            return response()->json(['message' => 'Call is not in ringing state.', 'status' => $call->status]);
        }

        // Find an admin or staff user to assign as answering party
        $answeringAdmin = \App\Models\User::whereHas('roles', function ($query) {
            $query->whereIn('slug', ['super_admin', 'admin', 'staff']);
        })->first();

        $adminId = $answeringAdmin ? $answeringAdmin->id : null;

        $call->update([
            'receiver_id' => $adminId,
            'status' => 'active',
            'started_at' => now(),
            'sdp_answer' => 'simulated_admin_answer_audio',
        ]);

        if ($call->room && $adminId && ! $call->room->assigned_staff_id) {
            $call->room->update(['assigned_staff_id' => $adminId]);
        }

        return response()->json([
            'status' => 'active',
            'call_id' => $call->id,
            'admin' => $answeringAdmin ? [
                'id' => $answeringAdmin->id,
                'name' => $answeringAdmin->full_name,
                'role' => $answeringAdmin->roles->pluck('name')->first() ?? 'Support Representative',
            ] : null,
        ]);
    }
}

