<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\User;
use Illuminate\Support\Facades\Hash;

class TeacherSeeder extends Seeder
{
    public function run(): void
    {
        $teachers = [
            [
                'name' => 'MLC Teacher',
                'email' => 'teacher@maidstonelearning.co.uk',
                'phone' => '+447700900002',
            ],
            [
                'name' => 'Sarah Johnson',
                'email' => 'sarah.johnson@maidstonelearning.co.uk',
                'phone' => '+447700900101',
            ],
            [
                'name' => 'Michael Brown',
                'email' => 'michael.brown@maidstonelearning.co.uk',
                'phone' => '+447700900102',
            ],
            [
                'name' => 'Emma Wilson',
                'email' => 'emma.wilson@maidstonelearning.co.uk',
                'phone' => '+447700900103',
            ],
            [
                'name' => 'David Thompson',
                'email' => 'david.thompson@maidstonelearning.co.uk',
                'phone' => '+447700900104',
            ],
            [
                'name' => 'Lisa Anderson',
                'email' => 'lisa.anderson@maidstonelearning.co.uk',
                'phone' => '+447700900105',
            ],
            [
                'name' => 'James Taylor',
                'email' => 'james.taylor@maidstonelearning.co.uk',
                'phone' => '+447700900106',
            ],
            [
                'name' => 'Rachel Martinez',
                'email' => 'rachel.martinez@maidstonelearning.co.uk',
                'phone' => '+447700900107',
            ],
            [
                'name' => 'Thomas White',
                'email' => 'thomas.white@maidstonelearning.co.uk',
                'phone' => '+447700900108',
            ],
            [
                'name' => 'Jennifer Garcia',
                'email' => 'jennifer.garcia@maidstonelearning.co.uk',
                'phone' => '+447700900109',
            ],
            [
                'name' => 'Robert Lee',
                'email' => 'robert.lee@maidstonelearning.co.uk',
                'phone' => '+447700900110',
            ],
        ];

        foreach ($teachers as $teacherData) {
            $teacher = User::create([
                'name' => $teacherData['name'],
                'email' => $teacherData['email'],
                'password' => Hash::make('password'),
                'role' => 'teacher',
                'phone' => $teacherData['phone'],
                'email_verified_at' => now(),
            ]);

            $teacher->assignRole('teacher');
        }

        $this->command->info('✓ 11 teachers created successfully!');
    }
}
