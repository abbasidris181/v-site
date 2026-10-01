<?php

namespace Tests\Feature;

use App\Models\User;
use Database\Seeders\AnnouncementSeeder;
use Database\Seeders\RolePermissionSeeder;
use Database\Seeders\ServiceCatalogSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class SidebarNavigationTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        $this->seed(RolePermissionSeeder::class);
        $this->seed(ServiceCatalogSeeder::class);
        $this->seed(AnnouncementSeeder::class);
    }

    public function test_user_portal_renders_sidebar_with_all_navigation_elements(): void
    {
        $user = User::where('email', 'user@vsite.ng')->first();

        $response = $this->actingAs($user)->get('/dashboard');

        $response->assertStatus(200);
        $response->assertSee('V-SITE');
        $response->assertSee($user->full_name);
        $response->assertSee('Dashboard');
        $response->assertSee('Services');
        $response->assertSee('NIN Verification');
        $response->assertSee('NIN Verification 2');
        $response->assertSee('NIN Verification 3');
        $response->assertSee('BVN Verification');
        $response->assertSee('IPE Clearing');
        $response->assertSee('NIN Validation');
        $response->assertSee('Transactions');
        $response->assertSee('Send Fund by Email or Phone');
        
        // Assert Fund Wallet link is not present in sidebar navigation
        $sidebarContent = substr($response->getContent(), 0, strpos($response->getContent(), '</aside>') ?: strlen($response->getContent()));
        $this->assertStringNotContainsString('Fund Wallet', $sidebarContent);
        
        $response->assertDontSee('#fund');
        $response->assertSee('Profile');
        $response->assertSee('Support');
        $response->assertSee('Logout');
    }

    public function test_user_can_access_send_fund_page(): void
    {
        $user = User::where('email', 'user@vsite.ng')->first();

        $response = $this->actingAs($user)->get('/wallet/send-fund');

        $response->assertStatus(200);
        $response->assertSee('Send Fund by Email or Phone');
        $response->assertSee('Recipient Email or Phone Number');
        $response->assertSee('Amount to Send');
        $response->assertSee('Send Fund Now');
    }

    public function test_user_can_access_transactions_history_page(): void
    {
        $user = User::where('email', 'user@vsite.ng')->first();

        $response = $this->actingAs($user)->get('/wallet/transactions');

        $response->assertStatus(200);
        $response->assertSee('Transactions');
        $response->assertSee('transactions history');
        $response->assertSee('Reference');
        $response->assertSee('Category');
        $response->assertSee('Amount');
    }

    public function test_admin_link_in_sidebar_is_gated_by_role(): void
    {
        $admin = User::where('email', 'admin@vsite.ng')->first();
        $user = User::where('email', 'user@vsite.ng')->first();

        // Admin sees the admin button in sidebar
        $responseAdmin = $this->actingAs($admin)->get('/dashboard');
        $responseAdmin->assertSee('Admin Dashboard');

        // Normal user does not see it
        $responseUser = $this->actingAs($user)->get('/dashboard');
        $responseUser->assertDontSee('Admin Dashboard');
    }

    public function test_user_can_access_profile_page(): void
    {
        $user = User::where('email', 'user@vsite.ng')->first();

        $response = $this->actingAs($user)->get('/profile');

        $response->assertStatus(200);
        $response->assertSee('Personal & Business Information', false);
        $response->assertSee($user->email);
        $response->assertSee('Verification Status');
        $response->assertSee('Change Password');
    }

    public function test_user_can_access_support_page(): void
    {
        $user = User::where('email', 'user@vsite.ng')->first();

        $response = $this->actingAs($user)->get('/support');

        $response->assertStatus(200);
        $response->assertSee('Help & Support Desk', false);
        $response->assertSee('WhatsApp Live Chat');
        $response->assertSee('Email Helpdesk');
        $response->assertSee('Frequently Asked Questions');
    }

    public function test_admin_backend_renders_admin_sidebar_navigation(): void
    {
        $admin = User::where('email', 'admin@vsite.ng')->first();

        $response = $this->actingAs($admin)->get('/admin');

        $response->assertStatus(200);
        $response->assertSee('ADMIN PANEL');
        $response->assertSee('Dashboard');
        $response->assertSee('Service Requests');
        $response->assertSee('id="service-requests-dropdown-btn"', false);
        $response->assertSee('toggleServiceRequestsDropdown()', false);
        $response->assertSee('IPE Clearing');
        $response->assertSee('NIN Validation');
        $response->assertSee('Modification IPE');
        $response->assertSee('BVN Retrieval by Phone Number');
        $response->assertSee('Self-Service Delinking');
        $response->assertSee('Users');
        $response->assertSee('id="users-dropdown-btn"', false);
        $response->assertSee('toggleUsersDropdown()', false);
        $response->assertSee('All Users');
        $response->assertSee('Agents');
        $response->assertSee('Staff');
        $response->assertSee('Administrators');
        $response->assertSee('Wallet & Finance');
        $response->assertSee('id="finance-dropdown-btn"', false);
        $response->assertSee('toggleFinanceDropdown()', false);
        $response->assertSee('Fund User');
        $response->assertSee('Wallet Transactions');
        $response->assertSee('Funding History');
        $response->assertSee('Transactions');
        $response->assertSee('id="transactions-dropdown-btn"', false);
        $response->assertSee('toggleTransactionsDropdown()', false);
        $response->assertSee('NIN Verifications');
        $response->assertSee('BVN Verifications');
        $response->assertSee('Deposit History');
        $response->assertSee('Wallet-to-Wallet Transactions');
        $response->assertSee('Charges');
        $response->assertSee('id="charges-dropdown-btn"', false);
        $response->assertSee('toggleChargesDropdown()', false);
        $response->assertSee('System Settings');
        $response->assertSee('Support FAQs');
        $response->assertSee('Internal Comms');
        $response->assertSee('Back to User Dashboard');
        $response->assertSee('Logout');
    }

    public function test_admin_can_access_users_directory_and_inspect_user(): void
    {
        $admin = User::where('email', 'admin@vsite.ng')->first();
        $targetUser = User::where('email', 'user@vsite.ng')->first();

        // 1. Directory access
        $responseDirectory = $this->actingAs($admin)->get('/admin/users');
        $responseDirectory->assertStatus(200);
        $responseDirectory->assertSee('User Management');
        $responseDirectory->assertSee($targetUser->email);

        // 2. User profile inspection
        $responseShow = $this->actingAs($admin)->get("/admin/users/{$targetUser->id}");
        $responseShow->assertStatus(200);
        $responseShow->assertSee($targetUser->full_name);
        $responseShow->assertSee('Reset User Password');
        $responseShow->assertSee('Access Status');
    }

    public function test_admin_can_toggle_user_status_and_reset_password(): void
    {
        $admin = User::where('email', 'admin@vsite.ng')->first();
        $targetUser = User::where('email', 'user@vsite.ng')->first();

        // 1. Suspend account
        $responseSuspend = $this->actingAs($admin)->post("/admin/users/{$targetUser->id}/status");
        $responseSuspend->assertRedirect();
        $this->assertFalse($targetUser->fresh()->is_active);

        // 2. Activate account
        $responseActivate = $this->actingAs($admin)->post("/admin/users/{$targetUser->id}/status");
        $responseActivate->assertRedirect();
        $this->assertTrue($targetUser->fresh()->is_active);

        // 3. Reset password
        $responseReset = $this->actingAs($admin)->post("/admin/users/{$targetUser->id}/password", [
            'password' => 'NewSecurePassword123!',
            'password_confirmation' => 'NewSecurePassword123!',
        ]);
        $responseReset->assertRedirect();
        $this->assertTrue(\Illuminate\Support\Facades\Hash::check('NewSecurePassword123!', $targetUser->fresh()->password));
    }

    public function test_staff_without_permission_cannot_access_users_directory(): void
    {
        $staff = User::where('email', 'staff@vsite.ng')->first();

        $response = $this->actingAs($staff)->get('/admin/users');
        $response->assertStatus(403);
    }

    public function test_disabled_service_is_removed_from_sidebar_dropdown(): void
    {
        $user = User::where('email', 'user@vsite.ng')->first();
        $admin = User::where('email', 'admin@vsite.ng')->first();

        $serviceToDisable = \App\Models\Service::where('slug', 'nin-verification-2')->firstOrFail();

        // Initially active - should be visible in sidebar
        $response = $this->actingAs($user)->get('/dashboard');
        $response->assertStatus(200);
        $response->assertSee(route('services.show', 'nin-verification-2'));
        $response->assertSee('NIN Verification 2');

        // Admin disables the service via charges endpoint
        $putResponse = $this->actingAs($admin)->put('/admin/charges', [
            'services' => [
                $serviceToDisable->id => [
                    'price' => $serviceToDisable->price,
                    'is_active' => '0',
                ],
            ],
        ]);
        $putResponse->assertSessionHasNoErrors();
        $putResponse->assertRedirect();

        $this->assertFalse($serviceToDisable->fresh()->is_active);

        // User views dashboard - disabled service must NOT appear in sidebar dropdown
        $responseAfterDisable = $this->actingAs($user)->get('/dashboard');
        $responseAfterDisable->assertStatus(200);
        $responseAfterDisable->assertDontSee(route('services.show', 'nin-verification-2'));
        $responseAfterDisable->assertDontSee('NIN Verification 2');
        // Other active services must still appear
        $responseAfterDisable->assertSee(route('services.show', 'nin-verification'));
        $responseAfterDisable->assertSee('NIN Verification');

        // Admin re-enables the service
        $putEnableResponse = $this->actingAs($admin)->put('/admin/charges', [
            'services' => [
                $serviceToDisable->id => [
                    'price' => $serviceToDisable->price,
                    'is_active' => '1',
                ],
            ],
        ]);
        $putEnableResponse->assertSessionHasNoErrors();
        $putEnableResponse->assertRedirect();

        $this->assertTrue($serviceToDisable->fresh()->is_active);

        // User views dashboard - service must reappear in sidebar dropdown
        $responseAfterEnable = $this->actingAs($user)->get('/dashboard');
        $responseAfterEnable->assertStatus(200);
        $responseAfterEnable->assertSee(route('services.show', 'nin-verification-2'));
        $responseAfterEnable->assertSee('NIN Verification 2');
    }
}


