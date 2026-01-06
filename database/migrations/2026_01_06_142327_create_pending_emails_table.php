<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('pending_emails', function (Blueprint $table) {
            $table->id();
            
            // User reference (nullable - preserves email history if user deleted)
            $table->foreignId('user_id')->nullable()->constrained('users')->onDelete('set null');
            
            // Email details
            $table->string('email');
            $table->string('subject');
            $table->text('body');
            
            // Additional data for email template (JSON)
            $table->json('data')->nullable()->comment('Additional data for email template');
            
            // Status tracking
            $table->enum('status', ['pending', 'sent', 'failed'])->default('pending');
            
            // Scheduling
            $table->timestamp('scheduled_at')->nullable()->comment('When to send this email');
            $table->timestamp('sent_at')->nullable()->comment('When email was successfully sent');
            
            // Retry tracking
            $table->unsignedTinyInteger('attempts')->default(0)->comment('Number of send attempts (max 3)');
            $table->text('error_message')->nullable()->comment('Last error message if failed');
            
            $table->timestamps();

            // Indexes for efficient queries
            $table->index(['status', 'scheduled_at'], 'idx_status_scheduled');
            $table->index('email', 'idx_email');
            $table->index('user_id', 'idx_user_id');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('pending_emails');
    }
};