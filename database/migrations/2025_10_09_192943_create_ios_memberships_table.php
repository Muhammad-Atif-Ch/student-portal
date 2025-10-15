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
        Schema::create('ios_memberships', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->onDelete('cascade');
            $table->string('product_id', 256)->nullable();
            $table->string('transaction_id', 256)->nullable();
            $table->string('original_transaction_id', 256)->nullable();
            $table->string('environment', 256)->nullable(); // sandbox or production
            $table->dateTime('purchase_date')->nullable();
            $table->dateTime('expires_date')->nullable();
            $table->boolean('is_trial_period')->nullable();
            $table->boolean('is_in_intro_offer_period')->nullable();
            $table->string('subscription_group_identifier', 256)->nullable();
            $table->boolean('auto_renew_status')->default(false);
            $table->string('auto_renew_product_id', 256)->nullable();
            $table->longText('receipt_data')->nullable();
            $table->json('raw_response')->nullable();
            $table->integer('status')->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('ios_memberships');
    }
};
