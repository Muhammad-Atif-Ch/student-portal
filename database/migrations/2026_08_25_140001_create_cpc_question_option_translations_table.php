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
        Schema::create('cpc_question_option_translations', function (Blueprint $table) {
            $table->id();
            $table->foreignId('cpc_question_option_id')->constrained(indexName: 'cqot_option_id_foreign')->cascadeOnDelete();
            $table->foreignId('language_id')->constrained()->cascadeOnDelete();
            $table->text('text_value_translation')->nullable();
            $table->string('option_audio')->nullable();
            $table->timestamps();

            $table->unique(['cpc_question_option_id', 'language_id'], 'cqot_option_language_unique');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('cpc_question_option_translations');
    }
};
