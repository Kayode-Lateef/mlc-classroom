<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('notification_settings', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained('users')->onDelete('cascade');
            $table->enum('notification_type', ['absence', 'homework_assigned', 'homework_graded', 'progress_report', 'schedule_change', 'emergency']);
            $table->boolean('email_enabled')->default(true);
            $table->boolean('sms_enabled')->default(true);
            $table->boolean('in_app_enabled')->default(true);
            $table->timestamps();

            // Indexes
            $table->index('user_id');
            $table->unique(['user_id', 'notification_type'], 'unique_user_notification');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('notification_settings');
    }
};