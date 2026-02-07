<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    /**
     * Run the migrations.
     * 
     * Adds 'test' to the message_type ENUM in sms_logs table.
     * Fixes: SQLSTATE[01000]: Warning: 1265 Data truncated for column 'message_type'
     */
    public function up(): void
    {
        DB::statement("ALTER TABLE sms_logs MODIFY COLUMN message_type ENUM('absence', 'homework', 'progress', 'schedule_change', 'emergency', 'general', 'test') NOT NULL");
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        // Convert any 'test' records to 'general' before removing the enum value
        DB::table('sms_logs')
            ->where('message_type', 'test')
            ->update(['message_type' => 'general']);

        DB::statement("ALTER TABLE sms_logs MODIFY COLUMN message_type ENUM('absence', 'homework', 'progress', 'schedule_change', 'emergency', 'general') NOT NULL");
    }
};