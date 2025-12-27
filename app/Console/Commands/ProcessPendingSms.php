<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Services\SmsService;
use App\Models\PendingSms;
use Illuminate\Support\Facades\Log;

class ProcessPendingSms extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'sms:process-pending 
                            {--limit=10 : Number of SMS to process per run}
                            {--force : Force processing even if SMS service is not configured}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Process pending SMS messages queued for sending';

    /**
     * Execute the console command.
     */
    public function handle(SmsService $smsService)
    {
        $this->info('Starting SMS processing...');
        
        // Check if SMS service is configured
        if (!$smsService->isConfigured() && !$this->option('force')) {
            $this->error('SMS service is not configured. Use --force to override.');
            return 1;
        }
        
        $limit = (int) $this->option('limit');
        
        // Get pending SMS
        $pending = PendingSms::where('status', 'pending')
            ->where('scheduled_at', '<=', now())
            ->where('attempts', '<', 3)
            ->limit($limit)
            ->get();
        
        if ($pending->isEmpty()) {
            $this->info('No pending SMS to process.');
            return 0;
        }
        
        $this->info("Found {$pending->count()} pending SMS message(s).");
        
        $sent = 0;
        $failed = 0;
        
        // Process each SMS
        foreach ($pending as $sms) {
            $this->line("Processing SMS #{$sms->id} to {$sms->phone_number}...");
            
            $result = $smsService->sendImmediate(
                $sms->phone_number,
                $sms->message_content,
                $sms->message_type
            );
            
            if ($result['success']) {
                $sms->update(['status' => 'sent']);
                $this->info("  ✓ SMS sent successfully (SID: {$result['sid']})");
                $sent++;
            } else {
                $sms->increment('attempts');
                
                if ($sms->attempts >= 3) {
                    $sms->update(['status' => 'failed']);
                    $this->error("  ✗ SMS failed after 3 attempts: {$result['error']}");
                    $failed++;
                } else {
                    $this->warn("  ⚠ SMS failed (Attempt {$sms->attempts}/3): {$result['error']}");
                }
            }
        }
        
        // Summary
        $this->newLine();
        $this->info('=== SMS Processing Summary ===');
        $this->info("Processed: {$pending->count()}");
        $this->info("Sent: {$sent}");
        $this->info("Failed: {$failed}");
        $this->info("Credit Balance: £" . number_format($smsService->getCreditBalance(), 2));
        
        // Check for low balance
        if ($smsService->getCreditBalance() < 10) {
            $this->warn('⚠ Warning: Low SMS credit balance! Please top up.');
        }
        
        Log::info("SMS processing completed: {$sent} sent, {$failed} failed");
        
        return 0;
    }
}