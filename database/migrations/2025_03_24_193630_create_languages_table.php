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
        Schema::create('languages', function (Blueprint $table) {
            $table->id();
            $table->string('family', 100)->nullable();
            $table->string('name', 100)->nullable();
            $table->string('native_name', 100)->nullable();
            $table->string('code', 10)->nullable();
            $table->string('code_2', 10)->nullable();
            $table->string('country_code', 10)->nullable();
            $table->boolean('status')->default(false);
            $table->boolean('show')->default(false);
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('languages');
    }
};
