<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('homework_assignment_topic', function (Blueprint $table) {
            $table->unsignedInteger('max_score')
                ->nullable()
                ->after('homework_topic_id')
                ->comment('Maximum possible score for this topic on this homework');
        });
    }

    public function down(): void
    {
        Schema::table('homework_assignment_topic', function (Blueprint $table) {
            $table->dropColumn('max_score');
        });
    }
};