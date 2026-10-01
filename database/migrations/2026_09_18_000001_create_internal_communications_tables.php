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
        Schema::create('internal_chat_rooms', function (Blueprint $table) {
            $table->id();
            $table->string('name')->nullable();
            $table->string('type')->default('channel'); // channel, direct
            $table->foreignId('created_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamps();
        });

        Schema::create('internal_chat_room_user', function (Blueprint $table) {
            $table->id();
            $table->foreignId('room_id')->constrained('internal_chat_rooms')->cascadeOnDelete();
            $table->foreignId('user_id')->constrained('users')->cascadeOnDelete();
            $table->timestamp('last_read_at')->nullable();
            $table->timestamps();

            $table->unique(['room_id', 'user_id']);
        });

        Schema::create('internal_chat_messages', function (Blueprint $table) {
            $table->id();
            $table->foreignId('room_id')->constrained('internal_chat_rooms')->cascadeOnDelete();
            $table->foreignId('user_id')->constrained('users')->cascadeOnDelete();
            $table->text('message');
            $table->timestamps();
        });

        Schema::create('internal_call_sessions', function (Blueprint $table) {
            $table->id();
            $table->foreignId('caller_id')->constrained('users')->cascadeOnDelete();
            $table->foreignId('receiver_id')->constrained('users')->cascadeOnDelete();
            $table->foreignId('room_id')->nullable()->constrained('internal_chat_rooms')->nullOnDelete();
            $table->string('status')->default('ringing'); // ringing, active, ended, declined, missed
            $table->timestamp('started_at')->nullable();
            $table->timestamp('ended_at')->nullable();
            $table->unsignedInteger('duration_seconds')->default(0);
            $table->longText('sdp_offer')->nullable();
            $table->longText('sdp_answer')->nullable();
            $table->json('ice_candidates')->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('internal_call_sessions');
        Schema::dropIfExists('internal_chat_messages');
        Schema::dropIfExists('internal_chat_room_user');
        Schema::dropIfExists('internal_chat_rooms');
    }
};
