<?php

namespace Tests\Feature;

use App\Models\Service;
use App\Models\ServiceCategory;
use App\Models\ServiceRequest;
use App\Models\User;
use App\Services\ServiceCatalogService;
use Database\Seeders\RolePermissionSeeder;
use Database\Seeders\ServiceCatalogSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ServiceCatalogTest extends TestCase
{
    use RefreshDatabase;

    protected ServiceCatalogService $catalogService;

    protected function setUp(): void
    {
        parent::setUp();
        $this->seed(RolePermissionSeeder::class);
        $this->seed(ServiceCatalogSeeder::class);
        $this->catalogService = app(ServiceCatalogService::class);
    }

    public function test_service_catalog_is_seeded_with_expected_categories(): void
    {
        $this->assertDatabaseHas('service_categories', ['slug' => 'identity-verification']);
        $this->assertDatabaseHas('service_categories', ['slug' => 'enrollment-clearing']);
        $this->assertDatabaseHas('service_categories', ['slug' => 'account-regularization']);

        $this->assertEquals(3, ServiceCategory::count());
    }

    public function test_all_initial_services_exist_with_accurate_specifications(): void
    {
        $services = [
            'nin-verification'   => ['type' => 'api', 'price' => 500.00, 'bulk' => false],
            'nin-verification-2' => ['type' => 'api', 'price' => 500.00, 'bulk' => false],
            'nin-verification-3' => ['type' => 'api', 'price' => 500.00, 'bulk' => false],
            'bvn-verification'   => ['type' => 'api', 'price' => 500.00, 'bulk' => false],
            'ipe-clearing'     => ['type' => 'manual', 'price' => 500.00, 'bulk' => true],
            'nin-validation'   => ['type' => 'manual', 'price' => 500.00, 'bulk' => true],
            'modification-ipe' => ['type' => 'manual', 'price' => 500.00, 'bulk' => true],
            'bvn-retrieval'    => ['type' => 'manual', 'price' => 1000.00, 'bulk' => false],
            'self-service-delinking' => ['type' => 'manual', 'price' => 1000.00, 'bulk' => false],
            'personalization'  => ['type' => 'manual', 'price' => 500.00, 'bulk' => false],
        ];

        foreach ($services as $slug => $meta) {
            $service = Service::where('slug', $slug)->first();
            $this->assertNotNull($service, "Service {$slug} should be seeded");
            $this->assertEquals($meta['type'], $service->type);
            $this->assertEquals($meta['price'], (float) $service->price);
            $this->assertEquals($meta['bulk'], $service->is_bulk_allowed);
        }
    }

    public function test_catalog_service_retrieves_active_catalog_grouped_by_category(): void
    {
        $catalog = $this->catalogService->getActiveCatalog();

        $this->assertCount(3, $catalog);
        $this->assertTrue($catalog->first()->relationLoaded('activeServices'));

        $identityCat = $catalog->firstWhere('slug', 'identity-verification');
        $this->assertCount(4, $identityCat->activeServices);

        $clearingCat = $catalog->firstWhere('slug', 'enrollment-clearing');
        $this->assertCount(4, $clearingCat->activeServices);
    }

    public function test_service_price_formatting_accessor(): void
    {
        $service = Service::where('slug', 'ipe-clearing')->first();
        $this->assertEquals('₦500.00', $service->formatted_price);

        $retrieval = Service::where('slug', 'bvn-retrieval')->first();
        $this->assertEquals('₦1,000.00', $retrieval->formatted_price);
    }

    public function test_bvn_retrieval_custom_fields_schema(): void
    {
        $service = Service::where('slug', 'bvn-retrieval')->first();
        $this->assertIsArray($service->fields_schema);
        $this->assertCount(2, $service->fields_schema);

        $names = collect($service->fields_schema)->pluck('name')->toArray();
        $this->assertContains('full_name', $names);
        $this->assertContains('phone_number', $names);
    }

    public function test_service_request_lifecycle_and_helpers(): void
    {
        $user = User::where('email', 'user@vsite.ng')->first();
        $service = Service::where('slug', 'ipe-clearing')->first();

        $request = ServiceRequest::create([
            'user_id' => $user->id,
            'service_id' => $service->id,
            'reference' => 'SRV_TEST_123',
            'tracking_input' => 'ABCDE12345FGHIJ',
            'input_payload' => ['tracking_id' => 'ABCDE12345FGHIJ'],
            'amount_charged' => 500.00,
            'status' => 'pending',
        ]);

        $this->assertTrue($request->isPending());
        $this->assertFalse($request->isCompleted());
        $this->assertFalse($request->isRefunded());
        $this->assertEquals('₦500.00', $request->formatted_amount);
    }

    public function test_service_request_reference_generation_format_and_sequence(): void
    {
        $user = User::where('email', 'user@vsite.ng')->first();
        $service = Service::where('slug', 'ipe-clearing')->first();

        // 1. Explicit sequence number (e.g. 405 -> REF-00000405-XXXX)
        $ref405 = ServiceRequest::generateReference(405);
        $this->assertMatchesRegularExpression('/^REF-00000405-[A-Z]{4}$/', $ref405);

        // 2. Automatic sequence generation
        $autoRef = ServiceRequest::generateReference();
        $this->assertMatchesRegularExpression('/^REF-\d{8}-[A-Z]{4}$/', $autoRef);

        // 3. Model booted creating hook populates reference if omitted
        $created = ServiceRequest::create([
            'user_id' => $user->id,
            'service_id' => $service->id,
            'tracking_input' => 'ABCDE12345FGHIJ',
            'input_payload' => ['tracking_id' => 'ABCDE12345FGHIJ'],
            'amount_charged' => 500.00,
            'status' => 'pending',
        ]);

        $this->assertNotEmpty($created->reference);
        $this->assertMatchesRegularExpression('/^REF-\d{8}-[A-Z]{4}$/', $created->reference);
    }
}

