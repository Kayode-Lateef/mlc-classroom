<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\User;
use Illuminate\Support\Facades\Hash;

class SuperAdminSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Create default superadmin user
        $superadmin = User::create([
            'name' => 'MLC Super Admin',
            'email' => 'superadmin@maidstonelearning.co.uk',
            'password' => Hash::make('password'), // Change in production!
            'role' => 'superadmin',
            'phone' => '+447700900001',
            'email_verified_at' => now(),
        ]);

        // Assign superadmin role
        $superadmin->assignRole('superadmin');

        $this->command->info('✓ Superadmin user created successfully!');
        $this->command->info('  Email: superadmin@maidstonelearning.co.uk');
        $this->command->info('  Password: password');
        $this->command->warn('⚠ IMPORTANT: Change this password immediately after first login!');
    }
}