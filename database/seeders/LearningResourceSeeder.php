<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\LearningResource;
use App\Models\ClassModel;
use App\Models\User;

class LearningResourceSeeder extends Seeder
{
    public function run(): void
    {
        $teachers = User::where('role', 'teacher')->get();
        $classes = ClassModel::all();
        
        // Only use resource types that match the ENUM in migration
        $resourceTypes = ['pdf', 'video', 'link', 'image', 'document'];
        
        // General resources (not class-specific)
        $generalResources = [
            ['title' => 'Study Skills Guide', 'subject' => 'General', 'type' => 'pdf'],
            ['title' => 'Time Management Tips', 'subject' => 'General', 'type' => 'pdf'],
            ['title' => 'Exam Preparation Strategies', 'subject' => 'General', 'type' => 'video'],
            ['title' => 'Note-Taking Techniques', 'subject' => 'General', 'type' => 'document'],
            ['title' => 'Revision Timetable Template', 'subject' => 'General', 'type' => 'document'],
        ];

        foreach ($generalResources as $resource) {
            LearningResource::create([
                'title' => $resource['title'],
                'description' => "Helpful {$resource['title']} for all students",
                'file_path' => 'resources/' . strtolower(str_replace(' ', '_', $resource['title'])) . '.' . $resource['type'],
                'resource_type' => $resource['type'],
                'uploaded_by' => $teachers->random()->id,
                'subject' => $resource['subject'],
            ]);
        }

        // Class-specific resources
        foreach ($classes as $class) {
            $numResources = rand(2, 5);
            
            for ($i = 0; $i < $numResources; $i++) {
                $type = $resourceTypes[array_rand($resourceTypes)];
                
                LearningResource::create([
                    'title' => "{$class->subject} - {$class->level} Resource " . ($i + 1),
                    'description' => "Educational resource for {$class->name}",
                    'file_path' => 'resources/class_' . $class->id . '_resource_' . ($i + 1) . '.' . $type,
                    'resource_type' => $type,
                    'uploaded_by' => $class->teacher_id,
                    'class_id' => $class->id,
                    'subject' => $class->subject,
                ]);
            }
        }

        $this->command->info('✓ Learning resources created successfully!');
    }
}
