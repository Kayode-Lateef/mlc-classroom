<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\User;
use Illuminate\Support\Facades\Hash;

class ParentSeeder extends Seeder
{
    public function run(): void
    {
        $parents = [
            ['name' => 'MLC Parent', 'email' => 'parent@example.com', 'phone' => '+447700900200'],
            ['name' => 'John Smith', 'email' => 'john.smith@example.com', 'phone' => '+447700900201'],
            ['name' => 'Mary Johnson', 'email' => 'mary.johnson@example.com', 'phone' => '+447700900202'],
            ['name' => 'Peter Williams', 'email' => 'peter.williams@example.com', 'phone' => '+447700900203'],
            ['name' => 'Susan Brown', 'email' => 'susan.brown@example.com', 'phone' => '+447700900204'],
            ['name' => 'David Jones', 'email' => 'david.jones@example.com', 'phone' => '+447700900205'],
            ['name' => 'Jennifer Davis', 'email' => 'jennifer.davis@example.com', 'phone' => '+447700900206'],
            ['name' => 'Michael Miller', 'email' => 'michael.miller@example.com', 'phone' => '+447700900207'],
            ['name' => 'Patricia Wilson', 'email' => 'patricia.wilson@example.com', 'phone' => '+447700900208'],
            ['name' => 'Robert Moore', 'email' => 'robert.moore@example.com', 'phone' => '+447700900209'],
            ['name' => 'Linda Taylor', 'email' => 'linda.taylor@example.com', 'phone' => '+447700900210'],
            ['name' => 'James Anderson', 'email' => 'james.anderson@example.com', 'phone' => '+447700900211'],
            ['name' => 'Barbara Thomas', 'email' => 'barbara.thomas@example.com', 'phone' => '+447700900212'],
            ['name' => 'William Jackson', 'email' => 'william.jackson@example.com', 'phone' => '+447700900213'],
            ['name' => 'Elizabeth White', 'email' => 'elizabeth.white@example.com', 'phone' => '+447700900214'],
            ['name' => 'Richard Harris', 'email' => 'richard.harris@example.com', 'phone' => '+447700900215'],
            ['name' => 'Sarah Martin', 'email' => 'sarah.martin@example.com', 'phone' => '+447700900216'],
            ['name' => 'Charles Thompson', 'email' => 'charles.thompson@example.com', 'phone' => '+447700900217'],
            ['name' => 'Nancy Garcia', 'email' => 'nancy.garcia@example.com', 'phone' => '+447700900218'],
            ['name' => 'Daniel Martinez', 'email' => 'daniel.martinez@example.com', 'phone' => '+447700900219'],
        ];

        foreach ($parents as $parentData) {
            $parent = User::create([
                'name' => $parentData['name'],
                'email' => $parentData['email'],
                'password' => Hash::make('password'),
                'role' => 'parent',
                'phone' => $parentData['phone'],
                'email_verified_at' => now(),
            ]);

            $parent->assignRole('parent');
        }

        $this->command->info('✓ 21 parents created successfully!');
    }
}