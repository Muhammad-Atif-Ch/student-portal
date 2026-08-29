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
        Schema::create('cpc_question_options', function (Blueprint $table) {
            $table->id();
            $table->foreignId('cpc_question_id')->constrained()->cascadeOnDelete();
            $table->string('option_key', 1); // a, b, c, d
            $table->enum('type', ['text', 'file']);
            $table->text('text_value')->nullable();
            $table->string('file_path')->nullable();
            $table->boolean('is_correct')->default(false);
            $table->timestamps();

            $table->unique(['cpc_question_id', 'option_key']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('cpc_question_options');
    }
};
