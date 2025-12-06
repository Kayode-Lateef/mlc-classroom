<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('pending_sms', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained('users')->onDelete('cascade');
            $table->string('phone_number', 20);
            $table->enum('message_type', ['absence', 'homework', 'progress', 'schedule_change', 'emergency']);
            $table->text('message_content');
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
        Schema::dropIfExists('pending_sms');
    }
};