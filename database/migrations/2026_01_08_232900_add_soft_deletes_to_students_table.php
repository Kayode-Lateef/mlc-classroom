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
            $table->softDeletes()->after('updated_at');
        });

        // Add indexes for better performance
        Schema::table('students', function (Blueprint $table) {
            // Index for name searches
            $table->index(['first_name', 'last_name'], 'idx_student_names');
            
            // Index for parent lookup
            $table->index('parent_id', 'idx_student_parent');
            
            // Index for enrollment date filtering
            $table->index('enrollment_date', 'idx_student_enrollment');
            
            // Index for status filtering
            $table->index('status', 'idx_student_status');
            
            // Index for date of birth (age calculations)
            $table->index('date_of_birth', 'idx_student_dob');
            
            // Composite index for duplicate detection
            $table->index(['first_name', 'last_name', 'date_of_birth', 'parent_id'], 'idx_student_duplicate_check');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('students', function (Blueprint $table) {
            // Drop indexes first
            $table->dropIndex('idx_student_names');
            $table->dropIndex('idx_student_parent');
            $table->dropIndex('idx_student_enrollment');
            $table->dropIndex('idx_student_status');
            $table->dropIndex('idx_student_dob');
            $table->dropIndex('idx_student_duplicate_check');
            
            // Drop soft deletes column
            $table->dropSoftDeletes();
        });
    }
};