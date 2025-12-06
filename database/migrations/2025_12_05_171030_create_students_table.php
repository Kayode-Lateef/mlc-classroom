<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('students', function (Blueprint $table) {
            $table->id();
            $table->string('first_name');
            $table->string('last_name');
            $table->date('date_of_birth');
            $table->foreignId('parent_id')->constrained('users')->onDelete('restrict');
            $table->date('enrollment_date');
            $table->enum('status', ['active', 'inactive', 'graduated', 'withdrawn'])->default('active');
            $table->string('emergency_contact')->nullable();
            $table->string('emergency_phone', 20)->nullable();
            $table->text('medical_info')->nullable();
            $table->string('profile_photo')->nullable();
            $table->timestamps();

            // Indexes
            $table->index('parent_id');
            $table->index('status');
            $table->index('enrollment_date');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('students');
    }
};