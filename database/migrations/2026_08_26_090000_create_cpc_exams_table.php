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
        Schema::create('cpc_exams', function (Blueprint $table) {
            $table->id();
            $table->foreignId('cpc_type_id')->constrained('cpc_types')->cascadeOnDelete();
            $table->string('title');
            $table->enum('mode', ['full', 'short'])->default('full');
            $table->unsignedInteger('total_time_minutes');
            $table->unsignedInteger('total_questions');
            $table->unsignedInteger('passing_score');
            $table->unsignedInteger('min_marks_per_scenario')->nullable();
            $table->boolean('is_active')->default(true);
            $table->timestamps();

            $table->unique(['cpc_type_id', 'mode']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('cpc_exams');
    }
};
