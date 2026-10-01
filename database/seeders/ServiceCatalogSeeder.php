<?php

namespace Database\Seeders;

use App\Models\Service;
use App\Models\ServiceCategory;
use Illuminate\Database\Seeder;

class ServiceCatalogSeeder extends Seeder
{
    /**
     * Run the database seeds for services and categories.
     */
    public function run(): void
    {
        // 1. Categories
        $categoriesData = [
            'identity-verification' => [
                'name' => 'Identity Verification Services',
                'icon' => 'shield-check',
                'description' => 'Real-time automated biometric and demographic identity verifications.',
                'sort_order' => 1,
            ],
            'enrollment-clearing' => [
                'name' => 'Manual Clearing & Validation',
                'icon' => 'document-check',
                'description' => 'Clearinghouse manual processing for IPE tracking IDs and NIN validations.',
                'sort_order' => 2,
            ],
            'account-regularization' => [
                'name' => 'Regularization & Retrieval',
                'icon' => 'user-group',
                'description' => 'Specialized manual retrieval and identity delinking services.',
                'sort_order' => 3,
            ],
        ];

        $categories = [];
        foreach ($categoriesData as $slug => $data) {
            $categories[$slug] = ServiceCategory::updateOrCreate(
                ['slug' => $slug],
                array_merge($data, ['is_active' => true])
            );
        }

        // 2. Services
        $servicesData = [
            // A. NIN Verification (SRS Section 15)
            [
                'category_slug' => 'identity-verification',
                'name' => 'NIN Verification',
                'slug' => 'nin-verification',
                'type' => 'api',
                'price' => 500.00,
                'description' => 'Verify National Identification Numbers via search by NIN, phone number, or demographic details.',
                'input_label' => 'NIN or Phone Number',
                'input_placeholder' => 'Enter 11-digit NIN or registered 11-digit phone number',
                'validation_rule' => 'nin_search',
                'provider_driver' => 'nin_v1',
                'is_bulk_allowed' => false,
                'sort_order' => 1,
                'fields_schema' => null,
            ],

            // A2. NIN Verification 2 (Biometric Cross-Match Gateway)
            [
                'category_slug' => 'identity-verification',
                'name' => 'NIN Verification 2',
                'slug' => 'nin-verification-2',
                'type' => 'api',
                'price' => 500.00,
                'description' => 'Secondary routing channel for National Identification Number verification via Biometric Cross-Match gateway.',
                'input_label' => 'NIN or Phone Number',
                'input_placeholder' => 'Enter 11-digit NIN or registered 11-digit phone number',
                'validation_rule' => 'nin_search',
                'provider_driver' => 'nin_v2',
                'is_bulk_allowed' => false,
                'sort_order' => 2,
                'fields_schema' => null,
            ],

            // A3. NIN Verification 3 (Real-Time Digital Engine)
            [
                'category_slug' => 'identity-verification',
                'name' => 'NIN Verification 3',
                'slug' => 'nin-verification-3',
                'type' => 'api',
                'price' => 500.00,
                'description' => 'Tertiary routing channel for National Identification Number verification via Real-Time Digital Engine.',
                'input_label' => 'NIN or Phone Number',
                'input_placeholder' => 'Enter 11-digit NIN or registered 11-digit phone number',
                'validation_rule' => 'nin_search',
                'provider_driver' => 'nin_v3',
                'is_bulk_allowed' => false,
                'sort_order' => 3,
                'fields_schema' => null,
            ],

            // B. BVN Verification (SRS Section 16)
            [
                'category_slug' => 'identity-verification',
                'name' => 'BVN Verification',
                'slug' => 'bvn-verification',
                'type' => 'api',
                'price' => 500.00,
                'description' => 'Instant verification of Bank Verification Number with real-time biographical response.',
                'input_label' => 'Bank Verification Number (BVN)',
                'input_placeholder' => 'Enter 11-digit BVN',
                'validation_rule' => 'numeric_11',
                'provider_driver' => 'bvn_v1',
                'is_bulk_allowed' => false,
                'sort_order' => 2,
                'fields_schema' => null,
            ],

            // C. IPE Clearing (SRS Section 17)
            [
                'category_slug' => 'enrollment-clearing',
                'name' => 'IPE Clearing',
                'slug' => 'ipe-clearing',
                'type' => 'manual',
                'price' => 500.00,
                'description' => 'Clearing of immigration and passport enrollment tracking IDs with bulk submission support.',
                'input_label' => 'Tracking ID (15 Alphanumeric Characters)',
                'input_placeholder' => 'Paste one or multiple 15-character tracking IDs (separated by newlines)',
                'validation_rule' => 'alphanumeric_15',
                'provider_driver' => null,
                'is_bulk_allowed' => true,
                'sort_order' => 3,
                'fields_schema' => null,
            ],

            // D. NIN Validation (SRS Section 18)
            [
                'category_slug' => 'enrollment-clearing',
                'name' => 'NIN Validation',
                'slug' => 'nin-validation',
                'type' => 'manual',
                'price' => 500.00,
                'description' => 'Manual backend validation of National Identification Numbers with bulk paste support.',
                'input_label' => 'National Identification Number (NIN)',
                'input_placeholder' => 'Paste one or multiple 11-digit NINs (separated by newlines)',
                'validation_rule' => 'numeric_11',
                'provider_driver' => null,
                'is_bulk_allowed' => true,
                'sort_order' => 4,
                'fields_schema' => null,
            ],

            // E. Modification IPE (SRS Section 21)
            [
                'category_slug' => 'enrollment-clearing',
                'name' => 'Modification IPE',
                'slug' => 'modification-ipe',
                'type' => 'manual',
                'price' => 500.00,
                'description' => 'Manual processing for modified enrollment tracking IDs with partial-batch wallet deduction.',
                'input_label' => 'Modification Tracking ID (15 Characters)',
                'input_placeholder' => 'Paste 15-character alphanumeric modification tracking IDs',
                'validation_rule' => 'alphanumeric_15',
                'provider_driver' => null,
                'is_bulk_allowed' => true,
                'sort_order' => 5,
                'fields_schema' => null,
            ],

            // F. BVN Retrieval by Phone Number (SRS Section 20)
            [
                'category_slug' => 'account-regularization',
                'name' => 'BVN Retrieval by Phone Number',
                'slug' => 'bvn-retrieval',
                'type' => 'manual',
                'price' => 1000.00,
                'description' => 'Assisted manual retrieval of BVN records using the customer’s full legal name and phone number.',
                'input_label' => 'Customer Verification Details',
                'input_placeholder' => null,
                'validation_rule' => 'bvn_retrieval_fields',
                'provider_driver' => null,
                'is_bulk_allowed' => false,
                'sort_order' => 6,
                'fields_schema' => [
                    ['name' => 'full_name', 'label' => 'Customer Full Legal Name', 'type' => 'text', 'placeholder' => 'e.g. Chukwuemeka Danjuma', 'required' => true],
                    ['name' => 'phone_number', 'label' => 'Registered Phone Number', 'type' => 'tel', 'placeholder' => 'e.g. 08012345678', 'required' => true],
                ],
            ],

            // G. Self-Service Delinking (SRS Section 22)
            [
                'category_slug' => 'account-regularization',
                'name' => 'Self-Service Delinking',
                'slug' => 'self-service-delinking',
                'type' => 'manual',
                'price' => 1000.00,
                'description' => 'Manual delinking request to disassociate incorrectly linked phone numbers from identity records.',
                'input_label' => 'National Identification Number (NIN)',
                'input_placeholder' => 'Enter 11-digit NIN to delink',
                'validation_rule' => 'numeric_11',
                'provider_driver' => null,
                'is_bulk_allowed' => false,
                'sort_order' => 7,
                'fields_schema' => null,
            ],

            // H. Personalization
            [
                'category_slug' => 'enrollment-clearing',
                'name' => 'Personalization',
                'slug' => 'personalization',
                'type' => 'manual',
                'price' => 500.00,
                'description' => 'Manual processing for identity card personalization tracking numbers.',
                'input_label' => 'Personalization Number (15 Characters)',
                'input_placeholder' => 'Enter 15-character alphanumeric personalization number',
                'validation_rule' => 'alphanumeric_15',
                'provider_driver' => null,
                'is_bulk_allowed' => false,
                'sort_order' => 8,
                'fields_schema' => null,
            ],
        ];

        foreach ($servicesData as $service) {
            $categorySlug = $service['category_slug'];
            unset($service['category_slug']);

            Service::updateOrCreate(
                ['slug' => $service['slug']],
                array_merge($service, [
                    'category_id' => $categories[$categorySlug]->id,
                    'is_active' => true,
                ])
            );
        }
    }
}
