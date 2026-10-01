<?php

namespace Tests\Feature;

use App\Models\Service;
use App\Models\ServiceRequest;
use App\Models\User;
use App\Models\Wallet;
use App\Services\WalletService;
use Database\Seeders\AnnouncementSeeder;
use Database\Seeders\RolePermissionSeeder;
use Database\Seeders\ServiceCatalogSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ManualProcessingQueueTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        $this->seed(RolePermissionSeeder::class);
        $this->seed(ServiceCatalogSeeder::class);
        $this->seed(AnnouncementSeeder::class);
    }

    public function test_admin_and_staff_can_access_manual_queue_while_agent_and_user_are_forbidden(): void
    {
        $staff = User::where('email', 'staff@vsite.ng')->first();
        $admin = User::where('email', 'admin@vsite.ng')->first();
        $agent = User::where('email', 'agent@vsite.ng')->first();
        $endUser = User::where('email', 'user@vsite.ng')->first();

        // Staff & Admin access allowed (200 OK)
        $this->actingAs($staff)->get('/admin/queue')->assertStatus(200)->assertSee('Service Requests');
        $this->actingAs($admin)->get('/admin/queue')->assertStatus(200)->assertSee('Service Requests');

        // Agent & End User access forbidden (403 Forbidden)
        $this->actingAs($agent)->get('/admin/queue')->assertStatus(403);
        $this->actingAs($endUser)->get('/admin/queue')->assertStatus(403);
    }

    public function test_staff_can_pick_and_claim_a_pending_manual_job(): void
    {
        $staff = User::where('email', 'staff@vsite.ng')->first();
        $user = User::where('email', 'user@vsite.ng')->first();
        $service = Service::where('slug', 'ipe-clearing')->first();

        $serviceRequest = ServiceRequest::create([
            'user_id' => $user->id,
            'service_id' => $service->id,
            'reference' => 'SR_TEST_PICK_001',
            'tracking_input' => 'ABCDE12345FGHIJ',
            'input_payload' => ['tracking_id' => 'ABCDE12345FGHIJ'],
            'amount_charged' => 500.00,
            'status' => 'pending',
        ]);

        $response = $this->actingAs($staff)->post("/admin/queue/{$serviceRequest->id}/pick");

        $response->assertStatus(302);
        $this->assertDatabaseHas('service_requests', [
            'id' => $serviceRequest->id,
            'status' => 'processing',
            'assigned_to' => $staff->id,
        ]);
    }

    public function test_staff_can_complete_a_job_with_resolution_payload(): void
    {
        $staff = User::where('email', 'staff@vsite.ng')->first();
        $user = User::where('email', 'user@vsite.ng')->first();
        $service = Service::where('slug', 'ipe-clearing')->first();

        $serviceRequest = ServiceRequest::create([
            'user_id' => $user->id,
            'service_id' => $service->id,
            'reference' => 'SR_TEST_COMPLETE_001',
            'tracking_input' => 'IPE998877665544',
            'input_payload' => ['tracking_id' => 'IPE998877665544'],
            'amount_charged' => 500.00,
            'status' => 'processing',
            'assigned_to' => $staff->id,
        ]);

        $response = $this->actingAs($staff)->post("/admin/queue/{$serviceRequest->id}/complete", [
            'resolution_reference' => 'CLR_VERIFIED_9999',
            'admin_notes' => 'Successfully cleared with immigration registry',
        ]);

        $response->assertStatus(302);
        $serviceRequest->refresh();

        $this->assertEquals('completed', $serviceRequest->status);
        $this->assertEquals($staff->id, $serviceRequest->processed_by);
        $this->assertNotNull($serviceRequest->completed_at);
        $this->assertEquals('CLR_VERIFIED_9999', $serviceRequest->result_payload['resolution_reference']);
    }

    public function test_failing_a_request_records_rejection_and_does_NOT_automatically_refund_customer(): void
    {
        $staff = User::where('email', 'staff@vsite.ng')->first();
        $user = User::where('email', 'user@vsite.ng')->first();
        $service = Service::where('slug', 'nin-validation')->first();

        // Customer starts with seeded balance
        $wallet = Wallet::where('user_id', $user->id)->first();
        $initialBalance = (float) $wallet->balance;

        $serviceRequest = ServiceRequest::create([
            'user_id' => $user->id,
            'service_id' => $service->id,
            'reference' => 'SR_TEST_FAIL_001',
            'tracking_input' => '12345678901',
            'input_payload' => ['nin' => '12345678901'],
            'amount_charged' => 500.00,
            'status' => 'processing',
            'assigned_to' => $staff->id,
        ]);

        $response = $this->actingAs($staff)->post("/admin/queue/{$serviceRequest->id}/fail", [
            'rejection_reason' => 'NIN record does not match national biometric register',
            'admin_notes' => 'Discrepancy in date of birth',
        ]);

        $response->assertStatus(302);
        $serviceRequest->refresh();
        $wallet->refresh();

        // Status is failed
        $this->assertEquals('failed', $serviceRequest->status);
        $this->assertEquals($staff->id, $serviceRequest->processed_by);
        $this->assertEquals('NIN record does not match national biometric register', $serviceRequest->rejection_reason);

        // CRITICAL CONSTRAINT: Customer balance must NOT have been refunded automatically!
        $this->assertEquals($initialBalance, (float) $wallet->balance);
        $this->assertNull($serviceRequest->refunded_at);
        $this->assertNull($serviceRequest->refunded_by);
        $this->assertFalse($serviceRequest->isRefunded());
    }

    public function test_authorized_staff_can_manually_issue_refund_for_failed_request(): void
    {
        $staff = User::where('email', 'staff@vsite.ng')->first();
        $user = User::where('email', 'user@vsite.ng')->first();
        $service = Service::where('slug', 'nin-validation')->first();

        $wallet = Wallet::where('user_id', $user->id)->first();
        $initialBalance = (float) $wallet->balance;

        $serviceRequest = ServiceRequest::create([
            'user_id' => $user->id,
            'service_id' => $service->id,
            'reference' => 'SR_TEST_MANUAL_REFUND_001',
            'tracking_input' => '12345678901',
            'input_payload' => ['nin' => '12345678901'],
            'amount_charged' => 500.00,
            'status' => 'failed',
            'rejection_reason' => 'Government provider portal timeout',
        ]);

        // Staff clicks the Manual Refund button
        $response = $this->actingAs($staff)->post("/admin/queue/{$serviceRequest->id}/refund");

        $response->assertStatus(302);
        $serviceRequest->refresh();
        $wallet->refresh();

        // Customer wallet balance credited by ₦500.00
        $this->assertEquals($initialBalance + 500.00, (float) $wallet->balance);

        // Request updated with refund audit trail
        $this->assertNotNull($serviceRequest->refunded_at);
        $this->assertEquals($staff->id, $serviceRequest->refunded_by);
        $this->assertTrue($serviceRequest->isRefunded());

        // Double-entry ledger contains the refund record
        $this->assertDatabaseHas('wallet_transactions', [
            'user_id' => $user->id,
            'type' => 'credit',
            'category' => 'manual_refund',
            'amount' => 500.00,
            'idempotency_key' => "REFUND_SR_{$serviceRequest->id}",
        ]);
    }

    public function test_cannot_issue_duplicate_refunds_on_the_same_request(): void
    {
        $staff = User::where('email', 'staff@vsite.ng')->first();
        $user = User::where('email', 'user@vsite.ng')->first();
        $service = Service::where('slug', 'nin-validation')->first();

        $serviceRequest = ServiceRequest::create([
            'user_id' => $user->id,
            'service_id' => $service->id,
            'reference' => 'SR_TEST_DUP_REFUND_001',
            'tracking_input' => '12345678901',
            'input_payload' => ['nin' => '12345678901'],
            'amount_charged' => 500.00,
            'status' => 'failed',
            'rejection_reason' => 'Invalid biometric match',
        ]);

        // First refund succeeds
        $this->actingAs($staff)->post("/admin/queue/{$serviceRequest->id}/refund");

        // Second refund fails with session error
        $response = $this->actingAs($staff)->post("/admin/queue/{$serviceRequest->id}/refund");
        $response->assertSessionHas('error');
    }

    public function test_queue_filters_by_status_and_search_query(): void
    {
        $staff = User::where('email', 'staff@vsite.ng')->first();
        $user = User::where('email', 'user@vsite.ng')->first();
        $service = Service::where('slug', 'ipe-clearing')->first();

        // Create 2 jobs: one pending, one completed
        ServiceRequest::create([
            'user_id' => $user->id,
            'service_id' => $service->id,
            'reference' => 'SR_FILTER_PENDING_01',
            'tracking_input' => 'UNIQUE_TRACKING_123',
            'input_payload' => ['tracking_id' => 'UNIQUE_TRACKING_123'],
            'amount_charged' => 500.00,
            'status' => 'pending',
        ]);

        ServiceRequest::create([
            'user_id' => $user->id,
            'service_id' => $service->id,
            'reference' => 'SR_FILTER_COMPLETED_02',
            'tracking_input' => 'OTHER_TRACKING_456',
            'input_payload' => ['tracking_id' => 'OTHER_TRACKING_456'],
            'amount_charged' => 500.00,
            'status' => 'completed',
        ]);

        $res = $this->actingAs($staff)->get('/admin/queue');
        $res->assertSee('SR_FILTER_PENDING_01');
        $res->assertSee('SR_FILTER_COMPLETED_02');
        $res->assertSee('Total IPE Records');
    }

    public function test_manual_queue_renders_individual_service_sections(): void
    {
        $admin = User::where('email', 'admin@vsite.ng')->first();

        $response = $this->actingAs($admin)->get('/admin/queue');

        $response->assertStatus(200);
        $response->assertSee('Service Requests');
        $response->assertSee('IPE Clearing');
        $response->assertSee('NIN Validation');
        $response->assertSee('Modification IPE');
        $response->assertSee('BVN Retrieval by Phone Number');
        $response->assertSee('Self-Service Delinking');
        $response->assertSee('Total IPE Records');
        $response->assertSee('Ref');
        $response->assertSee('Date and Time');
        $response->assertSee('Email');
        $response->assertSee('Old Tracking ID');
        $response->assertDontSee('<th class="py-3.5 px-4 font-semibold">Reply</th>', false);
        $response->assertDontSee('<th class="py-3.5 px-4 font-semibold text-center">Refund</th>', false);
        $response->assertSee('Action');
    }

    public function test_manual_queue_can_filter_by_individual_service_section(): void
    {
        $admin = User::where('email', 'admin@vsite.ng')->first();
        $ipeService = Service::where('slug', 'ipe-clearing')->first();

        $response = $this->actingAs($admin)->get("/admin/queue?service_id={$ipeService->id}");

        $response->assertStatus(200);
        $response->assertSee('Total IPE Records');
        $response->assertSee('Ref');
        $response->assertSee('Date and Time');
        $response->assertSee('Email');
        $response->assertSee('Old Tracking ID');
        $response->assertDontSee('<th class="py-3.5 px-4 font-semibold">Reply</th>', false);
        $response->assertDontSee('<th class="py-3.5 px-4 font-semibold text-center">Refund</th>', false);
        $response->assertSee('Action');
    }

    public function test_all_dropdown_services_under_service_requests_render_matching_table(): void
    {
        $admin = User::where('email', 'admin@vsite.ng')->first();
        $services = [
            'ipe-clearing',
            'nin-validation',
            'modification-ipe',
            'bvn-retrieval',
            'self-service-delinking',
            'personalization',
        ];

        foreach ($services as $slug) {
            $response = $this->actingAs($admin)->get("/admin/queue?service_slug={$slug}");
            $response->assertStatus(200);
            $response->assertSee('Pending');
            $response->assertSee('Failed');
            $response->assertSee('Successful');
            $response->assertSee('Ref');
            $response->assertSee('Date and Time');
            $response->assertSee('Email');
            if (in_array($slug, ['ipe-clearing', 'modification-ipe'])) {
                $response->assertSee('Old Tracking ID');
            } else {
                $response->assertSee('Input');
            }
            $response->assertDontSee('<th class="py-3.5 px-4 font-semibold">Reply</th>', false);
            $response->assertDontSee('<th class="py-3.5 px-4 font-semibold text-center">Refund</th>', false);
            $response->assertSee('Action');
        }
    }

    public function test_admin_dashboard_renders_cleanly_without_queue_breakdown(): void
    {
        $admin = User::where('email', 'admin@vsite.ng')->first();

        $response = $this->actingAs($admin)->get('/admin');

        $response->assertStatus(200);
        $response->assertSee('Dashboard');
        $response->assertDontSee('Submitted Request Queues');
    }

    public function test_any_admin_user_can_directly_alter_pending_submitted_request_without_staff_assignment(): void
    {
        $admin = User::where('email', 'admin@vsite.ng')->first();
        $user = User::where('email', 'user@vsite.ng')->first();
        $service = Service::where('slug', 'ipe-clearing')->first();

        // Create a request in pending state (no staff assigned)
        $pendingRequest = ServiceRequest::create([
            'user_id' => $user->id,
            'service_id' => $service->id,
            'reference' => 'SR_TEST_DIRECT_ALTER_001',
            'tracking_input' => 'IPE_DIRECT_12345',
            'input_payload' => ['tracking_id' => 'IPE_DIRECT_12345'],
            'amount_charged' => 500.00,
            'status' => 'pending',
            'assigned_to' => null,
        ]);

        // 1. Show page renders Complete and Fail forms directly for pending request
        $response = $this->actingAs($admin)->get("/admin/queue/{$pendingRequest->id}");
        $response->assertStatus(200);
        $response->assertSee('Complete Request');
        $response->assertSee('Reject / Fail Request');
        $response->assertDontSee('Assigned Handler:');
        $response->assertDontSee('Pick & Claim Job');

        // 2. Admin can directly complete without picking or assigning
        $completeResponse = $this->actingAs($admin)->post("/admin/queue/{$pendingRequest->id}/complete", [
            'resolution_reference' => 'CLR_DIRECT_COMPLETION',
            'admin_notes' => 'Completed directly from dashboard',
        ]);
        $completeResponse->assertStatus(302);

        $pendingRequest->refresh();
        $this->assertEquals('completed', $pendingRequest->status);
        $this->assertEquals($admin->id, $pendingRequest->processed_by);
        $this->assertNotNull($pendingRequest->completed_at);
    }

    public function test_submitted_request_views_do_not_contain_assigned_staff_columns(): void
    {
        $admin = User::where('email', 'admin@vsite.ng')->first();

        // 1. Main queue index view
        $queueResponse = $this->actingAs($admin)->get('/admin/queue');
        $queueResponse->assertStatus(200);
        $queueResponse->assertDontSee('Assigned Staff');

        // 2. Admin dashboard view
        $dashResponse = $this->actingAs($admin)->get('/admin');
        $dashResponse->assertStatus(200);
        $dashResponse->assertDontSee('Assigned Staff');
        $dashResponse->assertDontSee('Awaiting staff assignment');
    }
}
