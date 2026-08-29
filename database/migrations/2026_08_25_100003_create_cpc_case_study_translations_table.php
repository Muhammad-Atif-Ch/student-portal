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
        Schema::create('cpc_case_study_translations', function (Blueprint $table) {
            $table->id();
            $table->foreignId('cpc_case_study_id')->constrained()->cascadeOnDelete();
            $table->foreignId('language_id')->constrained()->cascadeOnDelete();
            $table->text('title_translation')->nullable();
            $table->string('status')->default('pending'); // pending, partial, completed
            $table->timestamps();

            $table->unique(['cpc_case_study_id', 'language_id']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('cpc_case_study_translations');
    }
};
