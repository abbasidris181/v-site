<?php

namespace Tests\Feature;

use App\Models\User;
use Database\Seeders\AnnouncementSeeder;
use Database\Seeders\RolePermissionSeeder;
use Database\Seeders\ServiceCatalogSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ThemeDualModeTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        $this->seed(RolePermissionSeeder::class);
        $this->seed(ServiceCatalogSeeder::class);
        $this->seed(AnnouncementSeeder::class);
    }

    public function test_guest_views_contain_theme_toggle_and_zero_flash_script(): void
    {
        $response = $this->get('/login');
        $response->assertStatus(200);
        $response->assertSee('id="theme-toggle"', false);
        $response->assertSee('localStorage.getItem(\'theme\')', false);
        $response->assertSee('toggleTheme()', false);

        $responseRegister = $this->get('/register');
        $responseRegister->assertStatus(200);
        $responseRegister->assertSee('id="theme-toggle"', false);
        $responseRegister->assertSee('toggleTheme()', false);
    }

    public function test_user_portal_views_contain_theme_toggle_and_dual_theme_classes(): void
    {
        $user = User::where('email', 'user@vsite.ng')->first();

        // 1. Dashboard
        $responseDashboard = $this->actingAs($user)->get('/dashboard');
        $responseDashboard->assertStatus(200);
        $responseDashboard->assertSee('id="theme-toggle"', false);
        $responseDashboard->assertSee('dark:bg-slate-950', false);
        $responseDashboard->assertSee('dark:text-white', false);

        // 2. Service Show View
        $responseService = $this->actingAs($user)->get('/services/nin-verification');
        $responseService->assertStatus(200);
        $responseService->assertSee('id="theme-toggle"', false);
        $responseService->assertSee('dark:border-slate-800', false);

        // 3. Fund Wallet View
        $responseWallet = $this->actingAs($user)->get('/wallet');
        $responseWallet->assertStatus(200);
        $responseWallet->assertSee('id="theme-toggle"', false);
        $responseWallet->assertSee('Fund Your Wallet');
        $responseWallet->assertSee('dark:bg-slate-900', false);

        // 4. Send Fund View
        $responseSendFund = $this->actingAs($user)->get('/wallet/send-fund');
        $responseSendFund->assertStatus(200);
        $responseSendFund->assertSee('id="theme-toggle"', false);
        $responseSendFund->assertSee('Send Fund by Email or Phone');
        $responseSendFund->assertSee('dark:bg-slate-900', false);
    }

    public function test_admin_backend_contains_theme_toggle_and_dual_theme_classes(): void
    {
        $admin = User::where('email', 'admin@vsite.ng')->first();

        $responseAdmin = $this->actingAs($admin)->get('/admin');
        $responseAdmin->assertStatus(200);
        $responseAdmin->assertSee('id="theme-toggle"', false);
        $responseAdmin->assertSee('ADMIN BACKEND');
        $responseAdmin->assertSee('toggleTheme()', false);
        $responseAdmin->assertSee('dark:bg-slate-950', false);
    }

    public function test_theme_toggle_supports_light_dark_and_system_modes(): void
    {
        $user = User::where('email', 'user@vsite.ng')->first();

        $response = $this->actingAs($user)->get('/dashboard');
        $response->assertStatus(200);

        // Assert presence of all three options: Light, Dark, and System
        $response->assertSee('id="theme-opt-light"', false);
        $response->assertSee('id="theme-opt-dark"', false);
        $response->assertSee('id="theme-opt-system"', false);

        $response->assertSee('Light');
        $response->assertSee('Dark');
        $response->assertSee('System');

        // Assert all three mode icons are present
        $response->assertSee('id="theme-icon-light"', false);
        $response->assertSee('id="theme-icon-dark"', false);
        $response->assertSee('id="theme-icon-system"', false);

        // Assert system mode handling in JS
        $response->assertSee('setTheme(\'system\')', false);
        $response->assertSee('prefers-color-scheme', false);
    }
}

