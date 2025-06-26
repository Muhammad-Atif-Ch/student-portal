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
        Schema::create('question_translations', function (Blueprint $table) {
            $table->id();
            $table->foreignId('quiz_id')->constrained("quizzes")->onDelete('cascade');
            $table->foreignId('question_id')->constrained("questions")->onDelete('cascade');
            $table->foreignId('lenguage_id')->constrained("lenguages")->onDelete('cascade');
            $table->text('question_translation')->nullable();
            $table->text('a_translation')->nullable();
            $table->text('b_translation')->nullable();
            $table->text('c_translation')->nullable();
            $table->text('d_translation')->nullable();
            $table->text('answer_explanation_translation')->nullable();
            $table->string('question_audio')->nullable();
            $table->string('a_audio')->nullable();
            $table->string('b_audio')->nullable();
            $table->string('c_audio')->nullable();
            $table->string('d_audio')->nullable();
            $table->string('answer_explanation_translation_audio')->nullable();
            $table->timestamps();

            // Add unique constraint to prevent duplicate translations
            $table->unique(['question_id', 'lenguage_id']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('question_translations');
    }
};
