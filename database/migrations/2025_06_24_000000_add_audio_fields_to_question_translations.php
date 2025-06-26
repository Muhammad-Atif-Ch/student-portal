<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
  public function up()
  {
    Schema::table('question_translations', function (Blueprint $table) {
      $table->string('question_audio')->nullable()->after('question_translation');
      $table->string('a_audio')->nullable()->after('a_translation');
      $table->string('b_audio')->nullable()->after('b_translation');
      $table->string('c_audio')->nullable()->after('c_translation');
      $table->string('d_audio')->nullable()->after('d_translation');
      $table->string('answer_explanation_audio')->nullable()->after('answer_explanation_translation');
    });
  }

  public function down()
  {
    Schema::table('question_translations', function (Blueprint $table) {
      $table->dropColumn([
        'question_audio',
        'a_audio',
        'b_audio',
        'c_audio',
        'd_audio',
        'answer_explanation_audio'
      ]);
    });
  }
};