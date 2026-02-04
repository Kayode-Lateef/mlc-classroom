<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        $this->call([
            RolesAndPermissionsSeeder::class,
            SuperAdminSeeder::class,
            AdminUserSeeder::class,
            TeacherSeeder::class,
            ParentSeeder::class,
            StudentSeeder::class,
            ClassSeeder::class,
            ScheduleSeeder::class,
            ClassEnrollmentSeeder::class,
            AttendanceSeeder::class,
            ProgressSheetSeeder::class,
            HomeworkSeeder::class,
            LearningResourceSeeder::class,
            SystemSettingsSeeder::class,
            
        ]);
    }
}