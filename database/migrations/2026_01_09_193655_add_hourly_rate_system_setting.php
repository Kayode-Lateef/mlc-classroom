<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use App\Models\SystemSetting;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        // Add hourly rate setting to system_settings table
        SystemSetting::create([
            'key' => 'hourly_rate',
            'value' => '50.00',
            'type' => 'string',
            'description' => 'Hourly teaching rate in GBP (for income calculations, SuperAdmin only)',
        ]);
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        // Remove hourly rate setting
        SystemSetting::where('key', 'hourly_rate')->delete();
    }
};