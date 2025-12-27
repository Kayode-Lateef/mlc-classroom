<?php

namespace App\Console;

use Illuminate\Console\Scheduling\Schedule;
use Illuminate\Foundation\Console\Kernel as ConsoleKernel;

class Kernel extends ConsoleKernel
{
    /**
     * Define the application's command schedule.
     */
    protected function schedule(Schedule $schedule): void
    {
        // Process pending SMS every 5 minutes
        $schedule->command('sms:process-pending')
            ->everyFiveMinutes()
            ->withoutOverlapping()
            ->appendOutputTo(storage_path('logs/sms-processing.log'));
        
        // Process pending emails every 5 minutes
        $schedule->command('email:process-pending')
            ->everyFiveMinutes()
            ->withoutOverlapping()
            ->appendOutputTo(storage_path('logs/email-processing.log'));
        
        // Check SMS balance daily at 9 AM
        $schedule->command('sms:check-balance')
            ->dailyAt('09:00')
            ->appendOutputTo(storage_path('logs/balance-check.log'));
        
        // Cleanup old logs weekly on Sunday at 2 AM
        $schedule->command('logs:cleanup --days=90')
            ->weekly()
            ->sundays()
            ->at('02:00')
            ->appendOutputTo(storage_path('logs/cleanup.log'));
    }

    /**
     * Register the commands for the application.
     */
    protected function commands(): void
    {
        $this->load(__DIR__.'/Commands');

        require base_path('routes/console.php');
    }
}