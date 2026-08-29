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
        Schema::create('cpc_question_translations', function (Blueprint $table) {
            $table->id();
            $table->foreignId('cpc_question_id')->constrained()->cascadeOnDelete();
            $table->foreignId('language_id')->constrained()->cascadeOnDelete();
            $table->text('question_translation')->nullable();
            $table->text('answer_explanation_translation')->nullable();
            $table->string('question_audio')->nullable();
            $table->string('status')->default('pending'); // pending, partial, completed
            $table->timestamps();

            $table->unique(['cpc_question_id', 'language_id']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('cpc_question_translations');
    }
};
