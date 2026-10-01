<?php

namespace Tests\Feature;

use App\Models\Service;
use App\Models\ServiceRequest;
use App\Models\User;
use App\Models\Wallet;
use App\Models\WalletTransaction;
use Database\Seeders\AnnouncementSeeder;
use Database\Seeders\RolePermissionSeeder;
use Database\Seeders\ServiceCatalogSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class AdminFinanceAndTransactionsTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        $this->seed(RolePermissionSeeder::class);
        $this->seed(ServiceCatalogSeeder::class);
        $this->seed(AnnouncementSeeder::class);
    }

    public function test_admin_and_staff_can_access_all_finance_and_transaction_views(): void
    {
        $admin = User::where('email', 'admin@vsite.ng')->first();
        $staff = User::where('email', 'staff@vsite.ng')->first();

        foreach ([$admin, $staff] as $authorizedUser) {
            // Finance views
            $this->actingAs($authorizedUser)->get(route('admin.finance.fund'))
                ->assertStatus(200)
                ->assertSee('Fund User');

            $this->actingAs($authorizedUser)->get(route('admin.finance.transactions'))
                ->assertStatus(200)
                ->assertSee('Wallet Transactions Ledger');

            $this->actingAs($authorizedUser)->get(route('admin.finance.history'))
                ->assertStatus(200)
                ->assertSee('Funding History');

            // Transaction views
            $this->actingAs($authorizedUser)->get(route('admin.transactions.nin'))
                ->assertStatus(200)
                ->assertSee('NIN Verifications');

            $this->actingAs($authorizedUser)->get(route('admin.transactions.bvn'))
                ->assertStatus(200)
                ->assertSee('BVN Verifications');

            $this->actingAs($authorizedUser)->get(route('admin.transactions.deposits'))
                ->assertStatus(200)
                ->assertSee('Deposit History');

            $this->actingAs($authorizedUser)->get(route('admin.transactions.transfers'))
                ->assertStatus(200)
                ->assertSee('Wallet-to-Wallet Transactions');
        }
    }

    public function test_regular_user_and_agent_cannot_access_finance_and_transactions(): void
    {
        $user = User::where('email', 'user@vsite.ng')->first();
        $agent = User::where('email', 'agent@vsite.ng')->first();

        $routes = [
            route('admin.finance.fund'),
            route('admin.finance.transactions'),
            route('admin.finance.history'),
            route('admin.transactions.nin'),
            route('admin.transactions.bvn'),
            route('admin.transactions.deposits'),
            route('admin.transactions.transfers'),
        ];

        foreach ([$user, $agent] as $unauthorizedUser) {
            foreach ($routes as $route) {
                $this->actingAs($unauthorizedUser)->get($route)->assertStatus(403);
            }
        }
    }

    public function test_admin_can_fund_user_via_platform_credit(): void
    {
        $admin = User::where('email', 'admin@vsite.ng')->first();
        $targetUser = User::where('email', 'user@vsite.ng')->first();

        $initialBalance = (float) $targetUser->wallet->fresh()->balance;
        $fundAmount = 5000.00;

        $response = $this->actingAs($admin)->post(route('admin.finance.fund.submit'), [
            'user_id' => $targetUser->id,
            'funding_type' => 'direct_credit',
            'amount' => $fundAmount,
            'description' => 'Testing administrative direct grant',
        ]);

        $response->assertRedirect(route('admin.finance.fund', ['user_id' => $targetUser->id]));
        $response->assertSessionHas('success');

        $finalBalance = (float) $targetUser->wallet->fresh()->balance;
        $this->assertEquals($initialBalance + $fundAmount, $finalBalance);

        $this->assertDatabaseHas('wallet_transactions', [
            'wallet_id' => $targetUser->wallet->id,
            'category' => 'deposit',
            'amount' => $fundAmount,
            'status' => 'successful',
        ]);
    }

    public function test_admin_can_transfer_from_own_wallet_to_user(): void
    {
        $admin = User::where('email', 'admin@vsite.ng')->first();
        $targetUser = User::where('email', 'user@vsite.ng')->first();

        // Ensure admin has sufficient balance
        $admin->wallet->update(['balance' => 10000.00]);
        $targetUser->wallet->update(['balance' => 1000.00]);

        $transferAmount = 2500.00;

        $response = $this->actingAs($admin)->post(route('admin.finance.fund.submit'), [
            'user_id' => $targetUser->id,
            'funding_type' => 'admin_transfer',
            'amount' => $transferAmount,
            'description' => 'Monthly operational allowance',
        ]);

        $response->assertRedirect(route('admin.finance.fund', ['user_id' => $targetUser->id]));
        $response->assertSessionHas('success');

        $this->assertEquals(7500.00, (float) $admin->wallet->fresh()->balance);
        $this->assertEquals(3500.00, (float) $targetUser->wallet->fresh()->balance);

        // Verify debit on admin
        $this->assertDatabaseHas('wallet_transactions', [
            'wallet_id' => $admin->wallet->id,
            'category' => 'transfer_out',
            'amount' => $transferAmount,
            'status' => 'successful',
        ]);

        // Verify credit on target user
        $this->assertDatabaseHas('wallet_transactions', [
            'wallet_id' => $targetUser->wallet->id,
            'category' => 'transfer_in',
            'amount' => $transferAmount,
            'status' => 'successful',
        ]);
    }

    public function test_admin_wallet_funding_fails_if_insufficient_admin_balance(): void
    {
        $admin = User::where('email', 'admin@vsite.ng')->first();
        $targetUser = User::where('email', 'user@vsite.ng')->first();

        // Admin has only 50
        $admin->wallet->update(['balance' => 50.00]);

        $response = $this->actingAs($admin)->post(route('admin.finance.fund.submit'), [
            'user_id' => $targetUser->id,
            'funding_type' => 'admin_transfer',
            'amount' => 500.00,
            'description' => 'Should fail due to insufficient admin funds',
        ]);

        $response->assertSessionHasErrors('amount');
    }

    public function test_audit_views_render_data_records(): void
    {
        $admin = User::where('email', 'admin@vsite.ng')->first();
        $user = User::where('email', 'user@vsite.ng')->first();
        $ninService = Service::where('slug', 'nin-verification')->first();

        // Create a NIN verification service request
        ServiceRequest::create([
            'user_id' => $user->id,
            'service_id' => $ninService->id,
            'reference' => 'SR_AUDIT_TEST_001',
            'tracking_input' => '11223344556',
            'input_payload' => ['nin' => '11223344556'],
            'amount_charged' => 300.00,
            'status' => 'completed',
        ]);

        $resNIN = $this->actingAs($admin)->get(route('admin.transactions.nin'));
        $resNIN->assertStatus(200);
        $resNIN->assertSee('SR_AUDIT_TEST_001');

        // Create a deposit transaction
        WalletTransaction::create([
            'wallet_id' => $user->wallet->id,
            'user_id' => $user->id,
            'type' => 'credit',
            'amount' => 1500.00,
            'balance_before' => 0.00,
            'balance_after' => 1500.00,
            'reference' => 'DEP_AUDIT_TEST_002',
            'category' => 'deposit',
            'status' => 'successful',
            'description' => 'Online Monnify topup',
        ]);

        $resDep = $this->actingAs($admin)->get(route('admin.transactions.deposits'));
        $resDep->assertStatus(200);
        $resDep->assertSee('DEP_AUDIT_TEST_002');
    }

    public function test_admin_can_debit_user_wallet(): void
    {
        $admin = User::where('email', 'admin@vsite.ng')->first();
        $targetUser = User::where('email', 'user@vsite.ng')->first();

        // Give user initial balance of 3,000
        $targetUser->wallet->update(['balance' => 3000.00]);

        $debitAmount = 1200.00;

        $response = $this->actingAs($admin)->post(route('admin.finance.fund.submit'), [
            'user_id' => $targetUser->id,
            'action_type' => 'debit',
            'amount' => $debitAmount,
            'description' => 'Reversal of erroneous credit bonus',
        ]);

        $response->assertRedirect(route('admin.finance.fund', ['user_id' => $targetUser->id]));
        $response->assertSessionHas('success');

        $this->assertEquals(1800.00, (float) $targetUser->wallet->fresh()->balance);

        $this->assertDatabaseHas('wallet_transactions', [
            'wallet_id' => $targetUser->wallet->id,
            'user_id' => $targetUser->id,
            'type' => 'debit',
            'category' => 'admin_debit',
            'amount' => $debitAmount,
            'status' => 'successful',
        ]);
    }

    public function test_admin_cannot_debit_more_than_user_wallet_balance(): void
    {
        $admin = User::where('email', 'admin@vsite.ng')->first();
        $targetUser = User::where('email', 'user@vsite.ng')->first();

        // User has only 400
        $targetUser->wallet->update(['balance' => 400.00]);

        $response = $this->actingAs($admin)->post(route('admin.finance.fund.submit'), [
            'user_id' => $targetUser->id,
            'action_type' => 'debit',
            'amount' => 1000.00,
            'description' => 'Overdraft attempt',
        ]);

        $response->assertSessionHasErrors('amount');
        // Balance remains unchanged
        $this->assertEquals(400.00, (float) $targetUser->wallet->fresh()->balance);
    }
}
