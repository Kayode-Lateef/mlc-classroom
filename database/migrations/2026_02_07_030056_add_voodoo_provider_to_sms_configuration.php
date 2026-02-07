<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    /**
     * Run the migrations.
     * 
     * Adds 'voodoo' to the provider ENUM in sms_configuration table.
     * Fixes: SQLSTATE[01000]: Warning: 1265 Data truncated for column 'provider'
     */
    public function up(): void
    {
        // Add 'voodoo' to the provider ENUM
        DB::statement("ALTER TABLE sms_configuration MODIFY COLUMN provider ENUM('twilio', 'vonage', 'messagebird', 'textlocal', 'bulksms', 'voodoo') NOT NULL DEFAULT 'textlocal'");

        // Make api_secret nullable (not all providers require it, e.g., TextLocal)
        Schema::table('sms_configuration', function (Blueprint $table) {
            $table->text('api_secret')->nullable()->comment('Encrypted - Not required for all providers')->change();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        // First, update any 'voodoo' records to 'textlocal' to prevent data loss
        DB::table('sms_configuration')
            ->where('provider', 'voodoo')
            ->update(['provider' => 'textlocal']);

        // Revert provider ENUM
        DB::statement("ALTER TABLE sms_configuration MODIFY COLUMN provider ENUM('twilio', 'vonage', 'messagebird', 'textlocal', 'bulksms') NOT NULL DEFAULT 'twilio'");

        // Revert api_secret to non-nullable
        Schema::table('sms_configuration', function (Blueprint $table) {
            $table->text('api_secret')->nullable(false)->comment('Encrypted')->change();
        });
    }
};