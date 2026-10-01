<?php

namespace Tests\Feature;

use App\Models\Role;
use App\Models\Service;
use App\Models\ServiceBatch;
use App\Models\ServiceCategory;
use App\Models\ServiceRequest;
use App\Models\User;
use App\Services\WalletService;
use Database\Seeders\RolePermissionSeeder;
use Database\Seeders\ServiceCatalogSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class BulkSubmissionTest extends TestCase
{
    use RefreshDatabase;

    protected User $user;
    protected WalletService $walletService;

    protected function setUp(): void
    {
        parent::setUp();

        $this->seed(RolePermissionSeeder::class);
        $this->seed(ServiceCatalogSeeder::class);

        $this->walletService = app(WalletService::class);

        $endUserRole = Role::where('slug', 'end_user')->first();

        $this->user = User::factory()->create([
            'email_verified_at' => now(),
            'phone_verified_at' => now(),
        ]);
        $this->user->roles()->attach($endUserRole);
    }

    public function test_srs_section_14_partial_batch_wallet_processing_rule(): void
    {
        // 1. Setup: User has ₦8,000 wallet balance
        $this->walletService->deposit($this->user, 8000.00, 'Initial Funding');

        $service = Service::where('slug', 'ipe-clearing')->firstOrFail();
        $this->assertEquals(500.00, (float) $service->price);
        $this->assertTrue((bool) $service->is_bulk_allowed);

        // 2. Generate 20 distinct valid 15-char IPE tracking IDs
        $entries = [];
        for ($i = 1; $i <= 20; $i++) {
            $entries[] = sprintf('IPE%012d', $i);
        }
        $rawInput = implode("\n", $entries);

        // 3. Post bulk submission
        $response = $this->actingAs($this->user)
            ->from(route('services.show', $service->slug))
            ->post(route('services.submit', $service->slug), [
                'tracking_input' => $rawInput,
            ]);

        $response->assertRedirect(route('services.show', $service->slug));
        $response->assertSessionHas('success');
        $response->assertSessionHas('bulk_result');

        $bulkResult = session('bulk_result');
        $this->assertCount(16, $bulkResult['accepted_entries']);
        $this->assertCount(4, $bulkResult['insufficient_balance_entries']);
        $this->assertCount(0, $bulkResult['invalid_entries']);
        $this->assertEquals(8000.00, $bulkResult['total_charged']);

        // 4. Verify Wallet balance deducted to ₦0.00
        $wallet = $this->walletService->getOrCreateWallet($this->user);
        $this->assertEquals(0.00, (float) $wallet->balance);

        // 5. Verify ServiceBatch created
        $batch = ServiceBatch::where('user_id', $this->user->id)->first();
        $this->assertNotNull($batch);
        $this->assertEquals(20, $batch->total_submitted);
        $this->assertEquals(16, $batch->accepted_count);
        $this->assertEquals(4, $batch->rejected_count);
        $this->assertEquals(8000.00, (float) $batch->total_charged);

        // 6. Verify exactly 16 individual trackable ServiceRequests created with pending status
        $requests = ServiceRequest::where('batch_id', $batch->id)->get();
        $this->assertCount(16, $requests);

        foreach ($requests as $index => $req) {
            $this->assertEquals('pending', $req->status);
            $this->assertEquals(500.00, (float) $req->amount_charged);
            $this->assertEquals($entries[$index], $req->tracking_input);
            $this->assertEquals($service->id, $req->service_id);
            $this->assertEquals($this->user->id, $req->user_id);
        }
    }

    public function test_ipe_tracking_input_is_normalized_to_uppercase_and_trimmed(): void
    {
        $this->walletService->deposit($this->user, 1000.00);

        $service = Service::where('slug', 'ipe-clearing')->firstOrFail();

        // Mixed case and spaces
        $rawInput = "  ipe1234567890ab  \n   ipe9999999999cd  ";

        $response = $this->actingAs($this->user)
            ->post(route('services.submit', $service->slug), [
                'tracking_input' => $rawInput,
            ]);

        $response->assertSessionHas('success');

        $requests = ServiceRequest::where('user_id', $this->user->id)->get();
        $this->assertCount(2, $requests);
        $this->assertEquals('IPE1234567890AB', $requests[0]->tracking_input);
        $this->assertEquals('IPE9999999999CD', $requests[1]->tracking_input);
    }

    public function test_invalid_format_entries_are_rejected_and_not_charged(): void
    {
        $this->walletService->deposit($this->user, 2000.00);

        $service = Service::where('slug', 'ipe-clearing')->firstOrFail();

        // 1 valid (15 chars), 2 invalid (too short, contains symbol)
        $rawInput = "VALIDIPE1234567\nTOO_SHORT\nINVALID!@#$1234";

        $response = $this->actingAs($this->user)
            ->post(route('services.submit', $service->slug), [
                'tracking_input' => $rawInput,
            ]);

        $response->assertSessionHas('bulk_result');
        $result = session('bulk_result');

        $this->assertCount(1, $result['accepted_entries']);
        $this->assertCount(2, $result['invalid_entries']);
        $this->assertEquals(500.00, $result['total_charged']);

        // Remaining balance: 2000 - 500 = 1500
        $wallet = $this->walletService->getOrCreateWallet($this->user);
        $this->assertEquals(1500.00, (float) $wallet->balance);
    }

    public function test_bulk_submission_halts_if_wallet_has_insufficient_funds_for_any_entry(): void
    {
        // 0 wallet balance
        $service = Service::where('slug', 'ipe-clearing')->firstOrFail();

        $response = $this->actingAs($this->user)
            ->from(route('services.show', $service->slug))
            ->post(route('services.submit', $service->slug), [
                'tracking_input' => "IPE1234567890AB\nIPE9999999999CD",
            ]);

        $response->assertRedirect(route('services.show', $service->slug));
        $response->assertSessionHas('error');

        $this->assertEquals(0, ServiceBatch::count());
        $this->assertEquals(0, ServiceRequest::count());
    }

    public function test_multi_field_single_submission_for_bvn_retrieval(): void
    {
        $this->walletService->deposit($this->user, 1500.00);

        $service = Service::where('slug', 'bvn-retrieval')->firstOrFail();
        $this->assertEquals(1000.00, (float) $service->price);
        $this->assertNotNull($service->fields_schema);

        $payload = [
            'phone_number' => '08012345678',
            'full_name' => 'Chioma Adebayo',
        ];

        $response = $this->actingAs($this->user)
            ->post(route('services.submit', $service->slug), $payload);

        $response->assertSessionHas('success');

        $request = ServiceRequest::where('user_id', $this->user->id)
            ->where('service_id', $service->id)
            ->first();

        $this->assertNotNull($request);
        $this->assertEquals('08012345678', $request->tracking_input);
        $this->assertEquals('pending', $request->status);
        $this->assertEquals(1000.00, (float) $request->amount_charged);
        $this->assertEquals($payload, $request->input_payload);

        // Wallet balance reduced from 1500 to 500
        $wallet = $this->walletService->getOrCreateWallet($this->user);
        $this->assertEquals(500.00, (float) $wallet->balance);
    }

    public function test_single_manual_submission_for_delinking(): void
    {
        $this->walletService->deposit($this->user, 2000.00);

        $service = Service::where('slug', 'self-service-delinking')->firstOrFail();
        $this->assertEquals(1000.00, (float) $service->price);

        $response = $this->actingAs($this->user)
            ->post(route('services.submit', $service->slug), [
                'tracking_input' => ' 1234 567 8901 ',
            ]);

        $response->assertSessionHas('success');

        $request = ServiceRequest::where('user_id', $this->user->id)
            ->where('service_id', $service->id)
            ->first();

        $this->assertNotNull($request);
        $this->assertEquals('12345678901', $request->tracking_input);
        $this->assertEquals('pending', $request->status);
        $this->assertEquals(1000.00, (float) $request->amount_charged);

        // Wallet balance: 2000 - 1000 = 1000
        $wallet = $this->walletService->getOrCreateWallet($this->user);
        $this->assertEquals(1000.00, (float) $wallet->balance);
    }

    public function test_single_submission_fails_when_wallet_has_insufficient_funds(): void
    {
        $service = Service::where('slug', 'self-service-delinking')->firstOrFail();

        $response = $this->actingAs($this->user)
            ->post(route('services.submit', $service->slug), [
                'tracking_input' => '12345678901',
            ]);

        $response->assertSessionHas('error');
        $this->assertEquals(0, ServiceRequest::count());
    }

    public function test_ipe_clearing_continuous_unspaced_input_chunks_each_15_characters_into_separate_entries(): void
    {
        $this->walletService->deposit($this->user, 3000.00);

        $service = Service::where('slug', 'ipe-clearing')->firstOrFail();

        // 3 continuous 15-character tracking IDs with NO spaces or commas (45 characters total)
        $id1 = 'ABCDE12345FGHIJ';
        $id2 = '12345ABCDE67890';
        $id3 = 'XYZ9876543210AB';
        $rawContinuous = $id1 . $id2 . $id3;

        $response = $this->actingAs($this->user)
            ->post(route('services.submit', $service->slug), [
                'tracking_input' => $rawContinuous,
            ]);

        $response->assertSessionHas('success');
        $response->assertSessionHas('bulk_result');

        $result = session('bulk_result');
        $this->assertCount(3, $result['accepted_entries']);
        $this->assertEquals(1500.00, $result['total_charged']);

        $requests = ServiceRequest::where('user_id', $this->user->id)
            ->where('service_id', $service->id)
            ->orderBy('id', 'asc')
            ->get();

        $this->assertCount(3, $requests);
        $this->assertEquals($id1, $requests[0]->tracking_input);
        $this->assertEquals($id2, $requests[1]->tracking_input);
        $this->assertEquals($id3, $requests[2]->tracking_input);
    }

    public function test_modification_ipe_continuous_unspaced_input_chunks_each_15_characters_into_separate_entries(): void
    {
        $this->walletService->deposit($this->user, 3000.00);

        $service = Service::where('slug', 'modification-ipe')->firstOrFail();

        // 2 continuous 15-character tracking IDs with NO spaces or commas (30 characters total)
        $id1 = 'MODIF1234567890';
        $id2 = 'MODIF9876543210';
        $rawContinuous = $id1 . $id2;

        $response = $this->actingAs($this->user)
            ->post(route('services.submit', $service->slug), [
                'tracking_input' => $rawContinuous,
            ]);

        $response->assertSessionHas('success');
        $result = session('bulk_result');
        $this->assertCount(2, $result['accepted_entries']);
        $this->assertEquals(1000.00, $result['total_charged']);

        $requests = ServiceRequest::where('user_id', $this->user->id)
            ->where('service_id', $service->id)
            ->orderBy('id', 'asc')
            ->get();

        $this->assertCount(2, $requests);
        $this->assertEquals($id1, $requests[0]->tracking_input);
        $this->assertEquals($id2, $requests[1]->tracking_input);
    }

    public function test_ipe_clearing_and_modification_views_render_15_char_auto_formatting(): void
    {
        foreach (['ipe-clearing', 'modification-ipe'] as $slug) {
            $response = $this->actingAs($this->user)->get(route('services.show', $slug));
            $response->assertStatus(200);
            $response->assertSee('id="bulk_entry_counter"', false);
            $response->assertSee('format15CharText', false);
            $response->assertSee('Each 15-character entry automatically occupies a new line');
        }
    }
}

