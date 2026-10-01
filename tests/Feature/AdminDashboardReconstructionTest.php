<?php

namespace Tests\Feature;

use App\Models\Service;
use App\Models\ServiceRequest;
use App\Models\User;
use App\Models\Wallet;
use App\Models\WalletTransaction;
use Carbon\Carbon;
use Database\Seeders\AnnouncementSeeder;
use Database\Seeders\RolePermissionSeeder;
use Database\Seeders\ServiceCatalogSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class AdminDashboardReconstructionTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        $this->seed(RolePermissionSeeder::class);
        $this->seed(ServiceCatalogSeeder::class);
        $this->seed(AnnouncementSeeder::class);
    }

    public function test_admin_and_staff_can_access_reconstructed_dashboard(): void
    {
        $admin = User::where('email', 'admin@vsite.ng')->first();
        $staff = User::where('email', 'staff@vsite.ng')->first();

        foreach ([$admin, $staff] as $authorizedUser) {
            $response = $this->actingAs($authorizedUser)->get('/admin');

            $response->assertStatus(200);

            // Top Row KPI Cards
            $response->assertSee("TOTAL USERS BALANCE");
            $response->assertSee("TODAY'S TOTAL DEPOSIT");
            $response->assertSee("TODAY'S TOTAL USERS");
            $response->assertSee("TODAY'S NIN VERIFICATIONS");
            $response->assertSee("TODAY'S BVN VERIFICATIONS");
            $response->assertSee('208,000.00');

            // Bottom Row Left: Financial Overview
            $response->assertSee("TODAY'S FINANCIAL OVERVIEW");
            $response->assertSee('DEPOSIT VS EXPENSE');
            $response->assertSee('Deposit');
            $response->assertSee('Expense');

            // Bottom Row Right: Manual Services Today
            $response->assertSee('MANUAL SERVICES — TODAY');
            $response->assertSee('IPE');
            $response->assertSee('NIN');
            $response->assertSee('MOD');
            $response->assertSee('BVN');
            $response->assertSee('DELINK');
            $response->assertSee('Volume of individual accepted requests submitted today.');
        }
    }

    public function test_regular_user_and_agent_are_forbidden(): void
    {
        $user = User::where('email', 'user@vsite.ng')->first();
        $agent = User::where('email', 'agent@vsite.ng')->first();

        $this->actingAs($user)->get('/admin')->assertStatus(403);
        $this->actingAs($agent)->get('/admin')->assertStatus(403);
    }

    public function test_today_metrics_dynamically_reflect_database_activity(): void
    {
        $admin = User::where('email', 'admin@vsite.ng')->first();
        $user = User::where('email', 'user@vsite.ng')->first();

        // 1. Create a deposit today
        WalletTransaction::create([
            'wallet_id' => $user->wallet->id,
            'user_id' => $user->id,
            'type' => 'credit',
            'amount' => 485000.00,
            'balance_before' => 0.00,
            'balance_after' => 485000.00,
            'reference' => 'WTX_DEP_TODAY_485K',
            'category' => 'deposit',
            'status' => 'successful',
            'description' => 'Today Inbound Deposit',
            'created_at' => Carbon::now(),
        ]);

        // 2. Create an expense today
        WalletTransaction::create([
            'wallet_id' => $user->wallet->id,
            'user_id' => $user->id,
            'type' => 'debit',
            'amount' => 172000.00,
            'balance_before' => 485000.00,
            'balance_after' => 313000.00,
            'reference' => 'WTX_EXP_TODAY_172K',
            'category' => 'service_charge',
            'status' => 'successful',
            'description' => 'Today API Verification Expense',
            'created_at' => Carbon::now(),
        ]);

        // 3. Create automated NIN and BVN verifications today
        $ninService = Service::where('slug', 'nin-verification')->first();
        $bvnService = Service::where('slug', 'bvn-verification')->first();

        ServiceRequest::create([
            'user_id' => $user->id,
            'service_id' => $ninService->id,
            'reference' => 'SR_NIN_TODAY_001',
            'tracking_input' => '11223344556',
            'input_payload' => ['nin' => '11223344556'],
            'amount_charged' => 500.00,
            'status' => 'completed',
            'created_at' => Carbon::now(),
        ]);

        ServiceRequest::create([
            'user_id' => $user->id,
            'service_id' => $bvnService->id,
            'reference' => 'SR_BVN_TODAY_001',
            'tracking_input' => '22334455667',
            'input_payload' => ['bvn' => '22334455667'],
            'amount_charged' => 500.00,
            'status' => 'completed',
            'created_at' => Carbon::now(),
        ]);

        // 4. Create manual service requests today
        $ipeService = Service::where('slug', 'ipe-clearing')->first();
        ServiceRequest::create([
            'user_id' => $user->id,
            'service_id' => $ipeService->id,
            'reference' => 'SR_IPE_TODAY_001',
            'tracking_input' => 'TRACKING_IPE_999',
            'input_payload' => ['tracking_id' => 'TRACKING_IPE_999'],
            'amount_charged' => 500.00,
            'status' => 'pending',
            'created_at' => Carbon::now(),
        ]);

        $response = $this->actingAs($admin)->get('/admin');

        $response->assertStatus(200);
        $response->assertSee('693,000'); // 208,000 seeded baseline + 485,000 today deposit
        $response->assertSee('172,000');
        $response->assertSee('521,000'); // Net differential
    }

    public function test_total_users_balance_reflects_wallet_sums_on_dashboard(): void
    {
        $admin = User::where('email', 'admin@vsite.ng')->first();
        $user = User::where('email', 'user@vsite.ng')->first();

        // Check initial seeded total (100,000 + 50,000 + 20,000 + 30,000 + 8,000 = 208,000.00)
        $response = $this->actingAs($admin)->get('/admin');
        $response->assertStatus(200);
        $response->assertSee('TOTAL USERS BALANCE');
        $response->assertSee('208,000.00');

        // Increase user's wallet balance by 45,250.75
        $user->wallet->increment('balance', 45250.75);

        // Verify updated balance on admin dashboard: 208,000.00 + 45,250.75 = 253,250.75
        $response2 = $this->actingAs($admin)->get('/admin');
        $response2->assertStatus(200);
        $response2->assertSee('253,250.75');
    }
}
