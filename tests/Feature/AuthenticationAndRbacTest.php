<?php

namespace Tests\Feature;

use App\Models\Role;
use App\Models\User;
use Database\Seeders\RolePermissionSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class AuthenticationAndRbacTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        $this->seed(RolePermissionSeeder::class);
    }

    public function test_user_can_view_registration_form(): void
    {
        $response = $this->get('/register');
        $response->assertStatus(200);
        $response->assertSee('Create your account');
    }

    public function test_user_registration_requires_all_8_fields(): void
    {
        $response = $this->post('/register', [
            'first_name' => 'Yakubu',
            'surname' => 'Gowon',
            'middle_name' => 'Dan',
            'email' => 'yakubu@domain.ng',
            'phone_number' => '08011223344',
            'business' => 'Gowon Enterprises',
            'password' => 'Password123!',
            'password_confirmation' => 'Password123!',
        ]);

        $response->assertRedirect('/verification/email');

        $this->assertDatabaseHas('users', [
            'first_name' => 'Yakubu',
            'surname' => 'Gowon',
            'middle_name' => 'Dan',
            'email' => 'yakubu@domain.ng',
            'phone_number' => '08011223344',
            'business' => 'Gowon Enterprises',
            'email_verified_at' => null,
            'phone_verified_at' => null,
        ]);

        $user = User::where('email', 'yakubu@domain.ng')->first();
        $this->assertTrue($user->hasRole('end_user'));
        $this->assertEquals('Yakubu Dan Gowon', $user->full_name);
    }

    public function test_middle_name_is_optional_in_registration(): void
    {
        $response = $this->post('/register', [
            'first_name' => 'Bolanle',
            'surname' => 'Austin',
            'middle_name' => '', // optional
            'email' => 'bolanle@domain.ng',
            'phone_number' => '08099887766',
            'business' => 'Austin Tech',
            'password' => 'Password123!',
            'password_confirmation' => 'Password123!',
        ]);

        $response->assertRedirect('/verification/email');

        $user = User::where('email', 'bolanle@domain.ng')->first();
        $this->assertNull($user->middle_name);
        $this->assertEquals('Bolanle Austin', $user->full_name);
    }

    public function test_dual_verification_enforces_email_then_phone_independently(): void
    {
        $user = User::factory()->create([
            'first_name' => 'Test',
            'surname' => 'Subject',
            'business' => 'Testing Biz',
            'phone_number' => '08055555555',
            'email_verified_at' => null,
            'phone_verified_at' => null,
        ]);

        // Attempting to visit dashboard without verification redirects to email notice
        $response = $this->actingAs($user)->get('/dashboard');
        $response->assertRedirect('/verification/email');

        // Complete email verification
        $verifyEmailResponse = $this->actingAs($user)->post('/verification/email/verify');
        $verifyEmailResponse->assertRedirect('/verification/phone');

        $user->refresh();
        $this->assertNotNull($user->email_verified_at);
        $this->assertNull($user->phone_verified_at, 'Email verification must NOT automatically verify phone number');

        // Attempting to visit dashboard with only email verified redirects to phone notice
        $response2 = $this->actingAs($user)->get('/dashboard');
        $response2->assertRedirect('/verification/phone');

        // Complete phone verification
        $verifyPhoneResponse = $this->actingAs($user)->post('/verification/phone/verify');
        $verifyPhoneResponse->assertRedirect('/dashboard');

        $user->refresh();
        $this->assertTrue($user->isFullyVerified());

        // Now dashboard is accessible
        $dashboardResponse = $this->actingAs($user)->get('/dashboard');
        $dashboardResponse->assertStatus(200);
        $dashboardResponse->assertSee('Good day! Test Subject');
    }

    public function test_super_admin_admin_and_staff_can_access_admin_backend(): void
    {
        $superAdmin = User::where('email', 'superadmin@vsite.ng')->first();
        $admin = User::where('email', 'admin@vsite.ng')->first();
        $staff = User::where('email', 'staff@vsite.ng')->first();

        $this->actingAs($superAdmin)->get('/admin')->assertStatus(200)->assertSee('Welcome, Root Global SuperAdmin');
        $this->actingAs($admin)->get('/admin')->assertStatus(200)->assertSee('Welcome, Farouk Aliyu Administrator');
        $this->actingAs($staff)->get('/admin')->assertStatus(200)->assertSee('Welcome, Zainab Bello Officer');
    }

    public function test_agent_and_end_user_cannot_access_admin_backend(): void
    {
        $agent = User::where('email', 'agent@vsite.ng')->first();
        $endUser = User::where('email', 'user@vsite.ng')->first();

        // Agent gets 403 Forbidden
        $this->actingAs($agent)->get('/admin')->assertStatus(403);

        // End User gets 403 Forbidden
        $this->actingAs($endUser)->get('/admin')->assertStatus(403);
    }

    public function test_admin_button_is_visible_to_admin_roles_and_hidden_from_agent_and_user(): void
    {
        $admin = User::where('email', 'admin@vsite.ng')->first();
        $agent = User::where('email', 'agent@vsite.ng')->first();
        $endUser = User::where('email', 'user@vsite.ng')->first();

        // Admin sees the button
        $responseAdmin = $this->actingAs($admin)->get('/dashboard');
        $responseAdmin->assertSee('Admin Dashboard');

        // Agent DOES NOT see the button (SRS Section 7 & 9)
        $responseAgent = $this->actingAs($agent)->get('/dashboard');
        $responseAgent->assertDontSee('Admin Dashboard');

        // End User DOES NOT see the button (SRS Section 8 & 9)
        $responseUser = $this->actingAs($endUser)->get('/dashboard');
        $responseUser->assertDontSee('Admin Dashboard');
    }
}
