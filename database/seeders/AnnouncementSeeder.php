<?php

namespace Database\Seeders;

use App\Models\Announcement;
use Illuminate\Database\Seeder;

class AnnouncementSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        Announcement::updateOrCreate(
            ['title' => 'Welcome to V-PORTAL Verification Platform'],
            [
                'content' => 'Ensure your wallet is funded before initiating bulk IPE Clearing or NIN Verification requests. Partial batch allocation will automatically optimize submissions according to your available balance.',
                'type' => 'info',
                'is_active' => true,
            ]
        );
    }
}
