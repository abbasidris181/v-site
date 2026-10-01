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
        // 1. Service Categories Table
        Schema::create('service_categories', function (Blueprint $table) {
            $table->id();
            $table->string('name', 100);
            $table->string('slug', 100)->unique();
            $table->string('icon', 50)->default('identification');
            $table->string('description', 255)->nullable();
            $table->unsignedInteger('sort_order')->default(0);
            $table->boolean('is_active')->default(true);
            $table->timestamps();
        });

        // 2. Services Catalog Table (API & Manual Services)
        Schema::create('services', function (Blueprint $table) {
            $table->id();
            $table->foreignId('category_id')->constrained('service_categories')->cascadeOnDelete();
            $table->string('name', 100);
            $table->string('slug', 100)->unique();
            $table->enum('type', ['api', 'manual']);
            $table->decimal('price', 10, 2)->default(0.00);
            $table->string('description', 255)->nullable();
            $table->string('input_label', 100)->default('Input Value');
            $table->string('input_placeholder', 150)->nullable();
            $table->string('validation_rule', 50)->default('required'); // e.g. alphanumeric_15, numeric_11, bvn_retrieval
            $table->string('provider_driver', 50)->nullable(); // nin_v1, nin_v2, nin_v3, bvn_v1
            $table->boolean('is_bulk_allowed')->default(false);
            $table->boolean('is_active')->default(true);
            $table->unsignedInteger('sort_order')->default(0);
            $table->json('fields_schema')->nullable(); // For services requiring multiple custom inputs
            $table->timestamps();
        });

        // 3. Service Batches Table (For Bulk Submissions)
        Schema::create('service_batches', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->cascadeOnDelete();
            $table->foreignId('service_id')->constrained()->cascadeOnDelete();
            $table->string('batch_reference', 64)->unique();
            $table->unsignedInteger('total_submitted');
            $table->unsignedInteger('accepted_count');
            $table->unsignedInteger('rejected_count');
            $table->decimal('unit_price', 10, 2);
            $table->decimal('total_charged', 12, 2);
            $table->text('submission_notes')->nullable();
            $table->timestamps();

            $table->index(['user_id', 'created_at']);
        });

        // 4. Service Requests Table (Individual Trackable Submissions)
        Schema::create('service_requests', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->cascadeOnDelete();
            $table->foreignId('service_id')->constrained()->cascadeOnDelete();
            $table->foreignId('batch_id')->nullable()->constrained('service_batches')->nullOnDelete();
            $table->string('reference', 64)->unique();
            $table->string('tracking_input', 191); // Primary search key (e.g. 15-char Tracking ID or 11-digit NIN)
            $table->json('input_payload'); // Complete input attributes (e.g. full_name, phone_number)
            $table->decimal('amount_charged', 10, 2);
            $table->enum('status', ['pending', 'processing', 'completed', 'failed'])->default('pending');
            $table->foreignId('assigned_to')->nullable()->constrained('users')->nullOnDelete(); // Staff picking the job
            $table->foreignId('processed_by')->nullable()->constrained('users')->nullOnDelete(); // Staff/Admin who resolved
            $table->json('result_payload')->nullable(); // Returned data or administrative resolution
            $table->text('admin_notes')->nullable();
            $table->text('rejection_reason')->nullable();
            $table->timestamp('refunded_at')->nullable(); // Recorded when manual refund button is triggered
            $table->foreignId('refunded_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamp('completed_at')->nullable();
            $table->timestamps();

            $table->index(['user_id', 'status']);
            $table->index(['service_id', 'status']);
            $table->index('tracking_input');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('service_requests');
        Schema::dropIfExists('service_batches');
        Schema::dropIfExists('services');
        Schema::dropIfExists('service_categories');
    }
};
