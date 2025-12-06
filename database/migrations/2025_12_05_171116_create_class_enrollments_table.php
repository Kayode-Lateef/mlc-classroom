<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('class_enrollments', function (Blueprint $table) {
            $table->id();
            $table->foreignId('student_id')->constrained('students')->onDelete('cascade');
            $table->foreignId('class_id')->constrained('classes')->onDelete('cascade');
            $table->date('enrollment_date');
            $table->enum('status', ['active', 'dropped', 'completed'])->default('active');
            $table->timestamps();

            // Indexes
            $table->index('student_id');
            $table->index('class_id');
            $table->index('status');
            $table->unique(['student_id', 'class_id', 'status'], 'unique_enrollment');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('class_enrollments');
    }
};