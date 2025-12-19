<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Schedule;
use App\Models\ClassModel;

class ScheduleSeeder extends Seeder
{
    public function run(): void
    {
        $classes = ClassModel::all();
        
        if ($classes->isEmpty()) {
            $this->command->error('No classes found. Run ClassSeeder first!');
            return;
        }

        $days = ['Monday', 'Tuesday', 'Wednesday', 'Thursday', 'Friday'];
        $timeSlots = [
            ['09:00:00', '10:30:00'],
            ['11:00:00', '12:30:00'],
            ['14:00:00', '15:30:00'],
            ['16:00:00', '17:30:00'],
        ];

        foreach ($classes as $class) {
            // Each class meets 2 times per week
            $selectedDays = array_rand(array_flip($days), 2);
            
            foreach ($selectedDays as $day) {
                $timeSlot = $timeSlots[array_rand($timeSlots)];
                
                Schedule::create([
                    'class_id' => $class->id,
                    'day_of_week' => $day,
                    'start_time' => $timeSlot[0],
                    'end_time' => $timeSlot[1],
                    'recurring' => true,
                ]);
            }
        }
    }
}