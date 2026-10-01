<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        // 1. Add last_seen_at to users table for presence tracking
        Schema::table('users', function (Blueprint $table) {
            $table->timestamp('last_seen_at')->nullable()->after('updated_at');
        });

        // 2. Add support attributes to internal_chat_rooms
        Schema::table('internal_chat_rooms', function (Blueprint $table) {
            $table->boolean('is_support')->default(false)->after('type');
            $table->foreignId('assigned_staff_id')->nullable()->after('created_by')->constrained('users')->nullOnDelete();
        });

        // 3. Make receiver_id nullable on internal_call_sessions to allow broadcast user-to-support calls
        Schema::table('internal_call_sessions', function (Blueprint $table) {
            $table->foreignId('receiver_id')->nullable()->change();
            $table->boolean('is_support_call')->default(false)->after('room_id');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('internal_call_sessions', function (Blueprint $table) {
            $table->dropColumn('is_support_call');
        });

        Schema::table('internal_chat_rooms', function (Blueprint $table) {
            $table->dropForeign(['assigned_staff_id']);
            $table->dropColumn(['is_support', 'assigned_staff_id']);
        });

        Schema::table('users', function (Blueprint $table) {
            $table->dropColumn('last_seen_at');
        });
    }
};
