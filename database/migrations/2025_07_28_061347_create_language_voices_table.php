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
        Schema::create('language_voices', function (Blueprint $table) {
            $table->id();
            $table->foreignId('language_id')->constrained("languages")->onDelete('cascade');
            $table->enum('gender', ["Female", "Male"]); // 'female' or 'male'
            $table->string('locale'); // e.g., 'en-US', 'es-ES'
            $table->string('name'); // e.g., 'en-US-JennyNeural'
            $table->timestamps();

            // Ensure unique combination of language_id and gender
            $table->unique(['language_id', 'gender']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('language_voices');
    }
};
