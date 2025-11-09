<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{

    public function up(): void
    {
        Schema::table('question_results', static function (Blueprint $table) {
            $table->foreignId('quiz_id')->constrained('quizzes');
        });
    }

    public function down(): void
    {
        Schema::table('question_results', static function (Blueprint $table) {
            $table->dropForeignIdFor('quiz_id');
        });
    }
};
