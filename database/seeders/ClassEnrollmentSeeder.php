<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\ClassEnrollment;
use App\Models\Student;
use App\Models\ClassModel;
use Carbon\Carbon;

class ClassEnrollmentSeeder extends Seeder
{
    public function run(): void
    {
        $students = Student::where('status', 'active')->get();
        $classes = ClassModel::all();
        
        if ($students->isEmpty() || $classes->isEmpty()) {
            $this->command->error('No students or classes found!');
            return;
        }

        foreach ($students as $student) {
            // Each student enrolls in 2-4 classes
            $numClasses = rand(2, 4);
            $selectedClasses = $classes->random($numClasses);
            
            foreach ($selectedClasses as $class) {
                // Check current enrollment count manually
                $currentEnrollment = ClassEnrollment::where('class_id', $class->id)
                    ->where('status', 'active')
                    ->count();
                
                // Check if class is not full
                if ($currentEnrollment < $class->capacity) {
                    // Check if student is not already enrolled
                    $alreadyEnrolled = ClassEnrollment::where('student_id', $student->id)
                        ->where('class_id', $class->id)
                        ->where('status', 'active')
                        ->exists();
                    
                    if (!$alreadyEnrolled) {
                        ClassEnrollment::create([
                            'student_id' => $student->id,
                            'class_id' => $class->id,
                            'enrollment_date' => Carbon::now()->subDays(rand(1, 60)),
                            'status' => 'active',
                        ]);
                    }
                }
            }
        }

        $this->command->info('✓ Class enrollments created successfully!');
    }
}