<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('group_metrics', static function (Blueprint $table) {
            $table->foreignId('group_id')
                ->after('id')
                ->constrained('groups')
                ->cascadeOnDelete();
        });
    }

    public function down(): void
    {
        Schema::table('group_metrics', static function (Blueprint $table) {
            $table->dropConstrainedForeignId('group_id');
        });
    }
};
