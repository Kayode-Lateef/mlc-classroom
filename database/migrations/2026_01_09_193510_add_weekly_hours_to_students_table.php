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
        Schema::table('students', function (Blueprint $table) {
            $table->decimal('weekly_hours', 4, 1)
                ->default(1.0)
                ->after('enrollment_date')
                ->comment('Weekly teaching hours (0.5 hour increments, max 15 hours)');
            
            // Index for dashboard calculations
            $table->index('weekly_hours', 'idx_student_weekly_hours');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('students', function (Blueprint $table) {
            $table->dropIndex('idx_student_weekly_hours');
            $table->dropColumn('weekly_hours');
        });
    }
};