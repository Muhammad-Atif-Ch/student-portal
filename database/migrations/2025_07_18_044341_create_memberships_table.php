<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('memberships', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->onDelete('cascade');
            $table->enum('membership_type', ['free', 'premium'])->default('free');
            $table->timestamp('start_date')->nullable();
            $table->timestamp('end_date')->nullable();
            $table->string('order_id')->nullable();
            $table->boolean('auto_renewing')->default(false);
            $table->bigInteger('price')->nullable();
            $table->string('currency', 10)->nullable();
            $table->string('country_code', 10)->nullable();
            $table->integer('cancel_reason')->nullable();
            $table->integer('purchase_type')->nullable();
            $table->integer('acknowledgement_state')->nullable();
            $table->json('raw_response')->nullable();
            $table->boolean('status')->default(true);
            $table->timestamps();

            // Indexes
            $table->index(['user_id', 'status']);
            $table->index('end_date');

            // Ensure only one active membership per user
            $table->unique(['user_id', 'status'], 'unique_status_membership');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('memberships');
    }
};
