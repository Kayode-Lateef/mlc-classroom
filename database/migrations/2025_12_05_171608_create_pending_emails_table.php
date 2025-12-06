<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('pending_emails', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained('users')->onDelete('cascade');
            $table->string('email');
            $table->string('subject');
            $table->text('body');
            $table->enum('status', ['pending', 'sent', 'failed'])->default('pending');
            $table->dateTime('scheduled_at');
            $table->unsignedInteger('attempts')->default(0);
            $table->timestamps();

            // Indexes
            $table->index(['status', 'scheduled_at'], 'idx_status_scheduled');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('pending_emails');
    }
};