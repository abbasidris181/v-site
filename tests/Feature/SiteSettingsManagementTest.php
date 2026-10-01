<?php

namespace Tests\Feature;

use App\Models\Permission;
use App\Models\Role;
use App\Models\Service;
use App\Models\Setting;
use App\Models\User;
use Database\Seeders\RolePermissionSeeder;
use Database\Seeders\ServiceCatalogSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;

class SiteSettingsManagementTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        $this->seed(RolePermissionSeeder::class);
        $this->seed(ServiceCatalogSeeder::class);
    }

    public function test_super_admin_and_admin_can_access_site_settings(): void
    {
        $superAdmin = User::where('email', 'superadmin@vsite.ng')->first();
        $admin = User::where('email', 'admin@vsite.ng')->first();

        $this->actingAs($superAdmin)->get('/admin/settings')
            ->assertStatus(200)
            ->assertSee('Site Settings')
            ->assertSee('Manage Service Charges')
            ->assertDontSee('Real-time Service Tariff Management');

        $this->actingAs($admin)->get('/admin/settings')
            ->assertStatus(200)
            ->assertSee('Site Settings');
    }

    public function test_staff_without_permission_receives_403(): void
    {
        $staff = User::where('email', 'staff@vsite.ng')->first();

        $this->actingAs($staff)->get('/admin/settings')
            ->assertStatus(403);
    }

    public function test_all_roles_with_permission_can_access_site_settings(): void
    {
        $staff = User::where('email', 'staff@vsite.ng')->first();
        $permission = Permission::where('slug', 'settings.manage')->first();
        $staffRole = Role::where('slug', 'staff')->first();

        $staffRole->permissions()->attach($permission->id);

        $this->actingAs($staff)->get('/admin/settings')
            ->assertStatus(200)
            ->assertSee('Site Settings');
    }

    public function test_agent_and_regular_user_cannot_access_site_settings(): void
    {
        $agent = User::where('email', 'agent@vsite.ng')->first();
        $endUser = User::where('email', 'user@vsite.ng')->first();

        $this->actingAs($agent)->get('/admin/settings')->assertStatus(403);
        $this->actingAs($endUser)->get('/admin/settings')->assertStatus(403);
    }

    public function test_admin_can_update_service_pricing_and_parameters(): void
    {
        $admin = User::where('email', 'admin@vsite.ng')->first();
        $service = Service::where('slug', 'nin-verification')->first();

        $response = $this->actingAs($admin)->put('/admin/settings/pricing', [
            'services' => [
                $service->id => [
                    'price' => '350.00',
                    'provider_driver' => 'nin_v2',
                    'is_active' => '1',
                ],
            ],
        ]);

        $response->assertRedirect('/admin/settings?tab=pricing');
        $response->assertSessionHas('status', 'Service pricing and catalog parameters updated successfully.');

        $service->refresh();
        $this->assertEquals('350.00', $service->price);
        $this->assertEquals('nin_v2', $service->provider_driver);
        $this->assertTrue($service->is_active);
    }

    public function test_admin_can_deactivate_service(): void
    {
        $admin = User::where('email', 'admin@vsite.ng')->first();
        $service = Service::where('slug', 'nin-verification')->first();

        // Omit is_active to simulate unchecked toggle
        $response = $this->actingAs($admin)->put('/admin/settings/pricing', [
            'services' => [
                $service->id => [
                    'price' => '250.00',
                    'provider_driver' => 'nin_v1',
                ],
            ],
        ]);

        $response->assertRedirect('/admin/settings?tab=pricing');

        $service->refresh();
        $this->assertFalse($service->is_active);
    }

    public function test_service_pricing_validation(): void
    {
        $admin = User::where('email', 'admin@vsite.ng')->first();
        $service = Service::where('slug', 'nin-verification')->first();

        $response = $this->actingAs($admin)->put('/admin/settings/pricing', [
            'services' => [
                $service->id => [
                    'price' => '-50.00', // Negative price
                ],
            ],
        ]);

        $response->assertSessionHasErrors(['services.' . $service->id . '.price']);
    }

    public function test_admin_can_update_site_configurations(): void
    {
        $admin = User::where('email', 'admin@vsite.ng')->first();

        $payload = [
            'site_name' => 'V-Site Enterprise',
            'site_tagline' => 'Premier Identity Validation Services',
            'contact_email' => 'ops@vsite.ng',
            'contact_phone' => '+234 811 222 3333',
            'contact_whatsapp' => '+234 811 444 5555',
            'min_wallet_deposit' => '1000.00',
            'maintenance_mode' => '1',
            'font_family' => 'Arial, sans-serif',
        ];

        $response = $this->actingAs($admin)->put('/admin/settings/configurations', $payload);

        $response->assertRedirect('/admin/settings?tab=configurations');
        $response->assertSessionHas('status', 'Site configurations saved successfully.');

        $this->assertEquals('V-Site Enterprise', Setting::get('site_name'));
        $this->assertEquals('Premier Identity Validation Services', Setting::get('site_tagline'));
        $this->assertEquals('ops@vsite.ng', Setting::get('contact_email'));
        $this->assertEquals('+234 811 222 3333', Setting::get('contact_phone'));
        $this->assertEquals('+234 811 444 5555', Setting::get('contact_whatsapp'));
        $this->assertEquals('1000.00', Setting::get('min_wallet_deposit'));
        $this->assertEquals('1', Setting::get('maintenance_mode'));
        $this->assertEquals('Arial, sans-serif', Setting::get('font_family'));
    }

    public function test_site_configurations_validation(): void
    {
        $admin = User::where('email', 'admin@vsite.ng')->first();

        $response = $this->actingAs($admin)->put('/admin/settings/configurations', [
            'site_name' => '', // required
            'contact_email' => 'invalid-email', // invalid email
            'contact_phone' => '', // required
            'min_wallet_deposit' => '20', // below min:50
        ]);

        $response->assertSessionHasErrors([
            'site_name',
            'contact_email',
            'contact_phone',
            'min_wallet_deposit',
        ]);
    }

    public function test_dynamic_font_configuration_applies_across_pages(): void
    {
        $admin = User::where('email', 'admin@vsite.ng')->first();

        $this->actingAs($admin)->put('/admin/settings/configurations', [
            'site_name' => 'V-Site Enterprise',
            'contact_email' => 'ops@vsite.ng',
            'contact_phone' => '+234 811 222 3333',
            'min_wallet_deposit' => '500.00',
            'font_family' => "'Inter', Arial, sans-serif",
        ]);

        $this->actingAs($admin)->get('/admin/settings')
            ->assertStatus(200)
            ->assertSee("'Inter', Arial, sans-serif", false);

        $this->actingAs($admin)->get('/dashboard')
            ->assertStatus(200)
            ->assertSee("'Inter', Arial, sans-serif", false);

        auth()->logout();

        $this->get('/login')
            ->assertStatus(200)
            ->assertSee("'Inter', Arial, sans-serif", false);
    }

    protected function createFakePng(string $name = 'logo.png'): UploadedFile
    {
        $pngContent = base64_decode('iVBORw0KGgoAAAANSUhEUgAAAAEAAAABCAYAAAAfFcSJAAAADUlEQVR42mNk+M9QDwADhgGAWjR9awAAAABJRU5ErkJggg==');
        return UploadedFile::fake()->createWithContent($name, $pngContent);
    }

    public function test_admin_can_upload_custom_logo(): void
    {
        Storage::fake('public');

        $admin = User::where('email', 'admin@vsite.ng')->first();
        $logo = $this->createFakePng('custom-brand-logo.png');

        $response = $this->actingAs($admin)->put('/admin/settings/configurations', [
            'site_name' => 'V-Site Enterprise',
            'contact_email' => 'ops@vsite.ng',
            'contact_phone' => '+234 811 222 3333',
            'min_wallet_deposit' => '500.00',
            'site_logo' => $logo,
        ]);

        $response->assertRedirect('/admin/settings?tab=configurations');
        $response->assertSessionHas('status', 'Site configurations saved successfully.');

        $storedLogoPath = Setting::get('site_logo');
        $this->assertNotEmpty($storedLogoPath);
        Storage::disk('public')->assertExists($storedLogoPath);

        // Verify custom logo appears across Admin backend, User portal, and Guest Login page
        $this->actingAs($admin)->get('/admin/settings')
            ->assertStatus(200)
            ->assertSee($storedLogoPath, false);

        $this->actingAs($admin)->get('/dashboard')
            ->assertStatus(200)
            ->assertSee($storedLogoPath, false);

        auth()->logout();

        $this->get('/login')
            ->assertStatus(200)
            ->assertSee($storedLogoPath, false);
    }

    public function test_admin_can_remove_custom_logo(): void
    {
        Storage::fake('public');

        $admin = User::where('email', 'admin@vsite.ng')->first();
        $logo = $this->createFakePng('brand.png');

        // Upload first
        $this->actingAs($admin)->put('/admin/settings/configurations', [
            'site_name' => 'V-Site Enterprise',
            'contact_email' => 'ops@vsite.ng',
            'contact_phone' => '+234 811 222 3333',
            'min_wallet_deposit' => '500.00',
            'site_logo' => $logo,
        ]);

        $storedLogoPath = Setting::get('site_logo');
        Storage::disk('public')->assertExists($storedLogoPath);

        // Now remove it
        $response = $this->actingAs($admin)->put('/admin/settings/configurations', [
            'site_name' => 'V-Site Enterprise',
            'contact_email' => 'ops@vsite.ng',
            'contact_phone' => '+234 811 222 3333',
            'min_wallet_deposit' => '500.00',
            'remove_logo' => '1',
        ]);

        $response->assertRedirect('/admin/settings?tab=configurations');
        $this->assertEquals('', Setting::get('site_logo'));
        Storage::disk('public')->assertMissing($storedLogoPath);
    }

    public function test_invalid_logo_file_is_rejected(): void
    {
        Storage::fake('public');

        $admin = User::where('email', 'admin@vsite.ng')->first();
        $file = UploadedFile::fake()->create('document.pdf', 500, 'application/pdf');

        $response = $this->actingAs($admin)->put('/admin/settings/configurations', [
            'site_name' => 'V-Site Enterprise',
            'contact_email' => 'ops@vsite.ng',
            'contact_phone' => '+234 811 222 3333',
            'min_wallet_deposit' => '500.00',
            'site_logo' => $file,
        ]);

        $response->assertSessionHasErrors(['site_logo']);
    }

    public function test_support_page_reflects_updated_support_email_and_whatsapp(): void
    {
        $admin = User::where('email', 'admin@vsite.ng')->first();
        $user = User::where('email', 'user@vsite.ng')->first();

        // 1. Initially verify default support contacts
        $initialResponse = $this->actingAs($user)->get('/support');
        $initialResponse->assertStatus(200);
        $initialResponse->assertSee('support@vsite.ng');
        $initialResponse->assertSee('Chat on WhatsApp');

        // 2. Admin updates the support email and WhatsApp line in Admin Dashboard
        $updateResponse = $this->actingAs($admin)->put('/admin/settings/configurations', [
            'site_name' => 'V-SITE',
            'contact_email' => 'custom-desk@vsite.ng',
            'contact_phone' => '+234 801 111 2222',
            'contact_whatsapp' => '+234 809 999 8888',
            'min_wallet_deposit' => '500.00',
        ]);
        $updateResponse->assertRedirect('/admin/settings?tab=configurations');

        // 3. User views /support page - updated email, WhatsApp URL, and phone must be present
        $updatedUserResponse = $this->actingAs($user)->get('/support');
        $updatedUserResponse->assertStatus(200);

        // Support Email updated
        $updatedUserResponse->assertSee('custom-desk@vsite.ng');
        $updatedUserResponse->assertSee('mailto:custom-desk@vsite.ng');
        $updatedUserResponse->assertDontSee('mailto:support@vsite.ng');

        // WhatsApp number & wa.me URL updated
        $updatedUserResponse->assertSee('https://wa.me/2348099998888');
        $updatedUserResponse->assertSee('+234 809 999 8888');
        $updatedUserResponse->assertDontSee('https://wa.me/2348000000001');

        // Support Phone updated
        $updatedUserResponse->assertSee('+234 801 111 2222');
    }
}
