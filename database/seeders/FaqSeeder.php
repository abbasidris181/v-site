<?php

namespace Database\Seeders;

use App\Models\Faq;
use Illuminate\Database\Seeder;

class FaqSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $faqs = [
            [
                'question' => 'How does Instant API Verification work?',
                'answer' => 'NIN and BVN verifications connect directly to verified upstream identity engines. Once your wallet is charged ₦500, results and official printable verification slips are generated in real-time.',
                'category' => 'Verification Services',
                'sort_order' => 1,
                'is_active' => true,
            ],
            [
                'question' => 'What is Partial-Batch Wallet Processing?',
                'answer' => 'When submitting bulk tracking IDs (e.g., 20 IPE entries at ₦500 each = ₦10,000) with only ₦8,000 in your wallet, the system automatically processes the 16 entries you can afford, charges exactly ₦8,000, and reports the 4 unfunded entries without failing your entire submission.',
                'category' => 'Wallet & Billing',
                'sort_order' => 2,
                'is_active' => true,
            ],
            [
                'question' => 'How do I access and print my official verification slips?',
                'answer' => 'You can click the "Slip →" button next to any completed verification record in your service history table or dashboard history to view and print official NIMC and NIBSS slips.',
                'category' => 'Slips & Results',
                'sort_order' => 3,
                'is_active' => true,
            ],
        ];

        foreach ($faqs as $faqData) {
            Faq::updateOrCreate(
                ['question' => $faqData['question']],
                $faqData
            );
        }
    }
}
