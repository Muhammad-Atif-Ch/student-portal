<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::table('languages', function (Blueprint $table) {
            $table->boolean('app_1_show')->default(false)->after('show');
            $table->boolean('app_2_show')->default(false)->after('app_1_show');
            $table->boolean('app_3_show')->default(false)->after('app_2_show');
        });

        DB::table('languages')->update([
            'app_1_show' => DB::raw('`show`'),
            'app_2_show' => DB::raw('`show`'),
        ]);

        Schema::table('languages', function (Blueprint $table) {
            $table->dropColumn('show');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('languages', function (Blueprint $table) {
            $table->boolean('show')->default(false)->after('status');
        });

        DB::table('languages')->update([
            'show' => DB::raw('`app_1_show`'),
        ]);

        Schema::table('languages', function (Blueprint $table) {
            $table->dropColumn(['app_1_show', 'app_2_show', 'app_3_show']);
        });
    }
};
