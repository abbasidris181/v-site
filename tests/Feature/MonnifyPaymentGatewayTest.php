<?php

namespace Tests\Feature;

use App\Models\MonnifyVirtualAccount;
use App\Models\PaymentTransaction;
use App\Models\Setting;
use App\Models\User;
use App\Services\Payment\MonnifyService;
use Database\Seeders\RolePermissionSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Http;
use Tests\TestCase;

class MonnifyPaymentGatewayTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        $this->seed(RolePermissionSeeder::class);
    }

    public function test_admin_can_update_monnify_keys_and_settings(): void
    {
        $admin = User::where('email', 'superadmin@vsite.ng')->first();

        $response = $this->actingAs($admin)->put(route('admin.settings.configurations'), [
            'site_name' => 'V-SITE TEST',
            'contact_email' => 'admin@vsite.ng',
            'contact_phone' => '+234 800 000 0000',
            'min_wallet_deposit' => 500.00,
            'monnify_enabled' => '1',
            'monnify_environment' => 'LIVE',
            'monnify_contract_code' => '8472910482',
            'monnify_api_key' => 'MK_LIVE_TEST_KEY_12345',
            'monnify_secret_key' => 'SEC_LIVE_TEST_SECRET_67890',
        ]);

        $response->assertRedirect(route('admin.settings.index', ['tab' => 'configurations']));
        $response->assertSessionHas('status');

        $this->assertEquals('1', Setting::get('monnify_enabled'));
        $this->assertEquals('LIVE', Setting::get('monnify_environment'));
        $this->assertEquals('8472910482', Setting::get('monnify_contract_code'));
        $this->assertEquals('MK_LIVE_TEST_KEY_12345', Setting::get('monnify_api_key'));
        $this->assertEquals('SEC_LIVE_TEST_SECRET_67890', Setting::get('monnify_secret_key'));
    }

    public function test_monnify_service_resolves_credentials_with_db_precedence(): void
    {
        Setting::set('monnify_api_key', 'MK_CUSTOM_DB_KEY', 'monnify');
        Setting::set('monnify_secret_key', 'SEC_CUSTOM_DB_SECRET', 'monnify');
        Setting::set('monnify_contract_code', '9988776655', 'monnify');
        Setting::set('monnify_environment', 'LIVE', 'monnify');
        Setting::set('monnify_enabled', '1', 'monnify');

        $service = app(MonnifyService::class);

        $this->assertEquals('MK_CUSTOM_DB_KEY', $service->getApiKey());
        $this->assertEquals('SEC_CUSTOM_DB_SECRET', $service->getSecretKey());
        $this->assertEquals('9988776655', $service->getContractCode());
        $this->assertEquals('LIVE', $service->getEnvironment());
        $this->assertTrue($service->isEnabled());
        $this->assertTrue($service->isConfigured());
        $this->assertEquals('https://api.monnify.com', $service->getBaseUrl());
    }

    public function test_admin_can_test_monnify_connection_endpoint(): void
    {
        Setting::set('monnify_api_key', 'MK_TEST_KEY', 'monnify');
        Setting::set('monnify_secret_key', 'SEC_TEST_SECRET', 'monnify');
        Setting::set('monnify_environment', 'SANDBOX', 'monnify');

        Http::fake([
            'https://sandbox.monnify.com/api/v1/auth/login' => Http::response([
                'requestSuccessful' => true,
                'responseMessage' => 'success',
                'responseCode' => '0',
                'responseBody' => [
                    'accessToken' => 'fake_oauth_token_12345',
                    'expiresIn' => 3600,
                ],
            ], 200),
        ]);

        $admin = User::where('email', 'superadmin@vsite.ng')->first();

        $response = $this->actingAs($admin)->postJson(route('admin.settings.monnify.test'));

        $response->assertStatus(200);
        $response->assertJson([
            'success' => true,
            'environment' => 'SANDBOX',
        ]);
    }

    public function test_user_can_initialize_monnify_payment(): void
    {
        Setting::set('monnify_api_key', 'MK_TEST_KEY', 'monnify');
        Setting::set('monnify_secret_key', 'SEC_TEST_SECRET', 'monnify');
        Setting::set('monnify_contract_code', '1234567890', 'monnify');
        Setting::set('monnify_environment', 'SANDBOX', 'monnify');
        Setting::set('monnify_enabled', '1', 'monnify');

        Http::fake([
            'https://sandbox.monnify.com/api/v1/auth/login' => Http::response([
                'requestSuccessful' => true,
                'responseBody' => ['accessToken' => 'fake_token', 'expiresIn' => 3600],
            ], 200),
            'https://sandbox.monnify.com/api/v1/merchant/transactions/init-transaction' => Http::response([
                'requestSuccessful' => true,
                'responseMessage' => 'success',
                'responseBody' => [
                    'transactionReference' => 'MNFY|TEST|12345',
                    'paymentReference' => 'MNFY_DEP_MOCKREF',
                    'authorizedAmount' => 5000.00,
                    'checkoutUrl' => 'https://sandbox.monnify.com/checkout/mock-session-url',
                ],
            ], 200),
        ]);

        $user = User::where('email', 'user@vsite.ng')->first();

        $response = $this->actingAs($user)->post(route('wallet.monnify.initialize'), [
            'amount' => 5000,
        ]);

        $response->assertRedirect('https://sandbox.monnify.com/checkout/mock-session-url');

        $this->assertDatabaseHas('payment_transactions', [
            'user_id' => $user->id,
            'amount' => 5000.00,
            'status' => 'pending',
            'checkout_url' => 'https://sandbox.monnify.com/checkout/mock-session-url',
            'gateway' => 'monnify',
        ]);
    }

    public function test_monnify_callback_verifies_payment_and_credits_wallet(): void
    {
        $user = User::where('email', 'user@vsite.ng')->first();
        $initialBalance = (float) $user->wallet->balance;

        $payment = PaymentTransaction::create([
            'user_id' => $user->id,
            'payment_reference' => 'MNFY_DEP_CALLBACK_TEST',
            'amount' => 5000.00,
            'status' => 'pending',
            'gateway' => 'monnify',
        ]);

        Setting::set('monnify_api_key', 'MK_TEST_KEY', 'monnify');
        Setting::set('monnify_secret_key', 'SEC_TEST_SECRET', 'monnify');
        Setting::set('monnify_environment', 'SANDBOX', 'monnify');

        Http::fake([
            'https://sandbox.monnify.com/api/v1/auth/login' => Http::response([
                'requestSuccessful' => true,
                'responseBody' => ['accessToken' => 'fake_token', 'expiresIn' => 3600],
            ], 200),
            'https://sandbox.monnify.com/api/v2/merchant/transactions/query?paymentReference=MNFY_DEP_CALLBACK_TEST' => Http::response([
                'requestSuccessful' => true,
                'responseBody' => [
                    'paymentReference' => 'MNFY_DEP_CALLBACK_TEST',
                    'transactionReference' => 'MNFY|SUCCESS|99999',
                    'amountPaid' => 5000.00,
                    'paymentStatus' => 'PAID',
                    'paymentMethod' => 'CARD',
                    'paidOn' => '2026-09-19 01:00:00',
                ],
            ], 200),
        ]);

        $response = $this->actingAs($user)->get(route('wallet.monnify.callback', [
            'paymentReference' => 'MNFY_DEP_CALLBACK_TEST',
        ]));

        $response->assertRedirect(route('wallet.index'));
        $response->assertSessionHas('success');

        $user->wallet->refresh();
        $this->assertEquals($initialBalance + 5000.00, (float) $user->wallet->balance);

        $payment->refresh();
        $this->assertEquals('paid', $payment->status);
        $this->assertEquals('MNFY|SUCCESS|99999', $payment->transaction_reference);
    }

    public function test_monnify_webhook_processes_valid_signature_and_credits_wallet_idempotently(): void
    {
        $user = User::where('email', 'user@vsite.ng')->first();
        $initialBalance = (float) $user->wallet->balance;
        $secretKey = 'SEC_WEBHOOK_SECRET_KEY';

        Setting::set('monnify_secret_key', $secretKey, 'monnify');

        $ref = 'MNFY_DEP_WEBHOOK_12345';
        $payloadData = [
            'eventType' => 'SUCCESSFUL_TRANSACTION',
            'eventData' => [
                'transactionReference' => 'MNFY|TXN|12345',
                'paymentReference' => $ref,
                'amountPaid' => 7500.00,
                'totalPayable' => 7500.00,
                'paidOn' => '2026-09-19 01:30:00',
                'paymentStatus' => 'PAID',
                'paymentMethod' => 'ACCOUNT_TRANSFER',
                'currency' => 'NGN',
                'customer' => [
                    'email' => $user->email,
                    'name' => $user->full_name,
                ],
            ],
        ];

        $rawJson = json_encode($payloadData);
        $signature = hash_hmac('sha512', $rawJson, $secretKey);

        // 1. First webhook transmission
        $response = $this->postJson(route('webhooks.monnify'), $payloadData, [
            'monnify-signature' => $signature,
        ]);

        $response->assertStatus(200);
        $response->assertJson(['status' => 'success']);

        $user->wallet->refresh();
        $this->assertEquals($initialBalance + 7500.00, (float) $user->wallet->balance);

        $this->assertDatabaseHas('payment_transactions', [
            'payment_reference' => $ref,
            'status' => 'paid',
            'amount' => 7500.00,
        ]);

        // 2. Second webhook transmission (Idempotency test)
        $repeatResponse = $this->postJson(route('webhooks.monnify'), $payloadData, [
            'monnify-signature' => $signature,
        ]);

        $repeatResponse->assertStatus(200);

        // Balance should NOT increase again
        $user->wallet->refresh();
        $this->assertEquals($initialBalance + 7500.00, (float) $user->wallet->balance);
    }

    public function test_monnify_webhook_rejects_invalid_signature(): void
    {
        Setting::set('monnify_secret_key', 'LEGIT_SECRET_KEY', 'monnify');

        $payloadData = [
            'eventType' => 'SUCCESSFUL_TRANSACTION',
            'eventData' => [
                'paymentReference' => 'MNFY_TAMPERED',
                'amountPaid' => 50000.00,
                'paymentStatus' => 'PAID',
            ],
        ];

        $response = $this->postJson(route('webhooks.monnify'), $payloadData, [
            'monnify-signature' => 'invalid_tampered_signature_string',
        ]);

        $response->assertStatus(400);
        $response->assertJson(['status' => 'error']);
    }

    public function test_user_can_retrieve_or_generate_reserved_virtual_accounts(): void
    {
        $user = User::where('email', 'user@vsite.ng')->first();

        $accounts = $user->getMonnifyVirtualAccounts();

        $this->assertCount(3, $accounts);
        $this->assertEquals('Moniepoint MFB', $accounts[0]['bank_name']);
        $this->assertEquals('Sterling Bank', $accounts[1]['bank_name']);
        $this->assertEquals('Wema Bank', $accounts[2]['bank_name']);

        // Assert database persistence in monnify_virtual_accounts table
        $this->assertDatabaseCount('monnify_virtual_accounts', 3);
        $this->assertDatabaseHas('monnify_virtual_accounts', [
            'user_id' => $user->id,
            'bank_code' => '50515',
            'reservation_status' => 'ACTIVE',
        ]);

        // Assert finder by virtual account
        $moniepointAccNum = $accounts[0]['account_number'];
        $foundUser = User::findByVirtualAccount($moniepointAccNum);
        $this->assertNotNull($foundUser);
        $this->assertEquals($user->id, $foundUser->id);
    }

    public function test_monnify_service_creates_reserved_accounts_via_api(): void
    {
        Setting::set('monnify_api_key', 'MK_TEST_KEY', 'monnify');
        Setting::set('monnify_secret_key', 'SEC_TEST_SECRET', 'monnify');
        Setting::set('monnify_contract_code', '1234567890', 'monnify');
        Setting::set('monnify_environment', 'SANDBOX', 'monnify');

        Http::fake([
            'https://sandbox.monnify.com/api/v1/auth/login' => Http::response([
                'requestSuccessful' => true,
                'responseBody' => ['accessToken' => 'fake_token', 'expiresIn' => 3600],
            ], 200),
            'https://sandbox.monnify.com/api/v2/bank-transfer/reserved-accounts' => Http::response([
                'requestSuccessful' => true,
                'responseMessage' => 'success',
                'responseCode' => '0',
                'responseBody' => [
                    'contractCode' => '1234567890',
                    'accountReference' => 'VSITE_ACC_USR_API_TEST',
                    'accountName' => 'VSITE / JOHN DOE',
                    'reservationStatus' => 'ACTIVE',
                    'accounts' => [
                        [
                            'bankCode' => '50515',
                            'bankName' => 'Moniepoint MFB',
                            'accountNumber' => '8999888777',
                        ],
                        [
                            'bankCode' => '232',
                            'bankName' => 'Sterling Bank',
                            'accountNumber' => '8299888777',
                        ],
                        [
                            'bankCode' => '035',
                            'bankName' => 'Wema Bank',
                            'accountNumber' => '9999888777',
                        ],
                    ],
                ],
            ], 200),
        ]);

        $user = User::where('email', 'user@vsite.ng')->first();
        $service = app(MonnifyService::class);

        $result = $service->createReservedAccount($user);

        $this->assertTrue($result['success']);
        $this->assertCount(3, $result['accounts']);

        $this->assertDatabaseHas('monnify_virtual_accounts', [
            'user_id' => $user->id,
            'bank_code' => '50515',
            'account_number' => '8999888777',
        ]);
        $this->assertDatabaseHas('monnify_virtual_accounts', [
            'user_id' => $user->id,
            'bank_code' => '232',
            'account_number' => '8299888777',
        ]);
        $this->assertDatabaseHas('monnify_virtual_accounts', [
            'user_id' => $user->id,
            'bank_code' => '035',
            'account_number' => '9999888777',
        ]);
    }

    public function test_webhook_settles_direct_reserved_bank_transfer_to_user_wallet(): void
    {
        $user = User::where('email', 'user@vsite.ng')->first();
        $initialBalance = (float) $user->wallet->balance;
        $secretKey = 'SEC_WEBHOOK_SECRET_KEY';

        Setting::set('monnify_secret_key', $secretKey, 'monnify');

        // Create virtual account for user
        MonnifyVirtualAccount::create([
            'user_id' => $user->id,
            'account_reference' => 'VSITE_ACC_USR_' . $user->id,
            'account_name' => 'VSITE / ' . strtoupper($user->full_name),
            'bank_name' => 'Moniepoint MFB',
            'bank_code' => '50515',
            'bank_slug' => 'moniepoint',
            'account_number' => '8012345678',
            'reservation_status' => 'ACTIVE',
        ]);

        $txnRef = 'MNFY_BT_' . time();
        $payloadData = [
            'eventType' => 'SUCCESSFUL_TRANSACTION',
            'eventData' => [
                'product' => [
                    'type' => 'RESERVED_ACCOUNT',
                    'reference' => 'VSITE_ACC_USR_' . $user->id,
                ],
                'transactionReference' => $txnRef,
                'paymentReference' => $txnRef,
                'amountPaid' => 12500.00,
                'totalPayable' => 12500.00,
                'settlementAmount' => 12350.00,
                'paidOn' => '2026-09-19 15:30:00',
                'paymentStatus' => 'PAID',
                'paymentMethod' => 'ACCOUNT_TRANSFER',
                'currency' => 'NGN',
                'customer' => [
                    'email' => $user->email,
                    'name' => $user->full_name,
                ],
                'destinationAccountInformation' => [
                    'bankCode' => '50515',
                    'bankName' => 'Moniepoint MFB',
                    'accountNumber' => '8012345678',
                ],
            ],
        ];

        $rawJson = json_encode($payloadData);
        $signature = hash_hmac('sha512', $rawJson, $secretKey);

        $response = $this->postJson(route('webhooks.monnify'), $payloadData, [
            'monnify-signature' => $signature,
        ]);

        $response->assertStatus(200);
        $response->assertJson(['status' => 'success']);

        $user->wallet->refresh();
        $this->assertEquals($initialBalance + 12500.00, (float) $user->wallet->balance);

        $this->assertDatabaseHas('payment_transactions', [
            'user_id' => $user->id,
            'payment_reference' => $txnRef,
            'payment_method' => 'ACCOUNT_TRANSFER',
            'amount' => 12500.00,
            'status' => 'paid',
        ]);
    }

    public function test_multiple_monnify_webhooks_for_same_transaction_only_fund_wallet_once(): void
    {
        $user = User::where('email', 'user@vsite.ng')->first();
        $initialBalance = (float) $user->wallet->balance;
        $secretKey = 'SEC_WEBHOOK_STRICT_IDEMPOTENCY';

        Setting::set('monnify_secret_key', $secretKey, 'monnify');

        $paymentRef = 'MNFY_DUP_TEST_' . time();
        $txnRef = 'TRX_DUP_' . time();

        $payloadData = [
            'eventType' => 'SUCCESSFUL_TRANSACTION',
            'eventData' => [
                'transactionReference' => $txnRef,
                'paymentReference' => $paymentRef,
                'amountPaid' => 5000.00,
                'totalPayable' => 5000.00,
                'paidOn' => '2026-09-20 08:15:00',
                'paymentStatus' => 'PAID',
                'paymentMethod' => 'ACCOUNT_TRANSFER',
                'currency' => 'NGN',
                'customer' => [
                    'email' => $user->email,
                    'name' => $user->full_name,
                ],
            ],
        ];

        $rawJson = json_encode($payloadData);
        $signature = hash_hmac('sha512', $rawJson, $secretKey);

        // Send the exact same webhook 4 times in a row (simulating network retries and duplicate webhooks)
        for ($attempt = 1; $attempt <= 4; $attempt++) {
            $response = $this->postJson(route('webhooks.monnify'), $payloadData, [
                'monnify-signature' => $signature,
            ]);

            $response->assertStatus(200);
            $response->assertJson(['status' => 'success']);
        }

        // Verify balance was credited EXACTLY once (+5000, NOT +20000)
        $user->wallet->refresh();
        $this->assertEquals($initialBalance + 5000.00, (float) $user->wallet->balance);

        // Verify exactly one ledger entry was written
        $ledgerEntries = \App\Models\WalletTransaction::where('idempotency_key', 'monnify_' . $paymentRef)->count();
        $this->assertEquals(1, $ledgerEntries);

        // Verify exactly one PaymentTransaction record exists and is marked paid
        $paymentRecords = PaymentTransaction::where('payment_reference', $paymentRef)->count();
        $this->assertEquals(1, $paymentRecords);
    }
}
