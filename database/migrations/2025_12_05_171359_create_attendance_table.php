<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('attendance', function (Blueprint $table) {
            $table->id();
            $table->foreignId('student_id')->constrained('students')->onDelete('cascade');
            $table->foreignId('class_id')->constrained('classes')->onDelete('cascade');
            $table->foreignId('schedule_id')->constrained('schedules')->onDelete('cascade');
            $table->date('date');
            $table->enum('status', ['present', 'absent', 'late', 'unauthorized']);
            $table->foreignId('marked_by')->constrained('users')->onDelete('restrict')->comment('Teacher who marked attendance');
            $table->text('notes')->nullable();
            $table->timestamps();

            // Indexes
            $table->index(['student_id', 'date'], 'idx_student_date');
            $table->index(['class_id', 'date'], 'idx_class_date');
            $table->index('status');
            $table->unique(['student_id', 'class_id', 'schedule_id', 'date'], 'unique_attendance');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('attendance');
    }
};