<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('sms_configuration', function (Blueprint $table) {
            $table->id();
            $table->enum('provider', ['twilio', 'vonage', 'messagebird', 'textlocal', 'bulksms'])->default('twilio');
            $table->text('api_key')->comment('Encrypted');
            $table->text('api_secret')->comment('Encrypted');
            $table->string('sender_id', 50)->nullable()->comment('UK sender ID or phone number');
            $table->decimal('credit_balance', 10, 2)->default(0.00)->comment('In GBP');
            $table->decimal('low_balance_threshold', 10, 2)->default(10.00)->comment('In GBP');
            $table->boolean('is_active')->default(true);
            $table->unsignedInteger('daily_limit')->nullable()->comment('Max SMS per day');
            $table->unsignedInteger('monthly_limit')->nullable()->comment('Max SMS per month');
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('sms_configuration');
    }
};