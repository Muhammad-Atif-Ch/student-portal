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
        Schema::create('questions', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('quiz_id');
            $table->string('question');
            $table->enum('correct_answer', ['a', 'b', 'c', 'd', 'e', 'f']);
            $table->string('a');
            $table->string('b');
            $table->string('c');
            $table->string('d');
            $table->string('e')->nullable();
            $table->string('f')->nullable();
            $table->text('answer_explanation')->nullable();
            $table->text('audio_file')->nullable();
            $table->timestamps();

            // Foreign key
            $table->foreign('quiz_id')->references('id')->on('quizzes')->onDelete('cascade');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('questions');
    }
};
