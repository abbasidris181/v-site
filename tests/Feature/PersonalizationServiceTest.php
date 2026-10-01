<?php

namespace Tests\Feature;

use App\Models\Service;
use App\Models\ServiceRequest;
use App\Models\User;
use App\Services\WalletService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class PersonalizationServiceTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        $this->seed();
    }

    public function test_personalization_service_is_visible_on_user_dashboard(): void
    {
        $user = User::where('email', 'user@vsite.ng')->first();

        $response = $this->actingAs($user)->get('/dashboard');

        $response->assertStatus(200);
        $response->assertSee('Personalization');
        $response->assertSee(route('services.show', 'personalization'));
        $response->assertSee('images/services/personalization.png');
    }

    public function test_personalization_link_is_present_in_sidebar_navigation(): void
    {
        $user = User::where('email', 'user@vsite.ng')->first();

        $response = $this->actingAs($user)->get('/dashboard');

        $response->assertStatus(200);
        $response->assertSee(route('services.show', 'personalization'));
    }

    public function test_user_can_view_personalization_service_page(): void
    {
        $user = User::where('email', 'user@vsite.ng')->first();

        $response = $this->actingAs($user)->get('/services/personalization');

        $response->assertStatus(200);
        $response->assertSee('Personalization');
        $response->assertSee('Personalization Number (15 Characters)');
        $response->assertSee('Enter 15-character alphanumeric personalization number');
        $response->assertSee('Submit Request');
        $response->assertSee('₦500.00');
        $response->assertSee('images/services/personalization.png');
    }

    public function test_submitting_invalid_length_or_format_fails_validation(): void
    {
        $user = User::where('email', 'user@vsite.ng')->first();
        $walletService = app(WalletService::class);
        $initialBalance = $walletService->getOrCreateWallet($user)->balance;

        // 1. Too short (10 chars)
        $response = $this->actingAs($user)
            ->from('/services/personalization')
            ->post('/services/personalization', [
                'tracking_input' => 'ABC1234567',
            ]);

        $response->assertRedirect('/services/personalization');
        $response->assertSessionHasErrors('tracking_input');

        // Verify balance was not touched
        $this->assertEquals($initialBalance, $walletService->getOrCreateWallet($user)->balance);
        $this->assertEquals(0, ServiceRequest::where('user_id', $user->id)->whereHas('service', fn ($q) => $q->where('slug', 'personalization'))->count());

        // 2. Contains special characters (@#$%)
        $responseSpecial = $this->actingAs($user)
            ->from('/services/personalization')
            ->post('/services/personalization', [
                'tracking_input' => 'ABC123456789!@#',
            ]);

        $responseSpecial->assertRedirect('/services/personalization');
        $responseSpecial->assertSessionHasErrors('tracking_input');
    }

    public function test_submitting_valid_15_char_alphanumeric_creates_pending_request_and_deducts_wallet(): void
    {
        $user = User::where('email', 'user@vsite.ng')->first();
        $walletService = app(WalletService::class);
        $wallet = $walletService->getOrCreateWallet($user);
        $initialBalance = (float) $wallet->balance;

        $trackingId = 'ABC123456789XYZ';

        $response = $this->actingAs($user)
            ->from('/services/personalization')
            ->post('/services/personalization', [
                'tracking_input' => $trackingId,
            ]);

        $response->assertRedirect('/services/personalization');
        $response->assertSessionHas('success');

        // Check wallet deduction
        $service = Service::where('slug', 'personalization')->first();
        $newBalance = (float) $walletService->getOrCreateWallet($user)->balance;
        $this->assertEquals($initialBalance - (float) $service->price, $newBalance);

        // Check ServiceRequest creation
        $serviceRequest = ServiceRequest::where('user_id', $user->id)
            ->where('service_id', $service->id)
            ->latest()
            ->first();

        $this->assertNotNull($serviceRequest);
        $this->assertEquals('pending', $serviceRequest->status);
        $this->assertStringStartsWith('REF-', $serviceRequest->reference);
        $this->assertMatchesRegularExpression('/^REF-\d{8}-[A-Z]{4}$/', $serviceRequest->reference);
    }

    public function test_lowercase_15_char_alphanumeric_is_normalized_to_uppercase(): void
    {
        $user = User::where('email', 'user@vsite.ng')->first();

        $response = $this->actingAs($user)
            ->from('/services/personalization')
            ->post('/services/personalization', [
                'tracking_input' => 'abc123456789xyz',
            ]);

        $response->assertRedirect('/services/personalization');
        $response->assertSessionHas('success');

        $service = Service::where('slug', 'personalization')->first();
        $serviceRequest = ServiceRequest::where('user_id', $user->id)
            ->where('service_id', $service->id)
            ->latest()
            ->first();

        $this->assertNotNull($serviceRequest);
        $this->assertEquals('ABC123456789XYZ', $serviceRequest->tracking_input);
    }

    public function test_admin_sidebar_and_queue_displays_personalization_service(): void
    {
        $admin = User::where('email', 'admin@vsite.ng')->first();

        $response = $this->actingAs($admin)->get('/admin/queue');

        $response->assertStatus(200);
        $response->assertSee('Personalization');
        $response->assertSee(route('admin.queue.index', ['service_slug' => 'personalization']));
    }
}
