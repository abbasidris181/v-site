<?php

namespace Database\Seeders;

use App\Models\InternalChatMessage;
use App\Models\InternalChatRoom;
use App\Models\User;
use Illuminate\Database\Seeder;

class InternalChatSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $superAdmin = User::where('email', 'superadmin@vsite.ng')->first() ?? User::first();

        // 1. General Staff Channel
        $generalRoom = InternalChatRoom::firstOrCreate(
            ['name' => 'Staff General', 'type' => 'channel'],
            ['created_by' => $superAdmin?->id]
        );

        // 2. Operations & Clearing Channel
        $opsRoom = InternalChatRoom::firstOrCreate(
            ['name' => 'Operations Desk', 'type' => 'channel'],
            ['created_by' => $superAdmin?->id]
        );

        // Attach all staff and admins to both channels
        $staffUsers = User::whereHas('roles', function ($q) {
            $q->whereIn('slug', ['super_admin', 'admin', 'staff']);
        })->get();

        foreach ([$generalRoom, $opsRoom] as $room) {
            foreach ($staffUsers as $staff) {
                $room->users()->syncWithoutDetaching([
                    $staff->id => ['last_read_at' => now()],
                ]);
            }
        }

        // Add initial system message if empty
        if ($generalRoom->messages()->count() === 0 && $superAdmin) {
            InternalChatMessage::create([
                'room_id' => $generalRoom->id,
                'user_id' => $superAdmin->id,
                'message' => 'Welcome to the Internal Communications Hub! Staff and administrators can chat in real-time, coordinate operations, and place direct internal voice calls.',
            ]);
        }

        if ($opsRoom->messages()->count() === 0 && $superAdmin) {
            InternalChatMessage::create([
                'room_id' => $opsRoom->id,
                'user_id' => $superAdmin->id,
                'message' => 'Operations & Clearing Channel: Use this channel for immediate escalation on IPE Clearing, NIN validations, and manual queues.',
            ]);
        }
    }
}
