<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('progress_notes', function (Blueprint $table) {
            $table->id();
            $table->foreignId('progress_sheet_id')->constrained('progress_sheets')->onDelete('cascade');
            $table->foreignId('student_id')->constrained('students')->onDelete('cascade');
            $table->enum('performance', ['excellent', 'good', 'average', 'struggling', 'absent'])->nullable();
            $table->text('notes')->nullable();
            $table->timestamps();

            // Indexes
            $table->index('progress_sheet_id');
            $table->index('student_id');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('progress_notes');
    }
};