<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\ClassModel;
use App\Models\User;

class ClassSeeder extends Seeder
{
    public function run(): void
    {
        $teachers = User::where('role', 'teacher')->get();
        
        if ($teachers->isEmpty()) {
            $this->command->error('No teachers found. Run TeacherSeeder first!');
            return;
        }

        $classes = [
            // Mathematics Classes
            ['name' => 'Maths Year 5', 'subject' => 'Mathematics', 'level' => 'Year 5', 'room' => 'M101', 'capacity' => 15],
            ['name' => 'Maths Year 6', 'subject' => 'Mathematics', 'level' => 'Year 6', 'room' => 'M102', 'capacity' => 15],
            ['name' => 'Maths 11+ Preparation', 'subject' => 'Mathematics', 'level' => '11+', 'room' => 'M103', 'capacity' => 12],
            ['name' => 'Maths GCSE Foundation', 'subject' => 'Mathematics', 'level' => 'GCSE', 'room' => 'M104', 'capacity' => 18],
            ['name' => 'Maths GCSE Higher', 'subject' => 'Mathematics', 'level' => 'GCSE', 'room' => 'M105', 'capacity' => 16],
            
            // English Classes
            ['name' => 'English Year 5', 'subject' => 'English', 'level' => 'Year 5', 'room' => 'E101', 'capacity' => 15],
            ['name' => 'English Year 6', 'subject' => 'English', 'level' => 'Year 6', 'room' => 'E102', 'capacity' => 15],
            ['name' => 'English 11+ Preparation', 'subject' => 'English', 'level' => '11+', 'room' => 'E103', 'capacity' => 12],
            ['name' => 'English GCSE Literature', 'subject' => 'English', 'level' => 'GCSE', 'room' => 'E104', 'capacity' => 18],
            ['name' => 'English GCSE Language', 'subject' => 'English', 'level' => 'GCSE', 'room' => 'E105', 'capacity' => 16],
            
            // Science Classes
            ['name' => 'Science Year 5', 'subject' => 'Science', 'level' => 'Year 5', 'room' => 'S101', 'capacity' => 15],
            ['name' => 'Science Year 6', 'subject' => 'Science', 'level' => 'Year 6', 'room' => 'S102', 'capacity' => 15],
            ['name' => 'Biology GCSE', 'subject' => 'Science', 'level' => 'GCSE', 'room' => 'S103', 'capacity' => 16],
            ['name' => 'Chemistry GCSE', 'subject' => 'Science', 'level' => 'GCSE', 'room' => 'S104', 'capacity' => 16],
            ['name' => 'Physics GCSE', 'subject' => 'Science', 'level' => 'GCSE', 'room' => 'S105', 'capacity' => 16],
        ];

        foreach ($classes as $classData) {
            ClassModel::create([
                'name' => $classData['name'],
                'subject' => $classData['subject'],
                'level' => $classData['level'],
                'room_number' => $classData['room'],
                'teacher_id' => $teachers->random()->id,
                'capacity' => $classData['capacity'],
                'description' => "Comprehensive {$classData['subject']} course for {$classData['level']} students.",
            ]);
        }

        $this->command->info('✓ 15 classes created successfully!');
    }
}