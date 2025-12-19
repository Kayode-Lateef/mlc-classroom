<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Student;
use App\Models\User;
use Carbon\Carbon;

class StudentSeeder extends Seeder
{
    public function run(): void
    {
        $parents = User::where('role', 'parent')->get();
        
        if ($parents->isEmpty()) {
            $this->command->error('No parents found. Run ParentSeeder first!');
            return;
        }

        $firstNames = [
            'Oliver', 'George', 'Harry', 'Jack', 'Jacob', 'Noah', 'Charlie', 'Muhammad', 'Thomas', 'Oscar',
            'William', 'James', 'Henry', 'Leo', 'Alfie', 'Freddie', 'Archie', 'Isaac', 'Joshua', 'Alexander',
            'Amelia', 'Olivia', 'Isla', 'Emily', 'Poppy', 'Ava', 'Isabella', 'Jessica', 'Lily', 'Sophie',
            'Grace', 'Sophia', 'Mia', 'Evie', 'Ruby', 'Ella', 'Scarlett', 'Isabelle', 'Chloe', 'Sienna',
            'Freya', 'Phoebe', 'Charlotte', 'Daisy', 'Alice', 'Florence', 'Eva', 'Sofia', 'Millie', 'Lucy'
        ];

        $lastNames = [
            'Smith', 'Johnson', 'Williams', 'Brown', 'Jones', 'Garcia', 'Miller', 'Davis', 'Rodriguez', 'Martinez',
            'Hernandez', 'Lopez', 'Gonzalez', 'Wilson', 'Anderson', 'Thomas', 'Taylor', 'Moore', 'Jackson', 'Martin',
            'Lee', 'Thompson', 'White', 'Harris', 'Sanchez', 'Clark', 'Ramirez', 'Lewis', 'Robinson', 'Walker'
        ];

        $students = [];
        
        // Create 50 students
        for ($i = 0; $i < 50; $i++) {
            $parent = $parents->random();
            $firstName = $firstNames[array_rand($firstNames)];
            $lastName = $lastNames[array_rand($lastNames)];
            
            // Age range: 5-16 years old
            $age = rand(5, 16);
            $dateOfBirth = Carbon::now()->subYears($age)->subDays(rand(0, 364));
            
            $students[] = [
                'first_name' => $firstName,
                'last_name' => $lastName,
                'date_of_birth' => $dateOfBirth,
                'parent_id' => $parent->id,
                'enrollment_date' => Carbon::now()->subDays(rand(30, 365)),
                'status' => 'active',
                'emergency_contact' => $parent->name,
                'emergency_phone' => $parent->phone,
                'medical_info' => rand(0, 5) == 0 ? 'Asthma - requires inhaler' : null,
                'created_at' => now(),
                'updated_at' => now(),
            ];
        }

        Student::insert($students);

        $this->command->info('✓ 50 students created successfully!');
    }
}