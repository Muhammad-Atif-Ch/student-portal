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
            $table->foreignId('language_id')->constrained("languages")->onDelete('cascade');
            $table->text('title_audio_file')->nullable();
            $table->text('a_audio_file')->nullable();
            $table->text('b_audio_file')->nullable();
            $table->text('c_audio_file')->nullable();
            $table->text('d_audio_file')->nullable();
            $table->timestamps();
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
