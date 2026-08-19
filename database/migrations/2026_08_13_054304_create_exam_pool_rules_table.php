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
        Schema::create('exam_pool_rules', function (Blueprint $table) {
            $table->id();
            $table->foreignId('exam_type_id')->constrained("exam_types")->onDelete('cascade');
            $table->foreignId('quiz_id')->constrained("quizzes")->onDelete('cascade'); // question category
            $table->enum('pool_type', ['common', 'specific']);
            $table->enum('specific_type', ['car', 'bike', 'bus', 'truck'])->nullable();
            $table->unsignedSmallInteger('required_count');
            $table->timestamps();

            $table->unique(['exam_type_id', 'quiz_id', 'pool_type', 'specific_type'], 'exam_pool_rule_unique');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('exam_pool_rules');
    }
};
