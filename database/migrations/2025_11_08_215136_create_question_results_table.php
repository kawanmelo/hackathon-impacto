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
        Schema::create('question_results', static function (Blueprint $table) {
            $table->id();
            $table->foreignId('student_id')->constrained('students',);
            $table->foreignId('question_id')->constrained('questions',);
            $table->boolean('score');
            $table->float('time_spent');
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('question_results');
    }
};
