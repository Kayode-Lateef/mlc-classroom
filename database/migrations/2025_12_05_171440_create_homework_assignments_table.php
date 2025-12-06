<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('homework_assignments', function (Blueprint $table) {
            $table->id();
            $table->foreignId('class_id')->constrained('classes')->onDelete('cascade');
            $table->foreignId('progress_sheet_id')->nullable()->constrained('progress_sheets')->onDelete('set null');
            $table->string('title');
            $table->text('description')->nullable();
            $table->date('assigned_date');
            $table->date('due_date');
            $table->string('file_path')->nullable()->comment('Teacher attachment');
            $table->foreignId('teacher_id')->constrained('users')->onDelete('restrict');
            $table->timestamps();

            // Indexes
            $table->index('class_id');
            $table->index('due_date');
            $table->index('assigned_date');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('homework_assignments');
    }
};