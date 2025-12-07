<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\User;
use Illuminate\Support\Facades\Hash;

class TeacherSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {

        // Create default teacher user
        $teacher = User::create([
            'name' => 'MLC Teacher',
            'email' => 'teacher@maidstonelearning.co.uk',
            'password' => Hash::make('password'), // Change in production!
            'role' => 'teacher',
            'phone' => '+447700900002',
            'email_verified_at' => now(),
        ]);

        // Assign teacher role
        $teacher->assignRole('teacher');

        $this->command->info('✓ Teacher user created successfully!');
        $this->command->info('  Email: teacher@maidstonelearning.co.uk');
        $this->command->info('  Password: password');
        $this->command->warn('⚠ IMPORTANT: Change this password immediately after first login!');
    }
}
