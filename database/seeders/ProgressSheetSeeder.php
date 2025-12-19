<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\ProgressSheet;
use App\Models\ProgressNote;
use App\Models\ClassModel;
use App\Models\Schedule;
use Carbon\Carbon;

class ProgressSheetSeeder extends Seeder
{
    public function run(): void
    {
        $classes = ClassModel::with(['schedules', 'teacher', 'enrollments.student'])->get();
        
        $topics = [
            'Mathematics' => ['Algebra', 'Geometry', 'Fractions', 'Decimals', 'Statistics'],
            'English' => ['Reading Comprehension', 'Creative Writing', 'Grammar', 'Poetry', 'Essay Writing'],
            'Science' => ['Forces and Motion', 'Chemical Reactions', 'Cells and Organisms', 'Energy', 'Electricity'],
        ];

        foreach ($classes as $class) {
            if ($class->schedules->isEmpty()) continue;
            
            // Create progress sheets for last 4 weeks
            for ($week = 0; $week < 4; $week++) {
                $schedule = $class->schedules->first();
                $date = Carbon::now()->subWeeks($week);
                
                $subjectTopics = $topics[$class->subject] ?? ['General Topic'];
                $topic = $subjectTopics[array_rand($subjectTopics)];
                
                $progressSheet = ProgressSheet::create([
                    'class_id' => $class->id,
                    'schedule_id' => $schedule->id,
                    'date' => $date->toDateString(),
                    'objective' => "Understand and apply concepts of {$topic}",
                    'topic' => $topic,
                    'teacher_id' => $class->teacher_id,
                    'notes' => "Class covered {$topic} with practical examples and exercises.",
                ]);

                // Create progress notes for each enrolled student
                foreach ($class->enrollments as $enrollment) {
                    if ($enrollment->status === 'active') {
                        $performances = ['excellent', 'good', 'average', 'struggling'];
                        $performance = $performances[array_rand($performances)];
                        
                        ProgressNote::create([
                            'progress_sheet_id' => $progressSheet->id,
                            'student_id' => $enrollment->student_id,
                            'performance' => $performance,
                            'notes' => $this->generatePerformanceNote($performance, $topic),
                        ]);
                    }
                }
            }
        }

        $this->command->info('✓ Progress sheets and notes created successfully!');
    }

    private function generatePerformanceNote($performance, $topic): string
    {
        $notes = [
            'excellent' => "Excellent grasp of {$topic}. Shows strong understanding and participates actively.",
            'good' => "Good understanding of {$topic}. Completes work with minimal support.",
            'average' => "Adequate understanding of {$topic}. May need additional practice.",
            'struggling' => "Requires additional support with {$topic}. Recommend extra tutoring.",
        ];

        return $notes[$performance] ?? '';
    }
}