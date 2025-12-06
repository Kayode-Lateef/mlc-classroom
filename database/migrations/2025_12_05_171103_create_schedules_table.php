<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('schedules', function (Blueprint $table) {
            $table->id();
            $table->foreignId('class_id')->constrained('classes')->onDelete('cascade');
            $table->enum('day_of_week', ['Monday', 'Tuesday', 'Wednesday', 'Thursday', 'Friday', 'Saturday', 'Sunday']);
            $table->time('start_time');
            $table->time('end_time');
            $table->boolean('recurring')->default(true);
            $table->timestamps();

            // Indexes
            $table->index('class_id');
            $table->index('day_of_week');
            $table->unique(['class_id', 'day_of_week', 'start_time'], 'unique_class_schedule');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('schedules');
    }
};