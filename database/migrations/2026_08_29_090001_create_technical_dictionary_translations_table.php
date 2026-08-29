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
        Schema::create('technical_dictionary_translations', function (Blueprint $table) {
            $table->id();
            $table->foreignId('technical_dictionary_id')->constrained(indexName: 'td_translations_dictionary_id_foreign')->cascadeOnDelete();
            $table->foreignId('language_id')->constrained(indexName: 'td_translations_language_id_foreign')->cascadeOnDelete();
            $table->text('explanation_translation')->nullable();
            $table->string('explanation_audio')->nullable();
            $table->string('status')->default('pending'); // pending, partial, completed
            $table->timestamps();

            $table->unique(['technical_dictionary_id', 'language_id'], 'td_translations_dictionary_language_unique');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('technical_dictionary_translations');
    }
};
