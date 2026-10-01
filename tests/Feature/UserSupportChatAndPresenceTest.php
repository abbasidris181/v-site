<?php

namespace Tests\Feature;

use App\Models\InternalCallSession;
use App\Models\InternalChatMessage;
use App\Models\InternalChatRoom;
use App\Models\Role;
use App\Models\User;
use App\Models\Wallet;
use Database\Seeders\InternalChatSeeder;
use Database\Seeders\RolePermissionSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class UserSupportChatAndPresenceTest extends TestCase
{
    use RefreshDatabase;

    protected User $user;
    protected User $admin;
    protected User $staff;

    protected function setUp(): void
    {
        parent::setUp();

        $this->seed(RolePermissionSeeder::class);
        $this->seed(InternalChatSeeder::class);

        $adminRole = Role::where('slug', 'admin')->firstOrFail();
        $staffRole = Role::where('slug', 'staff')->firstOrFail();
        $endUserRole = Role::where('slug', 'end_user')->firstOrFail();

        $this->user = User::factory()->create([
            'email' => 'customer@vsite.ng',
            'first_name' => 'Amina',
            'surname' => 'Bello',
            'email_verified_at' => now(),
            'phone_verified_at' => now(),
            'last_seen_at' => now(),
        ]);
        $this->user->roles()->attach($endUserRole);
        Wallet::create(['user_id' => $this->user->id, 'balance' => 500000]);

        $this->admin = User::factory()->create([
            'email' => 'admin_lead@vsite.ng',
            'first_name' => 'Adewale',
            'surname' => 'Admin',
            'email_verified_at' => now(),
            'phone_verified_at' => now(),
            'last_seen_at' => null, // Initially offline
        ]);
        $this->admin->roles()->attach($adminRole);
        Wallet::create(['user_id' => $this->admin->id, 'balance' => 0]);

        $this->staff = User::factory()->create([
            'email' => 'staff_rep@vsite.ng',
            'first_name' => 'Chidi',
            'surname' => 'Support',
            'email_verified_at' => now(),
            'phone_verified_at' => now(),
            'last_seen_at' => null,
        ]);
        $this->staff->roles()->attach($staffRole);
        Wallet::create(['user_id' => $this->staff->id, 'balance' => 0]);
    }

    public function test_user_sees_offline_status_when_no_admin_is_online(): void
    {
        $response = $this->actingAs($this->user)
            ->getJson('/support/chat/status');

        $response->assertStatus(200);
        $response->assertJson([
            'presence' => [
                'has_attending_admin' => false,
                'name' => 'Customer Support Desk',
                'is_online' => false,
                'badge' => 'offline',
            ],
        ]);
    }

    public function test_user_sees_online_status_when_an_admin_is_active(): void
    {
        // Admin active within last 2 minutes
        $this->admin->update(['last_seen_at' => now()->subMinute()]);

        $response = $this->actingAs($this->user)
            ->getJson('/support/chat/status');

        $response->assertStatus(200);
        $response->assertJson([
            'presence' => [
                'has_attending_admin' => false,
                'name' => 'Customer Support Desk',
                'is_online' => true,
                'status_text' => 'online',
                'badge' => 'online',
            ],
        ]);
    }

    public function test_user_can_send_support_message_and_retrieve_stream(): void
    {
        $sendResponse = $this->actingAs($this->user)
            ->postJson('/support/chat/messages', [
                'message' => 'Hello, I need help verifying a NIN slip.',
            ]);

        $sendResponse->assertStatus(201);
        $sendResponse->assertJsonPath('status', 'sent');
        $sendResponse->assertJsonPath('message.message', 'Hello, I need help verifying a NIN slip.');

        // User retrieves message list
        $fetchResponse = $this->actingAs($this->user)
            ->getJson('/support/chat/messages');

        $fetchResponse->assertStatus(200);
        $fetchResponse->assertJsonFragment([
            'message' => 'Hello, I need help verifying a NIN slip.',
            'is_current_user' => true,
        ]);
    }

    public function test_attending_admin_name_appears_on_name_side_after_admin_claims_or_responds(): void
    {
        // Customer creates room with message
        $this->actingAs($this->user)
            ->postJson('/support/chat/messages', [
                'message' => 'Need assistance please.',
            ]);

        $room = InternalChatRoom::where('is_support', true)->where('created_by', $this->user->id)->first();
        $this->assertNotNull($room);
        $this->assertNull($room->assigned_staff_id);

        // Staff member logs in, becomes online, and responds
        $this->staff->update(['last_seen_at' => now()]);
        $staffReply = $this->actingAs($this->staff)
            ->postJson("/admin/chat/rooms/{$room->id}/messages", [
                'message' => 'Good day Amina! My name is Chidi and I am attending to you.',
            ]);

        $staffReply->assertStatus(201);

        // Room now assigned to Chidi Support
        $room->refresh();
        $this->assertEquals($this->staff->id, $room->assigned_staff_id);

        // User checks status: must see Chidi Support on the name side
        $userStatus = $this->actingAs($this->user)
            ->getJson('/support/chat/status');

        $userStatus->assertStatus(200);
        $userStatus->assertJson([
            'presence' => [
                'has_attending_admin' => true,
                'name' => 'Chidi Support',
                'role_label' => 'Support Representative',
                'is_online' => true,
                'status_text' => 'online',
            ],
        ]);
    }

    public function test_customer_can_initiate_support_call_and_admin_can_answer(): void
    {
        // User starts audio call
        $callResponse = $this->actingAs($this->user)
            ->postJson('/support/chat/call', [
                'sdp_offer' => 'user_webrtc_offer',
            ]);

        $callResponse->assertStatus(201);
        $callId = $callResponse->json('call_id');
        $this->assertNotNull($callId);

        $call = InternalCallSession::find($callId);
        $this->assertTrue($call->is_support_call);
        $this->assertEquals('ringing', $call->status);

        // Online admin checks calls
        $this->admin->update(['last_seen_at' => now()]);
        $checkCalls = $this->actingAs($this->admin)
            ->getJson('/admin/chat/calls/check');

        $checkCalls->assertStatus(200);
        $checkCalls->assertJsonPath('incoming_call.call_id', $callId);
        $checkCalls->assertJsonPath('incoming_call.is_support', true);
        $checkCalls->assertJsonPath('incoming_call.caller.name', 'Amina Bello');

        // Admin accepts call
        $acceptResponse = $this->actingAs($this->admin)
            ->postJson("/admin/chat/calls/{$callId}/status", [
                'action' => 'accept',
            ]);

        $acceptResponse->assertStatus(200);

        // Call is now active with admin as receiver
        $call->refresh();
        $this->assertEquals('active', $call->status);
        $this->assertEquals($this->admin->id, $call->receiver_id);

        // Customer polls call status: sees active status and Adewale Admin attending
        $pollResponse = $this->actingAs($this->user)
            ->getJson("/support/chat/call/status?call_id={$callId}");

        $pollResponse->assertStatus(200);
        $pollResponse->assertJson([
            'call_id' => $callId,
            'status' => 'active',
            'attending_admin' => [
                'id' => $this->admin->id,
                'name' => 'Adewale Admin',
                'role' => 'Support Representative',
            ],
        ]);
    }

    public function test_user_portal_renders_sidebar_live_chat_and_no_dashboard_card(): void
    {
        $response = $this->actingAs($this->user)->get('/dashboard');

        $response->assertStatus(200);
        // Live chat item exists in sidebar
        $response->assertSee('sidebar-live-chat-btn', false);
        $response->assertSee('sidebar-chat-status-dot', false);
        $response->assertSee('sidebar-chat-status-text', false);
        $response->assertSee('Live Chat');

        // Removed from dashboard body and no floating launcher
        $response->assertDontSee('dash-presence-dot');
        $response->assertDontSee('dash-presence-text');
        $response->assertDontSee('support-launcher-btn');
    }

    public function test_admin_can_view_and_interact_with_support_room_in_admin_chat(): void
    {
        // Customer creates support room
        $this->actingAs($this->user)->postJson('/support/chat/messages', [
            'message' => 'Hello from customer Amina',
        ]);

        $room = InternalChatRoom::where('is_support', true)->where('created_by', $this->user->id)->first();
        $this->assertNotNull($room);

        // Admin loads /admin/chat?room_id={$room->id}
        $response = $this->actingAs($this->admin)->get("/admin/chat?room_id={$room->id}");
        $response->assertStatus(200);
        $response->assertSee('Customer Support Desk');
        $response->assertSee('Amina Bello');
        $response->assertSee('Hello from customer Amina');

        // Admin fetches room messages via API
        $apiResponse = $this->actingAs($this->admin)->getJson("/admin/chat/rooms/{$room->id}/messages");
        $apiResponse->assertStatus(200);
        $apiResponse->assertJsonPath('is_support', true);
        $apiResponse->assertSee('Amina Bello');
    }

    public function test_user_portal_renders_call_icon_for_chat_with_admin(): void
    {
        $response = $this->actingAs($this->user)->get('/dashboard');

        $response->assertStatus(200);
        $response->assertSee('sidebar-call-icon', false);
        $response->assertSee('sidebar-quick-call-btn', false);
        $response->assertSee('header-call-btn', false);
        $response->assertSee('user-call-equalizer', false);
        $response->assertSee('simulate-admin-answer-btn', false);
        $response->assertSee('Voice Call Active');
    }

    public function test_customer_can_simulate_admin_call_pickup_and_speaking(): void
    {
        // 1. Customer initiates call
        $initResponse = $this->actingAs($this->user)->postJson('/support/chat/call', [
            'sdp_offer' => 'simulated_webrtc_offer',
        ]);
        $initResponse->assertStatus(201);
        $callId = $initResponse->json('call_id');

        // 2. Customer simulates admin answering
        $simulateResponse = $this->actingAs($this->user)->postJson("/support/chat/call/{$callId}/simulate-answer");
        $simulateResponse->assertStatus(200);
        $simulateResponse->assertJson([
            'status' => 'active',
            'call_id' => $callId,
        ]);

        // 3. Status poll confirms call is active with attending admin
        $statusResponse = $this->actingAs($this->user)->getJson("/support/chat/call/status?call_id={$callId}");
        $statusResponse->assertStatus(200);
        $statusResponse->assertJson([
            'status' => 'active',
            'call_id' => $callId,
        ]);
        $this->assertNotNull($statusResponse->json('attending_admin'));
    }
}

