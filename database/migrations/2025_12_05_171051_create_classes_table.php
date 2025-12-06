<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('classes', function (Blueprint $table) {
            $table->id();
            $table->string('name')->comment('e.g., Maths 11+, English GCSE');
            $table->string('subject', 100);
            $table->string('level', 100)->nullable()->comment('e.g., Year 6, 11+, GCSE');
            $table->string('room_number', 50)->nullable();
            $table->foreignId('teacher_id')->nullable()->constrained('users')->onDelete('set null');
            $table->unsignedInteger('capacity')->default(20);
            $table->text('description')->nullable();
            $table->timestamps();

            // Indexes
            $table->index('teacher_id');
            $table->index('subject');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('classes');
    }
};