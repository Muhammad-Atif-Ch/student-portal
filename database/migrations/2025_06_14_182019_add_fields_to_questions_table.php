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
        Schema::table('questions', function (Blueprint $table) {
            $table->string('question_translation')->nullable();
            $table->string('a_translation')->nullable();
            $table->string('b_translation')->nullable();
            $table->string('c_translation')->nullable();
            $table->string('d_translation')->nullable();
            $table->text('answer_explanation_translation')->nullable();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('questions', function (Blueprint $table) {
            $table->dropColumn([
                'question_translation',
                'a_translation',
                'b_translation',
                'c_translation',
                'd_translation',
                'answer_explanation_translation'
            ]);
        });
    }
};
