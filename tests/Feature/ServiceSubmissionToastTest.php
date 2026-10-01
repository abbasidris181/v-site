<?php

namespace Tests\Feature;

use App\Models\Service;
use App\Models\User;
use Database\Seeders\AnnouncementSeeder;
use Database\Seeders\RolePermissionSeeder;
use Database\Seeders\ServiceCatalogSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ServiceSubmissionToastTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        $this->seed(RolePermissionSeeder::class);
        $this->seed(ServiceCatalogSeeder::class);
        $this->seed(AnnouncementSeeder::class);
    }

    public function test_toast_component_is_rendered_in_user_portal(): void
    {
        $user = User::where('email', 'user@vsite.ng')->first();
        $service = Service::where('slug', 'nin-verification')->first();

        $response = $this->actingAs($user)->get(route('services.show', $service->slug));

        $response->assertStatus(200);
        $response->assertSee('id="toast-container"', false);
        $response->assertSee('id="toast-template"', false);
        $response->assertSee('toast-badge', false);
        $response->assertSee('toast-progress-bar', false);
    }

    public function test_toast_triggers_successful_on_service_submission_success(): void
    {
        $user = User::where('email', 'user@vsite.ng')->first();
        $service = Service::where('slug', 'nin-verification')->first();

        $response = $this->actingAs($user)
            ->withSession(['success' => 'Verification Successful! Identity record retrieved.'])
            ->get(route('services.show', $service->slug));

        $response->assertStatus(200);
        $response->assertSee('window.showToast', false);
        $response->assertSee('type: \'success\'', false);
        $response->assertSee('title: \'Successful\'', false);
        $response->assertSee('Verification Successful! Identity record retrieved.');
    }

    public function test_toast_triggers_failed_on_service_submission_error(): void
    {
        $user = User::where('email', 'user@vsite.ng')->first();
        $service = Service::where('slug', 'nin-verification')->first();

        $response = $this->actingAs($user)
            ->withSession(['error' => 'Insufficient wallet balance to perform this verification.'])
            ->get(route('services.show', $service->slug));

        $response->assertStatus(200);
        $response->assertSee('window.showToast', false);
        $response->assertSee('type: \'failed\'', false);
        $response->assertSee('title: \'Failed\'', false);
        $response->assertSee('Insufficient wallet balance to perform this verification.');
    }

    public function test_toast_triggers_failed_when_form_validation_fails(): void
    {
        $user = User::where('email', 'user@vsite.ng')->first();
        $service = Service::where('slug', 'nin-verification')->first();

        // Simulate a redirected page load with validation errors in session
        $response = $this->actingAs($user)
            ->withSession(['errors' => (new \Illuminate\Support\ViewErrorBag)->put('default', new \Illuminate\Support\MessageBag(['nin' => ['Please provide a valid 11-digit NIN.']]))])
            ->get(route('services.show', $service->slug));

        $response->assertStatus(200);
        $response->assertSee('window.showToast', false);
        $response->assertSee('type: \'failed\'', false);
        $response->assertSee('title: \'Failed\'', false);
        $response->assertSee('Please provide a valid 11-digit NIN.');
    }
}
