<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\HomeworkAssignment;
use App\Models\HomeworkSubmission;
use App\Models\ClassModel;
use App\Models\ProgressSheet;
use Carbon\Carbon;

class HomeworkSeeder extends Seeder
{
    public function run(): void
    {
        $classes = ClassModel::with(['teacher', 'progressSheets', 'enrollments'])->get();
        
        foreach ($classes as $class) {
            // Create 3-5 homework assignments per class
            for ($i = 0; $i < rand(3, 5); $i++) {
                $assignedDate = Carbon::now()->subDays(rand(7, 30));
                $dueDate = $assignedDate->copy()->addDays(rand(7, 14));
                
                $progressSheet = $class->progressSheets->random();
                
                $homework = HomeworkAssignment::create([
                    'class_id' => $class->id,
                    'progress_sheet_id' => $progressSheet ? $progressSheet->id : null,
                    'title' => "Homework: {$progressSheet->topic}",
                    'description' => "Complete exercises related to {$progressSheet->topic}. Show all working.",
                    'assigned_date' => $assignedDate,
                    'due_date' => $dueDate,
                    'teacher_id' => $class->teacher_id,
                ]);

                // Create submissions for enrolled students
                foreach ($class->enrollments as $enrollment) {
                    if ($enrollment->status === 'active') {
                        // 70% submission rate
                        if (rand(1, 100) <= 70) {
                            $submittedDate = $assignedDate->copy()->addDays(rand(1, 10));
                            $isLate = $submittedDate->gt($dueDate);
                            
                            // 60% of submissions are graded
                            $isGraded = rand(1, 100) <= 60;
                            
                            HomeworkSubmission::create([
                                'homework_assignment_id' => $homework->id,
                                'student_id' => $enrollment->student_id,
                                'submitted_date' => $submittedDate,
                                'status' => $isGraded ? 'graded' : 'submitted',
                                'grade' => $isGraded ? rand(60, 100) : null,
                                'teacher_comments' => $isGraded ? ($isLate ? 'Late submission. Good effort.' : 'Well done!') : null,
                                'graded_at' => $isGraded ? $submittedDate->addDays(rand(1, 3)) : null,
                            ]);
                        }
                    }
                }
            }
        }

        $this->command->info('✓ Homework assignments and submissions created successfully!');
    }
}
