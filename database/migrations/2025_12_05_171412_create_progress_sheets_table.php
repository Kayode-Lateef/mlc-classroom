<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('progress_sheets', function (Blueprint $table) {
            $table->id();
            $table->foreignId('class_id')->constrained('classes')->onDelete('cascade');
            $table->foreignId('schedule_id')->nullable()->constrained('schedules')->onDelete('set null');
            $table->date('date');
            $table->text('objective')->nullable()->comment('Lesson objective');
            $table->string('topic')->nullable();
            $table->foreignId('teacher_id')->constrained('users')->onDelete('restrict');
            $table->text('notes')->nullable();
            $table->timestamps();

            // Indexes
            $table->index(['class_id', 'date'], 'idx_class_date');
            $table->index('teacher_id');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('progress_sheets');
    }
};