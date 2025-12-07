<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\User;
use Illuminate\Support\Facades\Hash;

class ParentSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {

        // Create default parent user
        $parent = User::create([
            'name' => 'MLC Parent',
            'email' => 'parent@maidstonelearning.co.uk',
            'password' => Hash::make('password'), // Change in production!
            'role' => 'parent',
            'phone' => '+447700900003',
            'email_verified_at' => now(),
        ]);

        // Assign parent role
        $parent->assignRole('parent');

        $this->command->info('✓ Parent user created successfully!');
        $this->command->info('  Email: parent@maidstonelearning.co.uk');
        $this->command->info('  Password: password');
        $this->command->warn('⚠ IMPORTANT: Change this password immediately after first login!');
    
    }
}
