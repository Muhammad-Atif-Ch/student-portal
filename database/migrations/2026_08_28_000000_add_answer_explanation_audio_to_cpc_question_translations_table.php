<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('cpc_question_translations', function (Blueprint $table) {
            $table->string('answer_explanation_audio')->nullable()->after('answer_explanation_translation');
        });
    }

    public function down(): void
    {
        Schema::table('cpc_question_translations', function (Blueprint $table) {
            $table->dropColumn('answer_explanation_audio');
        });
    }
};
