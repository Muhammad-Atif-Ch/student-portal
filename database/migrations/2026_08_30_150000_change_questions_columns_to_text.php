<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        DB::statement('ALTER TABLE `questions` MODIFY `question` TEXT NOT NULL');
        DB::statement('ALTER TABLE `questions` MODIFY `a` TEXT NOT NULL');
        DB::statement('ALTER TABLE `questions` MODIFY `b` TEXT NOT NULL');
        DB::statement('ALTER TABLE `questions` MODIFY `c` TEXT NULL');
        DB::statement('ALTER TABLE `questions` MODIFY `d` TEXT NULL');
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        DB::statement('ALTER TABLE `questions` MODIFY `question` VARCHAR(255) NOT NULL');
        DB::statement('ALTER TABLE `questions` MODIFY `a` VARCHAR(255) NOT NULL');
        DB::statement('ALTER TABLE `questions` MODIFY `b` VARCHAR(255) NOT NULL');
        DB::statement('ALTER TABLE `questions` MODIFY `c` VARCHAR(255) NULL');
        DB::statement('ALTER TABLE `questions` MODIFY `d` VARCHAR(255) NULL');
    }
};
