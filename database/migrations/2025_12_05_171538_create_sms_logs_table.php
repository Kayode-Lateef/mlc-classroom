<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('sms_logs', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained('users')->onDelete('cascade')->comment('Recipient parent');
            $table->string('phone_number', 20)->comment('UK format: +44...');
            $table->enum('message_type', ['absence', 'homework', 'progress', 'schedule_change', 'emergency', 'general']);
            $table->text('message_content');
            $table->string('provider', 50);
            $table->string('provider_message_id', 100)->nullable()->comment('Twilio SID or similar');
            $table->enum('status', ['pending', 'queued', 'sent', 'delivered', 'failed', 'undelivered'])->default('pending');
            $table->text('failure_reason')->nullable();
            $table->decimal('cost', 6, 4)->nullable()->comment('In GBP per SMS');
            $table->dateTime('sent_at')->nullable();
            $table->dateTime('delivered_at')->nullable();
            $table->unsignedTinyInteger('retry_count')->default(0);
            $table->timestamps();

            // Indexes
            $table->index('user_id');
            $table->index('status');
            $table->index('sent_at');
            $table->index('message_type');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('sms_logs');
    }
};