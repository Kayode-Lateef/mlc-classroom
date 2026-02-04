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
        Schema::table('homework_submissions', function (Blueprint $table) {
            // Add fields for teacher/admin who marked as submitted
            $table->foreignId('submitted_by')->nullable()->after('submitted_date')->constrained('users')->onDelete('set null')->comment('Teacher/Admin who marked as submitted');
            
            // Add field for tracking who graded
            $table->foreignId('graded_by')->nullable()->after('graded_at')->constrained('users')->onDelete('set null')->comment('Teacher/Admin who graded');
            
            // Add notes field for submission marking
            $table->text('submission_notes')->nullable()->after('submitted_by')->comment('Notes when marking as submitted');
            
            // Drop the file_path column as parents won't submit files
            // COMMENTED OUT - Keep for backward compatibility, but can be used for teacher notes/attachments
            // $table->dropColumn('file_path');
            
            // Index for queries
            $table->index('submitted_by');
            $table->index('graded_by');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('homework_submissions', function (Blueprint $table) {
            $table->dropForeign(['submitted_by']);
            $table->dropForeign(['graded_by']);
            $table->dropColumn(['submitted_by', 'graded_by', 'submission_notes']);
            $table->dropIndex(['submitted_by']);
            $table->dropIndex(['graded_by']);
        });
    }
};