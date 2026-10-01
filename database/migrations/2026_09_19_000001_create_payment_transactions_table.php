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
        Schema::create('payment_transactions', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->cascadeOnDelete();
            $table->string('payment_reference', 64)->unique();
            $table->string('transaction_reference', 64)->nullable()->unique();
            $table->decimal('amount', 14, 2);
            $table->decimal('fee', 10, 2)->default(0.00);
            $table->string('currency', 3)->default('NGN');
            $table->string('status', 30)->default('pending'); // pending, paid, failed, expired, cancelled
            $table->string('payment_method', 50)->nullable(); // CARD, ACCOUNT_TRANSFER, USSD, etc.
            $table->text('checkout_url')->nullable();
            $table->string('gateway', 50)->default('monnify');
            $table->json('raw_response')->nullable();
            $table->timestamp('paid_at')->nullable();
            $table->timestamps();

            $table->index(['user_id', 'created_at']);
            $table->index(['status', 'gateway']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('payment_transactions');
    }
};
