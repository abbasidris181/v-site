<?php

namespace Tests\Feature;

use App\Models\Permission;
use App\Models\Role;
use App\Models\Service;
use App\Models\User;
use Database\Seeders\RolePermissionSeeder;
use Database\Seeders\ServiceCatalogSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ChargesManagementTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        $this->seed(RolePermissionSeeder::class);
        $this->seed(ServiceCatalogSeeder::class);
    }

    public function test_super_admin_and_admin_can_access_charges_management(): void
    {
        $superAdmin = User::where('email', 'superadmin@vsite.ng')->first();
        $admin = User::where('email', 'admin@vsite.ng')->first();

        $response = $this->actingAs($superAdmin)->get('/admin/charges');
        $response->assertStatus(200)
            ->assertSee('Pricing &amp; Tariff Management', false)
            ->assertDontSee('Tariff Governance')
            ->assertDontSee('Pricing Architecture')
            ->assertDontSee('Avg Tariff')
            ->assertSee('SN')
            ->assertSee('NAME')
            ->assertSee('CATEGORY')
            ->assertSee('AMOUNT')
            ->assertSee('ACTION')
            ->assertSee('STATUS')
            ->assertSee('<span>Save</span>', false)
            ->assertDontSee('Identifier & Details')
            ->assertDontSee('Provider Driver');

        $this->actingAs($admin)->get('/admin/charges')
            ->assertStatus(200)
            ->assertSee('Pricing');
    }

    public function test_admin_can_update_service_charges_without_providing_driver(): void
    {
        $admin = User::where('email', 'admin@vsite.ng')->first();
        $service = Service::where('slug', 'nin-verification')->first();
        $originalDriver = $service->provider_driver;

        $response = $this->actingAs($admin)->put('/admin/charges', [
            'services' => [
                $service->id => [
                    'price' => '850.00',
                    'action' => 'enable',
                ],
            ],
        ]);

        $response->assertRedirect('/admin/charges');
        $response->assertSessionHas('status');

        $service->refresh();
        $this->assertEquals('850.00', $service->price);
        $this->assertEquals($originalDriver, $service->provider_driver);
        $this->assertTrue($service->is_active);
    }

    public function test_admin_can_update_service_charges_and_status_via_action_selector(): void
    {
        $admin = User::where('email', 'admin@vsite.ng')->first();
        $service = Service::where('slug', 'nin-verification')->first();

        // 1. Test disabling service via action selector
        $response = $this->actingAs($admin)->put('/admin/charges', [
            'services' => [
                $service->id => [
                    'price' => '750.00',
                    'action' => 'disable',
                ],
            ],
        ]);

        $response->assertRedirect('/admin/charges');
        $service->refresh();
        $this->assertEquals('750.00', $service->price);
        $this->assertFalse($service->is_active);

        // 2. Test enabling service via action selector
        $response = $this->actingAs($admin)->put('/admin/charges', [
            'services' => [
                $service->id => [
                    'price' => '800.00',
                    'action' => 'enable',
                ],
            ],
        ]);

        $response->assertRedirect('/admin/charges');
        $service->refresh();
        $this->assertEquals('800.00', $service->price);
        $this->assertTrue($service->is_active);
    }

    public function test_staff_without_permission_cannot_access_charges(): void
    {
        $staff = User::where('email', 'staff@vsite.ng')->first();

        $this->actingAs($staff)->get('/admin/charges')
            ->assertStatus(403);
    }

    public function test_admin_sidebar_renders_charges_dropdown_with_pricing_option(): void
    {
        $admin = User::where('email', 'admin@vsite.ng')->first();

        $response = $this->actingAs($admin)->get('/admin');

        $response->assertStatus(200);
        $response->assertSee('Charges');
        $response->assertSee('id="charges-dropdown-btn"', false);
        $response->assertSee('toggleChargesDropdown()', false);
        $response->assertSee('Pricing');
    }

    public function test_admin_can_update_service_charges_via_charges_endpoint(): void
    {
        $admin = User::where('email', 'admin@vsite.ng')->first();
        $service = Service::where('slug', 'nin-verification')->first();

        $response = $this->actingAs($admin)->put('/admin/charges', [
            'services' => [
                $service->id => [
                    'price' => '650.00',
                    'provider_driver' => 'nin_v2',
                    'action' => 'enable',
                ],
            ],
        ]);

        $response->assertRedirect('/admin/charges');
        $response->assertSessionHas('status');

        $service->refresh();
        $this->assertEquals('650.00', $service->price);
        $this->assertEquals('nin_v2', $service->provider_driver);
        $this->assertTrue($service->is_active);
    }

    public function test_pricing_validation_rejects_negative_charges(): void
    {
        $admin = User::where('email', 'admin@vsite.ng')->first();
        $service = Service::where('slug', 'bvn-verification')->first();

        $response = $this->actingAs($admin)->put('/admin/charges', [
            'services' => [
                $service->id => [
                    'price' => '-100.00',
                ],
            ],
        ]);

        $response->assertSessionHasErrors(['services.' . $service->id . '.price']);
    }
}
