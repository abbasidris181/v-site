<?php

namespace Tests\Feature;

use App\Models\Service;
use App\Models\ServiceBatch;
use App\Models\ServiceRequest;
use App\Models\User;
use Database\Seeders\AnnouncementSeeder;
use Database\Seeders\RolePermissionSeeder;
use Database\Seeders\ServiceCatalogSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class DashboardAndServiceViewTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        $this->seed(RolePermissionSeeder::class);
        $this->seed(ServiceCatalogSeeder::class);
        $this->seed(AnnouncementSeeder::class);
    }

    public function test_user_dashboard_displays_greeting_wallet_balance_and_announcements(): void
    {
        $user = User::where('email', 'user@vsite.ng')->first();

        $response = $this->actingAs($user)->get('/dashboard');

        $response->assertStatus(200);
        $response->assertSee('Good day! Amina Danjuma');
        $response->assertSee('₦8,000.00'); // End User seeded balance
        $response->assertSee('Announcement');
        $response->assertSee('Welcome to V-PORTAL Verification Platform');
    }

    public function test_dashboard_renders_all_dynamic_services_without_grouping_badges(): void
    {
        $user = User::where('email', 'user@vsite.ng')->first();

        $response = $this->actingAs($user)->get('/dashboard');

        $response->assertStatus(200);

        // Verify services appear dynamically
        $response->assertSee('NIN Verification');
        $response->assertSee('NIN Verification 2');
        $response->assertSee('NIN Verification 3');
        $response->assertSee('BVN Verification');
        $response->assertSee('IPE Clearing');
        $response->assertSee('NIN Validation');
        $response->assertSee('Modification IPE');
        $response->assertSee('BVN Retrieval by Phone Number');
        $response->assertSee('Self-Service Delinking');

        // Verify dashboard icons are rendered for NIN Verification 2 and 3
        $response->assertSee('images/services/nin-verification-2.png');
        $response->assertSee('images/services/nin-verification-3.png');

        // CRITICAL CONSTRAINT: Must NOT display grouping badges in the UI!
        $response->assertDontSee('Instant API');
        $response->assertDontSee('Manual Processing');
    }

    public function test_user_can_navigate_to_individual_service_page_with_history_table(): void
    {
        $user = User::where('email', 'user@vsite.ng')->first();

        $response = $this->actingAs($user)->get('/services/ipe-clearing');

        $response->assertStatus(200);
        $response->assertSee('IPE Clearing');
        $response->assertSee('₦500.00');
        $response->assertSee('Total Records');
        $response->assertSee('Batch ID');
        $response->assertSee('Old Tracking ID');
        $response->assertSee('Reply');
    }

    public function test_standard_services_retain_default_table_headers(): void
    {
        $user = User::where('email', 'user@vsite.ng')->first();

        $response = $this->actingAs($user)->get('/services/nin-verification');

        $response->assertStatus(200);
        $response->assertSee('Input');
        $response->assertDontSee('Old Tracking ID');
        $response->assertDontSee('Batch ID');
    }

    public function test_modification_ipe_has_specialized_table_headers(): void
    {
        $user = User::where('email', 'user@vsite.ng')->first();

        $response = $this->actingAs($user)->get('/services/modification-ipe');

        $response->assertStatus(200);
        $response->assertSee('Modification IPE');
        $response->assertSee('Batch ID');
        $response->assertSee('Old Tracking ID');
        $response->assertSee('Reply');
    }

    public function test_bvn_retrieval_displays_multi_field_dynamic_form(): void
    {
        $user = User::where('email', 'user@vsite.ng')->first();

        $response = $this->actingAs($user)->get('/services/bvn-retrieval');

        $response->assertStatus(200);
        $response->assertSee('Customer Full Legal Name');
        $response->assertSee('Registered Phone Number');
        $response->assertSee('₦1,000.00');
    }

    public function test_non_existent_or_inactive_service_aborts_404(): void
    {
        $user = User::where('email', 'user@vsite.ng')->first();

        $response = $this->actingAs($user)->get('/services/unknown-slug-xyz');
        $response->assertStatus(404);

        // Inactive service test
        $inactiveService = Service::create([
            'category_id' => 1,
            'name' => 'Deactivated Service',
            'slug' => 'deactivated-service',
            'type' => 'manual',
            'price' => 500,
            'is_active' => false,
        ]);

        $responseInactive = $this->actingAs($user)->get('/services/deactivated-service');
        $responseInactive->assertStatus(404);
    }

    public function test_interactive_batch_inspector_and_8_digit_batch_generation(): void
    {
        $user = User::where('email', 'user@vsite.ng')->first();
        $service = Service::where('slug', 'ipe-clearing')->first();

        // Create a batch with an 8-digit integer reference
        $batch = ServiceBatch::create([
            'user_id' => $user->id,
            'service_id' => $service->id,
            'batch_reference' => '48291048',
            'total_submitted' => 2,
            'accepted_count' => 2,
            'rejected_count' => 0,
            'unit_price' => 500.00,
            'total_charged' => 1000.00,
        ]);

        $req1 = ServiceRequest::create([
            'user_id' => $user->id,
            'service_id' => $service->id,
            'batch_id' => $batch->id,
            'reference' => 'SRV_TEST1_' . time(),
            'tracking_input' => 'ABC123456789012',
            'input_payload' => ['tracking_input' => 'ABC123456789012'],
            'amount_charged' => 500.00,
            'status' => 'completed',
            'admin_notes' => 'Cleared by NIS Gate 4',
        ]);

        $req2 = ServiceRequest::create([
            'user_id' => $user->id,
            'service_id' => $service->id,
            'batch_id' => $batch->id,
            'reference' => 'SRV_TEST2_' . time(),
            'tracking_input' => 'XYZ987654321098',
            'input_payload' => ['tracking_input' => 'XYZ987654321098'],
            'amount_charged' => 500.00,
            'status' => 'pending',
        ]);

        // 1. Visit IPE Clearing service page: verify 8-digit batch number and inspector modal are present in view
        $response = $this->actingAs($user)->get('/services/ipe-clearing');
        $response->assertStatus(200);
        $response->assertSee('48291048');
        $response->assertSee('openBatchInspector');
        $response->assertSee('batch_inspector_modal');
        $response->assertSee('batch_report_textarea');

        // 2. Fetch Batch Details API endpoint
        $apiResponse = $this->actingAs($user)->getJson('/services/batch/48291048');
        $apiResponse->assertStatus(200);
        $apiResponse->assertJsonPath('batch_number', '48291048');
        $apiResponse->assertJsonPath('total', 2);
        $apiResponse->assertJsonPath('completed', 1);
        $apiResponse->assertJsonPath('pending', 1);

        $data = $apiResponse->json();
        $this->assertArrayHasKey('report_text', $data);
        $this->assertStringContainsString('ABC123456789012=Cleared by NIS Gate 4', $data['report_text']);
        $this->assertStringContainsString('XYZ987654321098=Pending', $data['report_text']);
        $this->assertStringContainsString("Summary:\nTotal Records: 2\nTotal Successful: 1\nTotal Pending: 1\nTotal Failed: 0", $data['report_text']);

        // 3. Test fully completed batch matching user format:
        $req2->update(['status' => 'completed', 'admin_notes' => 'H94WJM62L0003VB']);
        $completedApiResponse = $this->actingAs($user)->getJson('/services/batch/48291048');
        $completedData = $completedApiResponse->json();
        $expectedReport = "ABC123456789012=Cleared by NIS Gate 4\nXYZ987654321098=H94WJM62L0003VB\n\nSummary:\nTotal Records: 2\nTotal Successful: 2\nTotal Failed: 0";
        $this->assertEquals($expectedReport, $completedData['report_text']);
    }

    public function test_user_dashboard_does_not_display_virtual_accounts_card_which_resides_on_wallet_page(): void
    {
        $user = User::where('email', 'user@vsite.ng')->first();

        // 1. Dashboard should NOT show virtual accounts funding card
        $dashboardResponse = $this->actingAs($user)->get('/dashboard');
        $dashboardResponse->assertStatus(200);
        $dashboardResponse->assertDontSee('Automated Bank Transfer Funding');
        $dashboardResponse->assertDontSee('Monnify Static Account');

        // 2. Dedicated Wallet Funding page (/wallet) should show virtual accounts
        $walletResponse = $this->actingAs($user)->get('/wallet');
        $walletResponse->assertStatus(200);
        $walletResponse->assertSee('Automated Bank Transfer Funding');
        $walletResponse->assertSee('Instant Credit');
        $walletResponse->assertSee('Moniepoint MFB');
        $walletResponse->assertSee('Sterling Bank');
        $walletResponse->assertSee('Wema Bank');
        $walletResponse->assertSee('images/banks/moniepoint.png');
        $walletResponse->assertSee('images/banks/sterling.png');
        $walletResponse->assertSee('images/banks/wema.png');

        // Virtual Account Details
        $accounts = $user->getMonnifyVirtualAccounts();
        $this->assertCount(3, $accounts);

        foreach ($accounts as $acc) {
            $this->assertEquals(10, strlen($acc['account_number']));
            $walletResponse->assertSee($acc['account_number']);
            $walletResponse->assertSee($acc['account_name']);
        }

        // Account Name format
        $walletResponse->assertSee('VSITE / AMINA DANJUMA');

        // Verify reverse resolution by virtual account
        $moniepointAcc = $accounts[0]['account_number'];
        $resolvedUser = User::findByVirtualAccount($moniepointAcc);
        $this->assertNotNull($resolvedUser);
        $this->assertEquals($user->id, $resolvedUser->id);
    }

    public function test_dashboard_fund_wallet_triggers_modal_popup(): void
    {
        $user = User::where('email', 'user@vsite.ng')->first();

        $response = $this->actingAs($user)->get('/dashboard');
        $response->assertStatus(200);

        // Assert trigger buttons on dashboard
        $response->assertSee('id="fund-wallet-icon-btn"', false);
        $response->assertSee('openFundWalletModal()', false);
        $response->assertSee('+ Fund Your Wallet');

        // Assert modal elements and design structure
        $response->assertSee('id="fundWalletModal"', false);
        $response->assertSee('Fund Wallet');
        $response->assertSee('closeFundWalletModal()', false);
        $response->assertSee('Fund your wallet instantly by depositing into the virtual account number');

        // Assert all 3 virtual banks rendered with logos and copy buttons
        $response->assertSee('images/banks/sterling.png');
        $response->assertSee('images/banks/wema.png');
        $response->assertSee('images/banks/moniepoint.png');
        $response->assertSee('Sterling bank');
        $response->assertSee('Wema bank');
        $response->assertSee('Moniepoint Microfinance Bank');
        $response->assertSee('copyModalAccount');

        // Assert user's virtual accounts are rendered inside modal
        $accounts = $user->getMonnifyVirtualAccounts();
        foreach ($accounts as $acc) {
            $response->assertSee($acc['account_number']);
            $response->assertSee($acc['account_name']);
        }

        // Assert footer support note and 'Go to wallet' link
        $response->assertSee('If your funds is not received within 30mins. Please');
        $response->assertSee('Contact Support');
        $response->assertSee('Go to wallet');
        $response->assertSee(route('wallet.index'));
    }
}

