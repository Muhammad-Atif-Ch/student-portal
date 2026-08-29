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
        Schema::create('cpc_case_study_block_translations', function (Blueprint $table) {
            $table->id();
            $table->foreignId('cpc_case_study_block_id')->constrained(indexName: 'cscsbt_block_id_foreign')->cascadeOnDelete();
            $table->foreignId('language_id')->constrained()->cascadeOnDelete();
            $table->text('content_translation')->nullable();
            $table->json('items_translation')->nullable();
            $table->string('content_audio')->nullable();
            $table->timestamps();

            $table->unique(['cpc_case_study_block_id', 'language_id'], 'cscsbt_block_language_unique');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('cpc_case_study_block_translations');
    }
};
