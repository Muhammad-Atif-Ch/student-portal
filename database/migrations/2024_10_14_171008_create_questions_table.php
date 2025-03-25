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
        Schema::create('questions', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('quiz_id');
            $table->string('question');
            $table->enum('correct_answer', ['a', 'b', 'c', 'd']);
            $table->string('a');
            $table->string('b');
            $table->string('c')->nullable();
            $table->string('d')->nullable();
            $table->enum('type', ['car', 'bike', 'both'])->nullable();
            $table->text('answer_explanation')->nullable();
            $table->text('visual_explanation')->nullable();
            $table->text('audio_file')->nullable();
            $table->text('image')->nullable();
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
