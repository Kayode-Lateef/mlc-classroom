<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Models\SmsLog;
use App\Models\ActivityLog;
use App\Models\PendingSms;
use App\Models\PendingEmail;
use Carbon\Carbon;
use Illuminate\Support\Facades\Log;

class CleanupOldLogs extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'logs:cleanup 
                            {--days=90 : Number of days to keep logs}
                            {--dry-run : Show what would be deleted without actually deleting}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Clean up old logs and processed pending messages to free up database space';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $days = (int) $this->option('days');
        $dryRun = $this->option('dry-run');
        $cutoffDate = Carbon::now()->subDays($days);
        
        $this->info("Cleaning up logs older than {$days} days (before {$cutoffDate->format('Y-m-d')})...");
        
        if ($dryRun) {
            $this->warn('DRY RUN MODE - No data will be deleted');
        }
        
        $totalDeleted = 0;
        
        // Clean up SMS logs
        $smsLogsCount = SmsLog::where('created_at', '<', $cutoffDate)->count();
        if ($smsLogsCount > 0) {
            if (!$dryRun) {
                SmsLog::where('created_at', '<', $cutoffDate)->delete();
            }
            $this->info("SMS Logs: {$smsLogsCount} records " . ($dryRun ? 'would be' : 'were') . " deleted");
            $totalDeleted += $smsLogsCount;
        } else {
            $this->line("SMS Logs: No old records to delete");
        }
        
        // Clean up activity logs
        $activityLogsCount = ActivityLog::where('created_at', '<', $cutoffDate)->count();
        if ($activityLogsCount > 0) {
            if (!$dryRun) {
                ActivityLog::where('created_at', '<', $cutoffDate)->delete();
            }
            $this->info("Activity Logs: {$activityLogsCount} records " . ($dryRun ? 'would be' : 'were') . " deleted");
            $totalDeleted += $activityLogsCount;
        } else {
            $this->line("Activity Logs: No old records to delete");
        }
        
        // Clean up processed pending SMS (keep failed for analysis)
        $pendingSmsCount = PendingSms::where('status', 'sent')
            ->where('updated_at', '<', $cutoffDate)
            ->count();
        if ($pendingSmsCount > 0) {
            if (!$dryRun) {
                PendingSms::where('status', 'sent')
                    ->where('updated_at', '<', $cutoffDate)
                    ->delete();
            }
            $this->info("Pending SMS (sent): {$pendingSmsCount} records " . ($dryRun ? 'would be' : 'were') . " deleted");
            $totalDeleted += $pendingSmsCount;
        } else {
            $this->line("Pending SMS: No old records to delete");
        }
        
        // Clean up processed pending emails (keep failed for analysis)
        $pendingEmailsCount = PendingEmail::where('status', 'sent')
            ->where('updated_at', '<', $cutoffDate)
            ->count();
        if ($pendingEmailsCount > 0) {
            if (!$dryRun) {
                PendingEmail::where('status', 'sent')
                    ->where('updated_at', '<', $cutoffDate)
                    ->delete();
            }
            $this->info("Pending Emails (sent): {$pendingEmailsCount} records " . ($dryRun ? 'would be' : 'were') . " deleted");
            $totalDeleted += $pendingEmailsCount;
        } else {
            $this->line("Pending Emails: No old records to delete");
        }
        
        // Summary
        $this->newLine();
        $this->info('=== Cleanup Summary ===');
        $this->info("Total records " . ($dryRun ? 'that would be' : '') . " deleted: {$totalDeleted}");
        
        if ($dryRun) {
            $this->warn('This was a dry run. Run without --dry-run to actually delete the data.');
        } else {
            Log::info("Log cleanup completed: {$totalDeleted} records deleted");
        }
        
        return 0;
    }
}