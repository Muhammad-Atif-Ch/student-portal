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
        Schema::create('cpc_case_study_blocks', function (Blueprint $table) {
            $table->id();
            $table->foreignId('cpc_case_study_id')->constrained()->cascadeOnDelete();
            $table->enum('type', ['text', 'image', 'list']);
            $table->text('content')->nullable();
            $table->json('items')->nullable();
            $table->string('list_style')->nullable(); // bullet, numbered
            $table->string('file_path')->nullable();
            $table->unsignedInteger('sort_order')->default(0);
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('cpc_case_study_blocks');
    }
};
