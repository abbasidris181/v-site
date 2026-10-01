<?php

namespace Tests\Feature;

use App\Exceptions\InsufficientWalletBalanceException;
use App\Models\User;
use App\Models\WalletTransaction;
use App\Services\WalletService;
use Database\Seeders\RolePermissionSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class WalletLedgerTest extends TestCase
{
    use RefreshDatabase;

    protected WalletService $walletService;

    protected function setUp(): void
    {
        parent::setUp();
        $this->seed(RolePermissionSeeder::class);
        $this->walletService = app(WalletService::class);
    }

    public function test_user_can_deposit_funds_and_ledger_is_created(): void
    {
        $user = User::where('email', 'user@vsite.ng')->first();
        $wallet = $user->wallet;
        $initialBalance = (float) $wallet->balance;

        $response = $this->actingAs($user)->post('/wallet/deposit', [
            'amount' => 5000,
        ]);

        $response->assertRedirect();
        $response->assertSessionHas('success');

        $wallet->refresh();
        $this->assertEquals($initialBalance + 5000.00, (float) $wallet->balance);

        $this->assertDatabaseHas('wallet_transactions', [
            'user_id' => $user->id,
            'type' => 'credit',
            'category' => 'deposit',
            'amount' => 5000.00,
            'balance_before' => $initialBalance,
            'balance_after' => $initialBalance + 5000.00,
            'status' => 'successful',
        ]);
    }

    public function test_deposit_idempotency_prevents_double_crediting(): void
    {
        $user = User::where('email', 'user@vsite.ng')->first();
        $idempotencyKey = 'IDEM_KEY_' . uniqid();

        // First deposit
        $tx1 = $this->walletService->deposit(
            user: $user,
            amount: 2500.00,
            description: 'Idempotent Deposit',
            idempotencyKey: $idempotencyKey
        );

        $balanceAfterFirst = (float) $user->wallet->fresh()->balance;

        // Duplicate call with same key
        $tx2 = $this->walletService->deposit(
            user: $user,
            amount: 2500.00,
            description: 'Idempotent Deposit Retry',
            idempotencyKey: $idempotencyKey
        );

        $balanceAfterSecond = (float) $user->wallet->fresh()->balance;

        $this->assertEquals($tx1->id, $tx2->id);
        $this->assertEquals($balanceAfterFirst, $balanceAfterSecond, 'Balance must not change on duplicate idempotency key');
        $this->assertEquals(1, WalletTransaction::where('idempotency_key', $idempotencyKey)->count());
    }

    public function test_wallet_charge_deducts_balance_and_records_debit(): void
    {
        $user = User::where('email', 'user@vsite.ng')->first();
        $wallet = $user->wallet;
        $initialBalance = (float) $wallet->balance;

        $tx = $this->walletService->charge(
            user: $user,
            amount: 1500.00,
            category: 'service_charge',
            description: 'NIN Search Charge'
        );

        $this->assertEquals('debit', $tx->type);
        $this->assertEquals(1500.00, $tx->amount);
        $this->assertEquals($initialBalance, $tx->balance_before);
        $this->assertEquals($initialBalance - 1500.00, $tx->balance_after);
        $this->assertEquals($initialBalance - 1500.00, (float) $wallet->fresh()->balance);
    }

    public function test_wallet_charge_fails_when_balance_is_insufficient(): void
    {
        $user = User::where('email', 'user@vsite.ng')->first();
        $wallet = $user->wallet;
        $currentBalance = (float) $wallet->balance;

        $this->expectException(InsufficientWalletBalanceException::class);

        // Attempt to charge more than balance
        $this->walletService->charge(
            user: $user,
            amount: $currentBalance + 10000.00,
            category: 'service_charge',
            description: 'Excessive Charge'
        );
    }

    public function test_admin_can_transfer_funds_to_user_with_double_entry_ledger(): void
    {
        $admin = User::where('email', 'admin@vsite.ng')->first();
        $user = User::where('email', 'user@vsite.ng')->first();

        // Ensure Admin has ₦50,000 and User has ₦8,000 (SRS Section 5 example)
        $admin->wallet->update(['balance' => 50000.00]);
        $user->wallet->update(['balance' => 8000.00]);

        $response = $this->actingAs($admin)->post('/wallet/transfer', [
            'recipient' => $user->email,
            'amount' => 10000.00,
            'description' => 'Admin Test Allocation',
        ]);

        $response->assertRedirect();
        $response->assertSessionHas('success');

        // Admin balance: 50,000 - 10,000 = 40,000
        $this->assertEquals(40000.00, (float) $admin->wallet->fresh()->balance);

        // User balance: 8,000 + 10,000 = 18,000
        $this->assertEquals(18000.00, (float) $user->wallet->fresh()->balance);

        // Double-entry ledger verification
        $this->assertDatabaseHas('wallet_transactions', [
            'user_id' => $admin->id,
            'type' => 'debit',
            'category' => 'transfer_out',
            'amount' => 10000.00,
            'balance_before' => 50000.00,
            'balance_after' => 40000.00,
            'counterpart_user_id' => $user->id,
        ]);

        $this->assertDatabaseHas('wallet_transactions', [
            'user_id' => $user->id,
            'type' => 'credit',
            'category' => 'transfer_in',
            'amount' => 10000.00,
            'balance_before' => 8000.00,
            'balance_after' => 18000.00,
            'counterpart_user_id' => $admin->id,
        ]);
    }

    public function test_agent_can_transfer_funds_to_user(): void
    {
        $agent = User::where('email', 'agent@vsite.ng')->first();
        $user = User::where('email', 'user@vsite.ng')->first();

        $agent->wallet->update(['balance' => 30000.00]);
        $user->wallet->update(['balance' => 8000.00]);

        $response = $this->actingAs($agent)->post('/wallet/transfer', [
            'recipient' => $user->phone_number, // Can use phone number!
            'amount' => 5000.00,
        ]);

        $response->assertRedirect();
        $response->assertSessionHas('success');

        $this->assertEquals(25000.00, (float) $agent->wallet->fresh()->balance);
        $this->assertEquals(13000.00, (float) $user->wallet->fresh()->balance);
    }

    public function test_end_user_can_send_funds_by_email_or_phone_to_other_users(): void
    {
        $endUser = User::where('email', 'user@vsite.ng')->first();
        $admin = User::where('email', 'admin@vsite.ng')->first();

        $endUser->wallet->update(['balance' => 8000.00]);
        $adminInitial = (float) $admin->wallet->balance;

        // End user transfers funds
        $response = $this->actingAs($endUser)->post('/wallet/transfer', [
            'recipient' => $admin->email,
            'amount' => 1000.00,
            'description' => 'User send fund',
        ]);

        $response->assertRedirect();
        $response->assertSessionHas('success');
        $this->assertEquals(7000.00, (float) $endUser->wallet->fresh()->balance);
        $this->assertEquals($adminInitial + 1000.00, (float) $admin->wallet->fresh()->balance);
    }

    public function test_cannot_transfer_more_than_available_wallet_balance(): void
    {
        $admin = User::where('email', 'admin@vsite.ng')->first();
        $user = User::where('email', 'user@vsite.ng')->first();

        $admin->wallet->update(['balance' => 5000.00]);

        $response = $this->actingAs($admin)->post('/wallet/transfer', [
            'recipient' => $user->email,
            'amount' => 10000.00, // Exceeds balance
        ]);

        $response->assertSessionHasErrors('amount');
        $this->assertEquals(5000.00, (float) $admin->wallet->fresh()->balance);
    }

    public function test_cannot_transfer_funds_to_self(): void
    {
        $admin = User::where('email', 'admin@vsite.ng')->first();

        $response = $this->actingAs($admin)->post('/wallet/transfer', [
            'recipient' => $admin->email,
            'amount' => 1000.00,
        ]);

        $response->assertSessionHasErrors('recipient');
    }

    public function test_wallet_funding_page_renders_dedicated_virtual_accounts_and_no_deposit_input_card(): void
    {
        $user = User::where('email', 'user@vsite.ng')->first();

        $response = $this->actingAs($user)->get(route('wallet.index'));

        $response->assertStatus(200);
        $response->assertSee('Automated Bank Transfer Funding');
        $response->assertSee('Moniepoint MFB');
        $response->assertSee('Sterling Bank');
        $response->assertSee('Wema Bank');
        $response->assertSee('Current Available Balance');

        // Assert that the deposit amount card and quick presets are removed
        $response->assertDontSee('Deposit Amount (₦)');
        $response->assertDontSee('Quick Preset Amounts:');
        $response->assertDontSee('Pay with Monnify (Cards & Bank Transfer)');
        $response->assertDontSee('Or use Direct Wallet Credit');
    }
}
