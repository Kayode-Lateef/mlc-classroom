<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use App\Models\SystemSetting;

class HourlyRateSettingSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        SystemSetting::updateOrCreate(
            ['key' => 'hourly_rate'],
            [
                'value' => '25.00',  // Default £25 per hour
                'type' => 'string',
                'description' => 'Hourly teaching rate in GBP for income calculations (SuperAdmin only)',
            ]
        );

        SystemSetting::updateOrCreate(
            ['key' => 'weekly_hours_target'],
            [
                'value' => '40',  // Default 40 hours per week target
                'type' => 'integer',
                'description' => 'Target weekly teaching hours for workload planning',
            ]
        );

        SystemSetting::updateOrCreate(
            ['key' => 'monthly_income_target'],
            [
                'value' => '4000.00',  // Default £4000 per month
                'type' => 'string',
                'description' => 'Target monthly income for dashboard calculations',
            ]
        );
    }
}