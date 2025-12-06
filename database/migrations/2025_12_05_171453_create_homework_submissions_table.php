<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('homework_submissions', function (Blueprint $table) {
            $table->id();
            $table->foreignId('homework_assignment_id')->constrained('homework_assignments')->onDelete('cascade');
            $table->foreignId('student_id')->constrained('students')->onDelete('cascade');
            $table->dateTime('submitted_date')->nullable();
            $table->enum('status', ['pending', 'submitted', 'late', 'graded'])->default('pending');
            $table->string('file_path')->nullable()->comment('Student submission');
            $table->text('teacher_comments')->nullable();
            $table->string('grade', 50)->nullable();
            $table->dateTime('graded_at')->nullable();
            $table->timestamps();

            // Indexes
            $table->index('homework_assignment_id');
            $table->index('student_id');
            $table->index('status');
            $table->unique(['homework_assignment_id', 'student_id'], 'unique_submission');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('homework_submissions');
    }
};