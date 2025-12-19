<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Attendance;
use App\Models\ClassEnrollment;
use App\Models\Schedule;
use Carbon\Carbon;

class AttendanceSeeder extends Seeder
{
    public function run(): void
    {
        $enrollments = ClassEnrollment::where('status', 'active')
            ->with(['class.schedules', 'class.teacher'])
            ->get();
        
        if ($enrollments->isEmpty()) {
            $this->command->error('No enrollments found!');
            return;
        }

        // Create attendance for the last 4 weeks
        $startDate = Carbon::now()->subWeeks(4)->startOfWeek();
        $endDate = Carbon::now();

        foreach ($enrollments as $enrollment) {
            $schedules = $enrollment->class->schedules;
            
            if ($schedules->isEmpty()) continue;

            $currentDate = $startDate->copy();
            
            while ($currentDate->lte($endDate)) {
                foreach ($schedules as $schedule) {
                    // Check if current date matches schedule day
                    if ($currentDate->format('l') === $schedule->day_of_week) {
                        // 85% attendance rate
                        $status = rand(1, 100) <= 85 ? 'present' : (rand(0, 1) ? 'absent' : 'late');
                        
                        Attendance::create([
                            'student_id' => $enrollment->student_id,
                            'class_id' => $enrollment->class_id,
                            'schedule_id' => $schedule->id,
                            'date' => $currentDate->toDateString(),
                            'status' => $status,
                            'marked_by' => $enrollment->class->teacher_id,
                            'notes' => $status === 'absent' ? 'Illness' : null,
                        ]);
                    }
                }
                $currentDate->addDay();
            }
        }

        $this->command->info('✓ Attendance records created successfully!');
    }
}