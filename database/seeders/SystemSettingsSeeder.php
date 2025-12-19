<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\SystemSetting;

class SystemSettingsSeeder extends Seeder
{
    public function run(): void
    {
        $settings = [
            // School Information
            ['key' => 'school_name', 'value' => 'Maidstone Learning Centre', 'type' => 'string', 'description' => 'School name'],
            ['key' => 'school_email', 'value' => 'info@maidstonelearning.co.uk', 'type' => 'string', 'description' => 'School contact email'],
            ['key' => 'school_phone', 'value' => '+441622000000', 'type' => 'string', 'description' => 'School contact phone'],
            ['key' => 'school_address', 'value' => 'Maidstone, Kent, UK', 'type' => 'string', 'description' => 'School address'],
            
            // System Settings
            ['key' => 'attendance_required', 'value' => 'true', 'type' => 'boolean', 'description' => 'Require attendance marking'],
            ['key' => 'late_homework_penalty', 'value' => 'true', 'type' => 'boolean', 'description' => 'Apply penalty for late homework'],
            ['key' => 'max_class_capacity', 'value' => '20', 'type' => 'integer', 'description' => 'Maximum students per class'],
            ['key' => 'term_start_date', 'value' => '2024-09-01', 'type' => 'string', 'description' => 'Current term start date'],
            ['key' => 'term_end_date', 'value' => '2025-07-15', 'type' => 'string', 'description' => 'Current term end date'],
            
            // SMS Settings
            ['key' => 'sms_enabled', 'value' => 'true', 'type' => 'boolean', 'description' => 'Enable SMS notifications'],
            ['key' => 'sms_provider', 'value' => 'twilio', 'type' => 'string', 'description' => 'SMS provider name'],
            
            // Email Settings
            ['key' => 'email_enabled', 'value' => 'true', 'type' => 'boolean', 'description' => 'Enable email notifications'],
            ['key' => 'admin_notification_email', 'value' => 'admin@maidstonelearning.co.uk', 'type' => 'string', 'description' => 'Admin notification email'],
        ];

        foreach ($settings as $setting) {
            SystemSetting::create($setting);
        }

        $this->command->info('✓ System settings created successfully!');
    }
}