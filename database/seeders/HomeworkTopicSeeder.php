<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\HomeworkTopic;

class HomeworkTopicSeeder extends Seeder
{
    public function run(): void
    {
        $topics = [
            // Mathematics
            ['name' => 'Algebra', 'subject' => 'Mathematics', 'description' => 'Algebraic equations and expressions'],
            ['name' => 'Geometry', 'subject' => 'Mathematics', 'description' => 'Shapes, angles, and spatial reasoning'],
            ['name' => 'Fractions & Decimals', 'subject' => 'Mathematics', 'description' => 'Working with fractions and decimal numbers'],
            ['name' => 'Statistics', 'subject' => 'Mathematics', 'description' => 'Data analysis and probability'],
            
            // English
            ['name' => 'Reading Comprehension', 'subject' => 'English', 'description' => 'Understanding and analyzing texts'],
            ['name' => 'Creative Writing', 'subject' => 'English', 'description' => 'Story writing and composition'],
            ['name' => 'Grammar', 'subject' => 'English', 'description' => 'Parts of speech, sentence structure'],
            ['name' => 'Poetry', 'subject' => 'English', 'description' => 'Analyzing and writing poetry'],
            
            // Science
            ['name' => 'Forces & Motion', 'subject' => 'Science', 'description' => 'Physics principles'],
            ['name' => 'Chemical Reactions', 'subject' => 'Science', 'description' => 'Chemistry experiments and theory'],
            ['name' => 'Living Organisms', 'subject' => 'Science', 'description' => 'Biology and life sciences'],
            ['name' => 'Energy', 'subject' => 'Science', 'description' => 'Types and transfer of energy'],
        ];

        foreach ($topics as $topic) {
            HomeworkTopic::create($topic);
        }

        $this->command->info('✓ Homework topics seeded successfully!');
    }
}