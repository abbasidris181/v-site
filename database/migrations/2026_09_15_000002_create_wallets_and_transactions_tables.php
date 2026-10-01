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
        Schema::create('wallets', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->cascadeOnDelete()->unique();
            $table->decimal('balance', 14, 2)->default(0.00);
            $table->string('currency', 3)->default('NGN');
            $table->timestamps();
        });

        Schema::create('wallet_transactions', function (Blueprint $table) {
            $table->id();
            $table->foreignId('wallet_id')->constrained()->cascadeOnDelete();
            $table->foreignId('user_id')->constrained()->cascadeOnDelete();
            $table->enum('type', ['credit', 'debit']);
            $table->decimal('amount', 14, 2);
            $table->decimal('balance_before', 14, 2);
            $table->decimal('balance_after', 14, 2);
            $table->string('reference', 64)->unique();
            $table->string('idempotency_key', 100)->nullable()->unique();
            $table->string('category', 50); // deposit, transfer_in, transfer_out, service_charge, manual_refund, admin_adjustment
            $table->string('status', 30)->default('successful'); // successful, failed, reversed
            $table->foreignId('counterpart_user_id')->nullable()->constrained('users')->nullOnDelete();
            $table->string('description', 255);
            $table->json('metadata')->nullable();
            $table->timestamps();

            $table->index(['user_id', 'created_at']);
            $table->index(['wallet_id', 'category']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('wallet_transactions');
        Schema::dropIfExists('wallets');
    }
};
