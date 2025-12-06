<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\User;
use Illuminate\Support\Facades\Hash;

class AdminUserSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Create default admin user
        $admin = User::create([
            'name' => 'MLC Admin',
            'email' => 'admin@maidstonelearning.co.uk',
            'password' => Hash::make('password'), // Change in production!
            'role' => 'admin',
            'phone' => '+447700900000',
            'email_verified_at' => now(),
        ]);

        // Assign admin role
        $admin->assignRole('admin');

        $this->command->info('✓ Admin user created successfully!');
        $this->command->info('  Email: admin@maidstonelearning.co.uk');
        $this->command->info('  Password: password');
        $this->command->warn('⚠ IMPORTANT: Change this password immediately after first login!');
    }
}