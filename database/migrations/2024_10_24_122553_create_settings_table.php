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
        Schema::create('settings', function (Blueprint $table) {
            $table->id();
            $table->string('project_name');
            $table->text('logo')->nullable();
            $table->text('favicon')->nullable();
            $table->string('theme_layout')->default(1)->nullable();
            $table->string('sidebar_color')->default(1)->nullable();
            $table->string('color_theme')->default('white')->nullable();
            $table->boolean('mini_sidebar')->default(false)->nullable();
            $table->boolean('stiky_header')->default(true)->nullable();
            $table->text('image')->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('settings');
    }
};
