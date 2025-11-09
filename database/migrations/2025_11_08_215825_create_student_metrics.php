<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{

    public function up(): void
    {
        Schema::create('student_metrics', static function (Blueprint $table) {
            $table->id();
            $table->foreignId('student_id')->constrained('students');
            $table->foreignId('discipline_id')->constrained('disciplines');
            $table->float('average_score');
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('student_metrics');
    }
};
