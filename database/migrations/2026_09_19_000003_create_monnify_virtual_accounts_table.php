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
        Schema::create('monnify_virtual_accounts', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->cascadeOnDelete();
            $table->string('account_reference')->index();
            $table->string('account_name');
            $table->string('bank_name');
            $table->string('bank_code', 10);
            $table->string('bank_slug', 30)->nullable();
            $table->string('account_number', 20)->index();
            $table->string('reservation_status', 20)->default('ACTIVE');
            $table->json('raw_response')->nullable();
            $table->timestamps();

            $table->unique(['user_id', 'bank_code']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('monnify_virtual_accounts');
    }
};
