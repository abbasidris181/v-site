<?php

namespace Tests\Feature;

use App\Models\Service;
use App\Models\ServiceRequest;
use App\Models\User;
use App\Models\Wallet;
use Database\Seeders\AnnouncementSeeder;
use Database\Seeders\RolePermissionSeeder;
use Database\Seeders\ServiceCatalogSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Config;
use Illuminate\Support\Facades\Http;
use Tests\TestCase;

class VerifyUserNinVerificationTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        $this->seed(RolePermissionSeeder::class);
        $this->seed(ServiceCatalogSeeder::class);
        $this->seed(AnnouncementSeeder::class);

        Config::set('services.verify_user.base_url', 'https://unitybills.com');
        Config::set('services.verify_user.token', 'test_bearer_token_12345');
    }

    protected function sampleSuccessfulProviderResponse(): array
    {
        return [
            'status' => true,
            'message' => [
                'firstname' => 'SANI',
                'middlename' => 'ADAMU',
                'surname' => 'IDIS',
                'telephoneno' => '08068803520',
                'birthdate' => '2000-12-19',
                'residence_state' => 'Sokoto',
                'residence_town' => 'KANO',
                'residence_AdressLine1' => 'OPPOSITE KANO HOTE',
                'residence_lga' => 'Kano',
                'birthcountry' => '',
                'birthstate' => null,
                'birthlga' => null,
                'country' => 'NG',
                'gender' => 'Male',
                'image' => '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',
                'signature' => null,
                'nin' => '84658575957',
                'trackingId' => 'TRK99887766',
                'heigth' => '175',
                'maritalstatus' => 'Single',
                'nok_address1' => 'Plot 12 Kano Express',
                'nok_firstname' => 'Musa',
                'nok_lga' => 'Kano',
                'nok_surname' => 'Idis',
                'nok_town' => 'Kano',
                'nspokenlang' => 'Hausa, English',
                'pmiddlename' => null,
                'profession' => 'Civil Servant',
                'religion' => 'Islam',
                'residencestatus' => 'Resident',
                'self_origin_lga' => 'Sokoto North',
                'self_origin_place' => 'Sokoto City',
                'self_origin_state' => 'Sokoto',
            ],
            'transID' => '075657-6ab0d539d2308',
        ];
    }

    public function test_user_can_verify_nin_via_verify_user_provider_with_wallet_charge(): void
    {
        $user = User::where('email', 'user@vsite.ng')->first();
        $service = Service::where('slug', 'nin-verification')->first();
        $wallet = Wallet::where('user_id', $user->id)->first();
        $initialBalance = (float) $wallet->balance; // 8000.00

        Http::fake([
            'https://unitybills.com/api/nin/index.php' => Http::response($this->sampleSuccessfulProviderResponse(), 200),
        ]);

        $response = $this->actingAs($user)->post('/services/nin-verification', [
            'tracking_input' => '84658575957',
            'consent' => 1,
        ]);

        $request = ServiceRequest::where('user_id', $user->id)
            ->where('service_id', $service->id)
            ->latest()
            ->first();

        $this->assertNotNull($request);
        $response->assertStatus(302);
        $response->assertRedirect(route('services.slip', $request->reference));
        $response->assertSessionHas('success');

        // Verify wallet debit
        $wallet->refresh();
        $this->assertEquals($initialBalance - 500.00, (float) $wallet->balance);

        // Verify Service Request Persistence
        $this->assertEquals('completed', $request->status);
        $this->assertEquals('84658575957', $request->tracking_input);
        $this->assertEquals(500.00, (float) $request->amount_charged);

        // Verify normalized data in result payload
        $result = $request->result_payload;
        $this->assertTrue($result['is_successful']);
        $data = $result['data'];

        $this->assertEquals('SANI ADAMU IDIS', $data['full_name']);
        $this->assertEquals('SANI', $data['first_name']);
        $this->assertEquals('ADAMU', $data['middle_name']);
        $this->assertEquals('IDIS', $data['surname']);
        $this->assertEquals('84658575957', $data['nin']);
        $this->assertEquals('8465 857 5957', $data['nin_formatted']);
        $this->assertEquals('08068803520', $data['phone_number']);
        $this->assertEquals('2000-12-19', $data['date_of_birth']);
        $this->assertEquals('Male', $data['gender']);
        $this->assertStringStartsWith('data:image/jpeg;base64,', $data['photo_base64']);
        $this->assertEquals('075657-6ab0d539d2308', $data['trans_id']);
        $this->assertEquals('TRK99887766', $data['tracking_id']);
        $this->assertEquals('Sokoto', $data['state_of_origin']);
        $this->assertEquals('Sokoto North', $data['lga_of_origin']);
        $this->assertStringContainsString('OPPOSITE KANO HOTE', $data['residence_address']);
        $this->assertEquals('Civil Servant', $data['profession']);
        $this->assertEquals('Islam', $data['religion']);
        $this->assertEquals('Musa Idis', $data['nok_name']);
    }

    public function test_front_end_view_renders_all_returned_identity_particulars(): void
    {
        $user = User::where('email', 'user@vsite.ng')->first();

        Http::fake([
            'https://unitybills.com/api/nin/index.php' => Http::response($this->sampleSuccessfulProviderResponse(), 200),
        ]);

        $submission = $this->actingAs($user)->post('/services/nin-verification', [
            'tracking_input' => '84658575957',
            'consent' => 1,
        ]);

        $request = ServiceRequest::where('user_id', $user->id)->latest()->first();

        $submission->assertStatus(302);
        $submission->assertRedirect(route('services.slip', $request->reference));

        // Follow redirect to GET /services/slip/{reference}
        $pageResponse = $this->actingAs($user)->get(route('services.slip', $request->reference));

        $pageResponse->assertStatus(200);
        $pageResponse->assertSee('Personal Informations');
        $pageResponse->assertSee('SANI ADAMU IDIS');
        $pageResponse->assertSee('8465 857 5957');
        $pageResponse->assertSee('08068803520');
        $pageResponse->assertSee('2000-12-19');
        $pageResponse->assertSee('Male');
        $pageResponse->assertSee('Sokoto');
        $pageResponse->assertSee('Civil Servant');
        $pageResponse->assertSee('Islam');
        $pageResponse->assertSee('Available Slips for Download');
        $pageResponse->assertSee('Standard NIMC Slip');
        $pageResponse->assertSee('Premium NIN Card');
        $pageResponse->assertSee('Compact Slip');
    }

    public function test_provider_failure_triggers_automatic_refund_and_flashes_error(): void
    {
        $user = User::where('email', 'user@vsite.ng')->first();
        $wallet = Wallet::where('user_id', $user->id)->first();
        $initialBalance = (float) $wallet->balance;

        Http::fake([
            'https://unitybills.com/api/nin/index.php' => Http::response([
                'status' => false,
                'message' => 'NIN not found in national database or record revoked',
            ], 200),
        ]);

        $response = $this->actingAs($user)->post('/services/nin-verification', [
            'tracking_input' => '84658575957',
            'consent' => 1,
        ]);

        $response->assertStatus(302);
        $response->assertSessionHas('error');
        $this->assertStringContainsString('NIN not found in national database', session('error'));

        // Wallet should be restored to initial balance (charge refunded immediately)
        $wallet->refresh();
        $this->assertEquals($initialBalance, (float) $wallet->balance);

        // No completed request created
        $this->assertDatabaseMissing('service_requests', [
            'tracking_input' => '84658575957',
            'status' => 'completed',
        ]);
    }

    public function test_user_can_view_official_slip_with_verify_user_payload(): void
    {
        $user = User::where('email', 'user@vsite.ng')->first();

        Http::fake([
            'https://unitybills.com/api/nin/index.php' => Http::response($this->sampleSuccessfulProviderResponse(), 200),
        ]);

        $this->actingAs($user)->post('/services/nin-verification', [
            'tracking_input' => '84658575957',
            'consent' => 1,
        ]);

        $request = ServiceRequest::where('user_id', $user->id)->latest()->first();

        $slipResponse = $this->actingAs($user)->get("/services/slip/{$request->reference}");

        $slipResponse->assertStatus(200);
        $slipResponse->assertSee('National Identification Number Slip (NINS)');
        $slipResponse->assertSee('8465 857 5957');
        $slipResponse->assertSee('SANI');
        $slipResponse->assertSee('ADAMU');
        $slipResponse->assertSee('IDIS');
        $slipResponse->assertSee('2000-12-19');
        $slipResponse->assertSee('08068803520');
        $slipResponse->assertSee('OPPOSITE KANO HOTE');
        $slipResponse->assertSee('images/card_and_Slip/basic.jpg');
        $slipResponse->assertSee('id="slip-compact"', false);
    }

    public function test_user_can_verify_nin_with_formatted_input_containing_spaces_or_dashes(): void
    {
        $user = User::where('email', 'user@vsite.ng')->first();

        Http::fake([
            'https://unitybills.com/api/nin/index.php' => Http::response($this->sampleSuccessfulProviderResponse(), 200),
        ]);

        // Submit formatted NIN with spaces
        $response = $this->actingAs($user)->post('/services/nin-verification', [
            'nin' => '8465 857 5957',
            'consent' => 1,
        ]);

        $request = ServiceRequest::where('user_id', $user->id)->latest()->first();

        $response->assertStatus(302);
        $response->assertRedirect(route('services.slip', $request->reference));
        $response->assertSessionHas('success');

        $this->assertDatabaseHas('service_requests', [
            'user_id' => $user->id,
            'tracking_input' => '84658575957',
            'status' => 'completed',
        ]);
    }

    public function test_history_table_renders_view_slip_button_and_recent_record_banner_is_omitted(): void
    {
        $user = User::where('email', 'user@vsite.ng')->first();

        Http::fake([
            'https://unitybills.com/api/nin/index.php' => Http::response($this->sampleSuccessfulProviderResponse(), 200),
        ]);

        $this->actingAs($user)->post('/services/nin-verification', [
            'tracking_input' => '84658575957',
            'consent' => 1,
        ]);

        $request = ServiceRequest::where('user_id', $user->id)->latest()->first();

        // Visiting /services/nin-verification with api_result session renders verified result banner with link to slip
        $response = $this->actingAs($user)->get('/services/nin-verification');
        $response->assertStatus(200);
        $response->assertSee('Verified Identity Record');
        $response->assertSee('SANI ADAMU IDIS');
        $response->assertSee(route('services.slip', $request->reference));

        // 2. Clear session to simulate a returning user on a later day: recent banner should not be present
        $this->flushSession();
        $freshResponse = $this->actingAs($user)->get('/services/nin-verification');
        $freshResponse->assertStatus(200);
        $freshResponse->assertDontSee('Most Recent Verified Record');
        $freshResponse->assertDontSee('View & Print Slip');
        $freshResponse->assertSee(route('services.slip', $request->reference));
    }

    public function test_tracking_id_is_null_and_omitted_from_verification_page_and_slips_when_not_provided_by_provider(): void
    {
        $user = User::where('email', 'user@vsite.ng')->first();

        $responsePayloadWithoutTrackingId = $this->sampleSuccessfulProviderResponse();
        unset($responsePayloadWithoutTrackingId['message']['trackingId']);

        Http::fake([
            'https://unitybills.com/api/nin/index.php' => Http::response($responsePayloadWithoutTrackingId, 200),
        ]);

        $postResponse = $this->actingAs($user)->post('/services/nin-verification', [
            'tracking_input' => '84658575957',
            'consent' => 1,
        ]);

        $request = ServiceRequest::where('user_id', $user->id)->latest()->first();

        // 1. In result payload, tracking_id must be null (no synthetic fallback generated from transId or reference)
        $this->assertNull($request->result_payload['data']['tracking_id']);

        // 2. On the verification page (rendered with session api_result), Tracking ID must not appear
        $verificationPage = $this->actingAs($user)->get('/services/nin-verification');
        $verificationPage->assertStatus(200);
        $verificationPage->assertDontSee('Tracking ID:');
        $verificationPage->assertDontSee('TRK-');
        $verificationPage->assertDontSee('TRK_');

        // 3. On the slip page, no synthetic tracking ID should appear
        $slipPage = $this->actingAs($user)->get(route('services.slip', $request->reference));
        $slipPage->assertStatus(200);
        $slipPage->assertDontSee('TRK_');
        $slipPage->assertDontSee('TRK-');
    }

    public function test_tracking_id_from_provider_response_is_displayed_on_verification_page_and_slips(): void
    {
        $user = User::where('email', 'user@vsite.ng')->first();

        $responsePayloadWithTrackingId = $this->sampleSuccessfulProviderResponse();
        $responsePayloadWithTrackingId['message']['trackingId'] = 'NIMC-REAL-TRK-12345';

        Http::fake([
            'https://unitybills.com/api/nin/index.php' => Http::response($responsePayloadWithTrackingId, 200),
        ]);

        $postResponse = $this->actingAs($user)->post('/services/nin-verification', [
            'tracking_input' => '84658575957',
            'consent' => 1,
        ]);

        $request = ServiceRequest::where('user_id', $user->id)->latest()->first();

        // 1. Result payload data has real provider tracking ID
        $this->assertEquals('NIMC-REAL-TRK-12345', $request->result_payload['data']['tracking_id']);

        // 2. Verification page displays the real tracking ID
        $verificationPage = $this->actingAs($user)->get('/services/nin-verification');
        $verificationPage->assertStatus(200);
        $verificationPage->assertSee('Tracking ID:');
        $verificationPage->assertSee('NIMC-REAL-TRK-12345');

        // 3. Slip page displays the real tracking ID
        $slipPage = $this->actingAs($user)->get(route('services.slip', $request->reference));
        $slipPage->assertStatus(200);
        $slipPage->assertSee('NIMC-REAL-TRK-12345');
    }

    public function test_middle_name_is_left_blank_when_response_did_not_return_a_string(): void
    {
        $user = User::where('email', 'user@vsite.ng')->first();

        $responseWithoutMiddleName = $this->sampleSuccessfulProviderResponse();
        $responseWithoutMiddleName['message']['middlename'] = null; // Provider returned null, not a string

        Http::fake([
            'https://unitybills.com/api/nin/index.php' => Http::response($responseWithoutMiddleName, 200),
        ]);

        $postResponse = $this->actingAs($user)->post('/services/nin-verification', [
            'tracking_input' => '84658575957',
            'consent' => 1,
        ]);

        $request = ServiceRequest::where('user_id', $user->id)->latest()->first();

        // 1. Slip page renders successfully
        $slipPage = $this->actingAs($user)->get(route('services.slip', $request->reference));
        $slipPage->assertStatus(200);

        // 2. First and Surname are rendered
        $slipPage->assertSee('SANI');
        $slipPage->assertSee('IDIS');

        // 3. Full name is constructed without extra middle name space or placeholder
        $slipPage->assertSee('SANI IDIS');

        // 4. Middle name value is blank in the HTML
        $content = $slipPage->getContent();
        // In #slip-standard, Middle Name Value container should be empty (no '—' or dummy text)
        $this->assertMatchesRegularExpression('/<!-- Middle Name Value -->\s*<div[^>]*>\s*<\/div>/', $content);
    }

    public function test_middle_name_handles_asterisks_and_is_left_blank(): void
    {
        $user = User::where('email', 'user@vsite.ng')->first();

        $responseWithAsterisks = $this->sampleSuccessfulProviderResponse();
        $responseWithAsterisks['message']['middlename'] = '***'; // Provider returned asterisks
        $responseWithAsterisks['message']['pmiddlename'] = '***';

        Http::fake([
            'https://unitybills.com/api/nin/index.php' => Http::response($responseWithAsterisks, 200),
        ]);

        $postResponse = $this->actingAs($user)->post('/services/nin-verification', [
            'tracking_input' => '84658575957',
            'consent' => 1,
        ]);

        $request = ServiceRequest::where('user_id', $user->id)->latest()->first();

        // Check normalized driver payload
        $this->assertNull($request->result_payload['data']['middle_name']);
        $this->assertEquals('SANI IDIS', $request->result_payload['data']['full_name']);

        // Check show / verification page
        $showPage = $this->actingAs($user)->get(route('services.show', 'nin-verification'));
        $showPage->assertStatus(200);
        $showPage->assertSee('SANI IDIS');
        $showPage->assertDontSee('SANI *** IDIS');

        // Check slip page
        $slipPage = $this->actingAs($user)->get(route('services.slip', $request->reference));
        $slipPage->assertStatus(200);

        // First and Surname are rendered
        $slipPage->assertSee('SANI');
        $slipPage->assertSee('IDIS');
        $slipPage->assertSee('SANI IDIS');
        $slipPage->assertDontSee('SANI *** IDIS');

        $content = $slipPage->getContent();
        // In #slip-standard, Middle Name Value container should be empty
        $this->assertMatchesRegularExpression('/<!-- Middle Name Value -->\s*<div[^>]*>\s*<\/div>/', $content);
        // Previous middle name should fallback to None instead of ***
        $this->assertStringNotContainsString('***', $content);
    }
}

