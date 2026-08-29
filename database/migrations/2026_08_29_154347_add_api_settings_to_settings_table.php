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
        Schema::table('settings', function (Blueprint $table) {
            $table->text('translation_api_key')->nullable();
            $table->string('translation_api_region')->nullable();
            $table->text('tts_api_key')->nullable();
            $table->string('tts_api_region')->nullable();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('settings', function (Blueprint $table) {
            $table->dropColumn(['translation_api_key', 'translation_api_region', 'tts_api_key', 'tts_api_region']);
        });
    }
};
