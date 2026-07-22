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
        Schema::create('translation_glossaries', function (Blueprint $table) {
            $table->id();
            $table->string('source_term');       // e.g. "roundabout"
            $table->foreignId('language_id')->constrained()->onDelete('cascade');
            $table->string('target_term');       // e.g. "rond-point"
            $table->timestamps();

            $table->unique(['source_term', 'language_id']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('translation_glossaries');
    }
};
