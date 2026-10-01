<?php

namespace Tests\Feature;

use App\Models\InternalCallSession;
use App\Models\InternalChatMessage;
use App\Models\InternalChatRoom;
use App\Models\Role;
use App\Models\User;
use Database\Seeders\InternalChatSeeder;
use Database\Seeders\RolePermissionSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class InternalChatAndCallingTest extends TestCase
{
    use RefreshDatabase;

    protected User $admin;
    protected User $staff;
    protected User $agent;
    protected User $endUser;

    protected function setUp(): void
    {
        parent::setUp();

        $this->seed(RolePermissionSeeder::class);
        $this->seed(InternalChatSeeder::class);

        $adminRole = Role::where('slug', 'admin')->firstOrFail();
        $staffRole = Role::where('slug', 'staff')->firstOrFail();
        $agentRole = Role::where('slug', 'agent')->firstOrFail();
        $endUserRole = Role::where('slug', 'end_user')->firstOrFail();

        $this->admin = User::factory()->create([
            'first_name' => 'Adewale',
            'surname' => 'Admin',
            'email' => 'adewale@vsite.ng',
            'email_verified_at' => now(),
            'phone_verified_at' => now(),
        ]);
        $this->admin->roles()->attach($adminRole);

        $this->staff = User::factory()->create([
            'first_name' => 'Suleiman',
            'surname' => 'Staff',
            'email' => 'suleiman@vsite.ng',
            'email_verified_at' => now(),
            'phone_verified_at' => now(),
        ]);
        $this->staff->roles()->attach($staffRole);

        $this->agent = User::factory()->create([
            'first_name' => 'Ibrahim',
            'surname' => 'Agent',
            'email' => 'ibrahim@vsite.ng',
            'email_verified_at' => now(),
            'phone_verified_at' => now(),
        ]);
        $this->agent->roles()->attach($agentRole);

        $this->endUser = User::factory()->create([
            'first_name' => 'Chinedu',
            'surname' => 'User',
            'email' => 'chinedu@vsite.ng',
            'email_verified_at' => now(),
            'phone_verified_at' => now(),
        ]);
        $this->endUser->roles()->attach($endUserRole);
    }

    public function test_staff_and_admins_can_access_internal_chat_while_users_and_agents_are_forbidden(): void
    {
        // Admin allowed
        $response = $this->actingAs($this->admin)->get(route('admin.chat.index'));
        $response->assertStatus(200);
        $response->assertSee('Internal Staff Communications');

        // Staff allowed
        $response = $this->actingAs($this->staff)->get(route('admin.chat.index'));
        $response->assertStatus(200);

        // Agent forbidden
        $response = $this->actingAs($this->agent)->get(route('admin.chat.index'));
        $response->assertStatus(403);

        // End user forbidden
        $response = $this->actingAs($this->endUser)->get(route('admin.chat.index'));
        $response->assertStatus(403);
    }

    public function test_admin_sidebar_displays_internal_chat_navigation_link(): void
    {
        $response = $this->actingAs($this->admin)->get(route('admin.dashboard'));
        $response->assertStatus(200);
        $response->assertSee('Internal Comms');
        $response->assertSee(route('admin.chat.index'));
        $response->assertSee('id="global-incoming-call-modal"', false);
    }

    public function test_staff_can_send_and_retrieve_chat_messages_in_channel(): void
    {
        $room = InternalChatRoom::where('name', 'Staff General')->firstOrFail();

        // 1. Admin sends message
        $response = $this->actingAs($this->admin)
            ->postJson(route('admin.chat.messages.send', $room), [
                'message' => 'Operations briefing at 10:00 AM regarding IPE batch submissions.',
            ]);

        $response->assertStatus(201);
        $response->assertJsonPath('status', 'sent');
        $response->assertJsonPath('message.user_name', $this->admin->full_name);

        $this->assertDatabaseHas('internal_chat_messages', [
            'room_id' => $room->id,
            'user_id' => $this->admin->id,
            'message' => 'Operations briefing at 10:00 AM regarding IPE batch submissions.',
        ]);

        // 2. Staff fetches messages
        $fetchResponse = $this->actingAs($this->staff)
            ->getJson(route('admin.chat.messages', $room));

        $fetchResponse->assertStatus(200);
        $fetchResponse->assertJsonFragment([
            'message' => 'Operations briefing at 10:00 AM regarding IPE batch submissions.',
        ]);
    }

    public function test_staff_can_start_direct_chat_with_another_staff_member(): void
    {
        $response = $this->actingAs($this->admin)
            ->postJson(route('admin.chat.direct'), [
                'colleague_id' => $this->staff->id,
            ]);

        $response->assertStatus(200);
        $response->assertJsonPath('room_name', $this->staff->full_name);

        $roomId = $response->json('room_id');
        $this->assertNotNull($roomId);

        $room = InternalChatRoom::find($roomId);
        $this->assertEquals('direct', $room->type);
        $this->assertTrue($room->users->contains('id', $this->admin->id));
        $this->assertTrue($room->users->contains('id', $this->staff->id));
    }

    public function test_call_lifecycle_initiate_ringing_check_accept_and_end(): void
    {
        // 1. Admin initiates voice call to staff
        $initResponse = $this->actingAs($this->admin)
            ->postJson(route('admin.chat.calls.initiate'), [
                'receiver_id' => $this->staff->id,
                'sdp_offer' => 'test_sdp_offer_payload',
            ]);

        $initResponse->assertStatus(201);
        $initResponse->assertJsonPath('status', 'initiated');
        $callId = $initResponse->json('call_id');

        $call = InternalCallSession::find($callId);
        $this->assertNotNull($call);
        $this->assertEquals('ringing', $call->status);
        $this->assertEquals($this->admin->id, $call->caller_id);
        $this->assertEquals($this->staff->id, $call->receiver_id);

        // 2. Staff background check polls and detects incoming ringing call
        $checkResponse = $this->actingAs($this->staff)
            ->getJson(route('admin.chat.calls.check'));

        $checkResponse->assertStatus(200);
        $checkResponse->assertJsonPath('incoming_call.call_id', $callId);
        $checkResponse->assertJsonPath('incoming_call.status', 'ringing');
        $checkResponse->assertJsonPath('incoming_call.caller.id', $this->admin->id);

        // 3. Staff accepts call
        $acceptResponse = $this->actingAs($this->staff)
            ->postJson(route('admin.chat.calls.status', $call), [
                'action' => 'accept',
            ]);

        $acceptResponse->assertStatus(200);
        $acceptResponse->assertJsonPath('call_status', 'active');
        $this->assertEquals('active', $call->fresh()->status);

        // 4. Signal call with WebRTC answer
        $signalResponse = $this->actingAs($this->staff)
            ->postJson(route('admin.chat.calls.signal', $call), [
                'sdp_answer' => 'test_sdp_answer_payload',
            ]);

        $signalResponse->assertStatus(200);
        $this->assertEquals('test_sdp_answer_payload', $call->fresh()->sdp_answer);

        // 5. Caller ends call
        $endResponse = $this->actingAs($this->admin)
            ->postJson(route('admin.chat.calls.status', $call), [
                'action' => 'end',
            ]);

        $endResponse->assertStatus(200);
        $endResponse->assertJsonPath('call_status', 'ended');
        $this->assertEquals('ended', $call->fresh()->status);
        $this->assertNotNull($call->fresh()->ended_at);
        $this->assertGreaterThanOrEqual(1, $call->fresh()->duration_seconds);
    }

    public function test_declined_call_updates_status_accurately(): void
    {
        $call = InternalCallSession::create([
            'caller_id' => $this->admin->id,
            'receiver_id' => $this->staff->id,
            'status' => 'ringing',
            'started_at' => now(),
        ]);

        $response = $this->actingAs($this->staff)
            ->postJson(route('admin.chat.calls.status', $call), [
                'action' => 'decline',
            ]);

        $response->assertStatus(200);
        $response->assertJsonPath('call_status', 'declined');
        $this->assertEquals('declined', $call->fresh()->status);
    }

    public function test_cannot_initiate_call_to_regular_user_or_self(): void
    {
        // Cannot call self
        $response = $this->actingAs($this->admin)
            ->postJson(route('admin.chat.calls.initiate'), [
                'receiver_id' => $this->admin->id,
            ]);

        $response->assertStatus(422);

        // Cannot call regular end user
        $response = $this->actingAs($this->admin)
            ->postJson(route('admin.chat.calls.initiate'), [
                'receiver_id' => $this->endUser->id,
            ]);

        $response->assertStatus(422);
    }
}
