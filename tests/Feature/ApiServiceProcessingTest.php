<?php

namespace Tests\Feature;

use App\Models\Service;
use App\Models\ServiceRequest;
use App\Models\User;
use App\Models\Wallet;
use App\Services\Providers\ProviderManager;
use Database\Seeders\AnnouncementSeeder;
use Database\Seeders\RolePermissionSeeder;
use Database\Seeders\ServiceCatalogSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ApiServiceProcessingTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        $this->seed(RolePermissionSeeder::class);
        $this->seed(ServiceCatalogSeeder::class);
        $this->seed(AnnouncementSeeder::class);
    }

    public function test_user_can_perform_nin_verification_with_wallet_debit_and_result_payload(): void
    {
        $user = User::where('email', 'user@vsite.ng')->first();
        $service = Service::where('slug', 'nin-verification')->first();
        $wallet = Wallet::where('user_id', $user->id)->first();
        $initialBalance = (float) $wallet->balance; // Seeded at 8000.00

        $response = $this->actingAs($user)->post('/services/nin-verification', [
            'tracking_input' => '12345678901',
            'consent' => 1,
        ]);

        $response->assertStatus(302);
        $response->assertSessionHas('api_result');
        $response->assertSessionHas('success');

        $wallet->refresh();
        $this->assertEquals($initialBalance - 500.00, (float) $wallet->balance);

        $request = ServiceRequest::where('user_id', $user->id)
            ->where('service_id', $service->id)
            ->latest()
            ->first();

        $this->assertNotNull($request);
        $this->assertEquals('completed', $request->status);
        $this->assertEquals('12345678901', $request->tracking_input);
        $this->assertEquals(500.00, (float) $request->amount_charged);
        $this->assertNotNull($request->result_payload);
        $this->assertTrue($request->result_payload['is_successful']);
        $this->assertNotEmpty($request->result_payload['data']['photo_base64']);
        $this->assertNotEmpty($request->result_payload['data']['full_name']);
        $this->assertStringStartsWith('REF-', $request->reference);
        $this->assertMatchesRegularExpression('/^REF-\d{8}-[A-Z]{4}$/', $request->reference);
    }

    public function test_user_can_perform_nin_verification_by_phone_number_with_ngn_country_code_prefix(): void
    {
        $user = User::where('email', 'user@vsite.ng')->first();
        $service = Service::where('slug', 'nin-verification')->first();
        $wallet = Wallet::where('user_id', $user->id)->first();
        $initialBalance = (float) $wallet->balance;

        $response = $this->actingAs($user)->post('/services/nin-verification', [
            'verification_mode' => 'phone',
            'phone_number' => '+2348012345678',
            'consent' => 1,
        ]);

        $response->assertStatus(302);
        $response->assertSessionHas('api_result');
        $response->assertSessionHas('success');

        $wallet->refresh();
        $this->assertEquals($initialBalance - 500.00, (float) $wallet->balance);

        $request = ServiceRequest::where('user_id', $user->id)
            ->where('service_id', $service->id)
            ->latest()
            ->first();

        $this->assertNotNull($request);
        $this->assertEquals('completed', $request->status);
        $this->assertEquals('08012345678', $request->tracking_input);
        $this->assertEquals('08012345678', $request->result_payload['data']['phone_number']);
        $this->assertNotEmpty($request->result_payload['data']['nin']);
    }

    public function test_user_can_perform_nin_verification_by_demographics(): void
    {
        $user = User::where('email', 'user@vsite.ng')->first();
        $service = Service::where('slug', 'nin-verification')->first();
        $wallet = Wallet::where('user_id', $user->id)->first();
        $initialBalance = (float) $wallet->balance;

        $response = $this->actingAs($user)->post('/services/nin-verification', [
            'verification_mode' => 'demographics',
            'first_name' => 'Amina',
            'middle_name' => 'Chioma',
            'dob' => '1992-08-20',
            'consent' => 1,
        ]);

        $response->assertStatus(302);
        $response->assertSessionHas('api_result');
        $response->assertSessionHas('success');

        $wallet->refresh();
        $this->assertEquals($initialBalance - 500.00, (float) $wallet->balance);

        $request = ServiceRequest::where('user_id', $user->id)
            ->where('service_id', $service->id)
            ->latest()
            ->first();

        $this->assertNotNull($request);
        $this->assertEquals('completed', $request->status);
        $this->assertEquals('Amina Chioma (1992-08-20)', $request->tracking_input);
        $this->assertEquals('Amina', $request->result_payload['data']['first_name']);
        $this->assertEquals('Chioma', $request->result_payload['data']['middle_name']);
        $this->assertEquals('1992-08-20', $request->result_payload['data']['date_of_birth']);
        $this->assertNotEmpty($request->result_payload['data']['nin']);
    }

    public function test_nin_verification_page_renders_method_selector_with_required_options(): void
    {
        $user = User::where('email', 'user@vsite.ng')->first();

        $response = $this->actingAs($user)->get('/services/nin-verification');

        $response->assertStatus(200);
        $response->assertSee('Verify Using');
        $response->assertSee('<option value="nin"', false);
        $response->assertSee('Nin');
        $response->assertSee('<option value="phone"', false);
        $response->assertSee('Phone number');
        $response->assertSee('<option value="demographics"', false);
        $response->assertSee('Demographics');
        $response->assertSee('Lastname');
        $response->assertSee('name="last_name"', false);
        $response->assertSee('Gender');
        $response->assertSee('id="gender"', false);
        $response->assertSee('<option value="Male"', false);
        $response->assertSee('<option value="Female"', false);
    }

    public function test_user_can_perform_nin_verification_by_demographics_with_lastname(): void
    {
        $user = User::where('email', 'user@vsite.ng')->first();
        $service = Service::where('slug', 'nin-verification')->first();

        $response = $this->actingAs($user)->post('/services/nin-verification', [
            'verification_mode' => 'demographics',
            'first_name' => 'Amina',
            'middle_name' => 'Chioma',
            'last_name' => 'Danjuma',
            'dob' => '1992-08-20',
            'consent' => 1,
        ]);

        $response->assertStatus(302);
        $response->assertSessionHas('api_result');
        $response->assertSessionHas('success');

        $request = ServiceRequest::where('user_id', $user->id)
            ->where('service_id', $service->id)
            ->latest()
            ->first();

        $this->assertNotNull($request);
        $this->assertEquals('completed', $request->status);
        $this->assertEquals('Amina Chioma Danjuma (1992-08-20)', $request->tracking_input);
        $this->assertEquals('Amina', $request->result_payload['data']['first_name']);
        $this->assertEquals('Danjuma', $request->result_payload['data']['surname']);
    }

    public function test_user_can_perform_nin_verification_by_demographics_with_gender(): void
    {
        $user = User::where('email', 'user@vsite.ng')->first();
        $service = Service::where('slug', 'nin-verification')->first();

        $response = $this->actingAs($user)->post('/services/nin-verification', [
            'verification_mode' => 'demographics',
            'first_name' => 'Fatima',
            'middle_name' => 'Zainab',
            'last_name' => 'Bello',
            'dob' => '1995-05-15',
            'gender' => 'Female',
            'consent' => 1,
        ]);

        $response->assertStatus(302);
        $response->assertSessionHas('api_result');
        $response->assertSessionHas('success');

        $request = ServiceRequest::where('user_id', $user->id)
            ->where('service_id', $service->id)
            ->latest()
            ->first();

        $this->assertNotNull($request);
        $this->assertEquals('completed', $request->status);
        $this->assertEquals('Female', $request->result_payload['data']['gender']);
    }

    public function test_user_can_perform_bvn_verification_with_wallet_debit(): void
    {
        $user = User::where('email', 'user@vsite.ng')->first();
        $service = Service::where('slug', 'bvn-verification')->first();
        $wallet = Wallet::where('user_id', $user->id)->first();
        $initialBalance = (float) $wallet->balance;

        $response = $this->actingAs($user)->post('/services/bvn-verification', [
            'tracking_input' => '22334455667',
            'consent' => 1,
        ]);

        $response->assertStatus(302);
        $response->assertSessionHas('api_result');

        $wallet->refresh();
        $this->assertEquals($initialBalance - 500.00, (float) $wallet->balance);

        $request = ServiceRequest::where('user_id', $user->id)
            ->where('service_id', $service->id)
            ->latest()
            ->first();

        $this->assertNotNull($request);
        $this->assertEquals('completed', $request->status);
        $this->assertEquals('22334455667', $request->tracking_input);
        $this->assertNotEmpty($request->result_payload['data']['enrollment_bank']);
        $this->assertEquals('ACTIVE & OPERATIONAL', $request->result_payload['data']['bvn_status']);
    }

    public function test_insufficient_wallet_balance_blocks_api_verification_without_debit(): void
    {
        $user = User::where('email', 'user@vsite.ng')->first();
        $wallet = Wallet::where('user_id', $user->id)->first();
        $wallet->update(['balance' => 0.00]);

        $response = $this->actingAs($user)->post('/services/nin-verification', [
            'tracking_input' => '12345678901',
            'consent' => 1,
        ]);

        $response->assertStatus(302);
        $response->assertSessionHas('error');

        $wallet->refresh();
        $this->assertEquals(0.00, (float) $wallet->balance);

        $this->assertDatabaseMissing('service_requests', [
            'user_id' => $user->id,
            'tracking_input' => '12345678901',
        ]);
    }

    public function test_invalid_11_digit_identifier_format_is_rejected(): void
    {
        $user = User::where('email', 'user@vsite.ng')->first();

        // 5 digits
        $res1 = $this->actingAs($user)->post('/services/nin-verification', [
            'tracking_input' => '12345',
            'consent' => 1,
        ]);
        $res1->assertSessionHasErrors('tracking_input');

        // Letters
        $res2 = $this->actingAs($user)->post('/services/bvn-verification', [
            'tracking_input' => 'ABCDEFGHIJK',
            'consent' => 1,
        ]);
        $res2->assertSessionHasErrors('tracking_input');
    }

    public function test_user_can_view_their_own_verification_slip(): void
    {
        $user = User::where('email', 'user@vsite.ng')->first();
        $service = Service::where('slug', 'nin-verification')->first();

        $this->actingAs($user)->post('/services/nin-verification', [
            'tracking_input' => '12345678901',
            'consent' => 1,
        ]);

        $request = ServiceRequest::where('user_id', $user->id)->latest()->first();

        $slipResponse = $this->actingAs($user)->get("/services/slip/{$request->reference}");

        $slipResponse->assertStatus(200);
        $slipResponse->assertSee('National Identification Number Slip (NINS)');
        $slipResponse->assertSee('Federal Republic of Nigeria');
        $slipResponse->assertSee('1234 567 8901');
    }

    public function test_user_cannot_view_another_users_slip_and_receives_403(): void
    {
        $userA = User::where('email', 'user@vsite.ng')->first();
        $userB = User::where('email', 'agent@vsite.ng')->first(); // Agent is another non-admin user

        $this->actingAs($userA)->post('/services/nin-verification', [
            'tracking_input' => '12345678901',
            'consent' => 1,
        ]);

        $request = ServiceRequest::where('user_id', $userA->id)->latest()->first();

        // User B attempts to access User A's slip -> 403 Forbidden
        $response = $this->actingAs($userB)->get("/services/slip/{$request->reference}");
        $response->assertStatus(403);
    }

    public function test_admin_and_staff_can_view_any_customer_slip_for_audit(): void
    {
        $user = User::where('email', 'user@vsite.ng')->first();
        $staff = User::where('email', 'staff@vsite.ng')->first();
        $admin = User::where('email', 'admin@vsite.ng')->first();

        $this->actingAs($user)->post('/services/nin-verification', [
            'tracking_input' => '12345678901',
            'consent' => 1,
        ]);

        $request = ServiceRequest::where('user_id', $user->id)->latest()->first();

        // Staff and Admin can view customer slip
        $this->actingAs($staff)->get("/services/slip/{$request->reference}")->assertStatus(200);
        $this->actingAs($admin)->get("/services/slip/{$request->reference}")->assertStatus(200);
    }

    public function test_nin_verification_fails_validation_when_consent_is_not_checked(): void
    {
        $user = User::where('email', 'user@vsite.ng')->first();
        $wallet = Wallet::where('user_id', $user->id)->first();
        $initialBalance = (float) $wallet->balance;

        $response = $this->actingAs($user)->post('/services/nin-verification', [
            'tracking_input' => '12345678901',
            // No consent provided
        ]);

        $response->assertStatus(302);
        $response->assertSessionHasErrors(['consent']);

        $wallet->refresh();
        $this->assertEquals($initialBalance, (float) $wallet->balance);
    }

    public function test_bvn_verification_fails_validation_when_consent_is_not_checked(): void
    {
        $user = User::where('email', 'user@vsite.ng')->first();
        $wallet = Wallet::where('user_id', $user->id)->first();
        $initialBalance = (float) $wallet->balance;

        $response = $this->actingAs($user)->post('/services/bvn-verification', [
            'tracking_input' => '22334455667',
            // No consent provided
        ]);

        $response->assertStatus(302);
        $response->assertSessionHasErrors(['consent']);

        $wallet->refresh();
        $this->assertEquals($initialBalance, (float) $wallet->balance);
    }

    public function test_check_consent_is_rendered_on_nin_and_bvn_verification_pages(): void
    {
        $user = User::where('email', 'user@vsite.ng')->first();

        // NIN Verification
        $resNin1 = $this->actingAs($user)->get('/services/nin-verification');
        $resNin1->assertStatus(200);
        $resNin1->assertSee('Check Consent');
        $resNin1->assertSee('id="check_consent"', false);
        $resNin1->assertSee('name="consent"', false);
        $resNin1->assertSee('NDPR / NDPA');

        // NIN Verification 2
        $resNin2 = $this->actingAs($user)->get('/services/nin-verification-2');
        $resNin2->assertStatus(200);
        $resNin2->assertSee('Check Consent');

        // NIN Verification 3
        $resNin3 = $this->actingAs($user)->get('/services/nin-verification-3');
        $resNin3->assertStatus(200);
        $resNin3->assertSee('Check Consent');

        // BVN Verification
        $resBvn = $this->actingAs($user)->get('/services/bvn-verification');
        $resBvn->assertStatus(200);
        $resBvn->assertSee('Check Consent');
        $resBvn->assertSee('Bank Verification Number (BVN)');
    }

    public function test_check_consent_is_not_rendered_on_non_identity_services(): void
    {
        $user = User::where('email', 'user@vsite.ng')->first();

        $response = $this->actingAs($user)->get('/services/ipe-clearing');
        $response->assertStatus(200);
        $response->assertDontSee('Check Consent');
        $response->assertDontSee('id="check_consent"', false);
    }

    public function test_provider_manager_resolves_all_drivers(): void
    {
        $manager = app(ProviderManager::class);

        $driverIds = $manager->getAvailableDriverIds();

        $this->assertContains('nin_v1', $driverIds);
        $this->assertContains('nin_v2', $driverIds);
        $this->assertContains('nin_v3', $driverIds);
        $this->assertContains('bvn_v1', $driverIds);

        $this->assertEquals('nin_v1', $manager->driver('nin_v1')->getProviderId());
        $this->assertEquals('nin_v2', $manager->driver('nin_v2')->getProviderId());
        $this->assertEquals('nin_v3', $manager->driver('nin_v3')->getProviderId());
        $this->assertEquals('bvn_v1', $manager->driver('bvn_v1')->getProviderId());
    }
}
