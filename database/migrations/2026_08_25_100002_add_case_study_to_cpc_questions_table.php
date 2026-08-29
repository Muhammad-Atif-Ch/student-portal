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
        Schema::table('cpc_questions', function (Blueprint $table) {
            $table->foreignId('cpc_case_study_id')->nullable()->after('id')->constrained()->nullOnDelete();
            $table->unsignedInteger('sort_order')->default(0)->after('cpc_case_study_id');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('cpc_questions', function (Blueprint $table) {
            $table->dropConstrainedForeignId('cpc_case_study_id');
            $table->dropColumn('sort_order');
        });
    }
};
