<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Services\NotificationService;
use App\Models\PendingEmail;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Mail;

class ProcessPendingEmails extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'email:process-pending 
                            {--limit=5 : Number of emails to process per run}
                            {--force : Force processing even if mail is not configured}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Process pending email messages queued for sending';

    /**
     * Execute the console command.
     */
    public function handle(NotificationService $notificationService)
    {
        $this->info('Starting email processing...');
        
        $limit = (int) $this->option('limit');
        
        // Get pending emails
        $pending = PendingEmail::where('status', 'pending')
            ->where('scheduled_at', '<=', now())
            ->where('attempts', '<', 3)
            ->limit($limit)
            ->get();
        
        if ($pending->isEmpty()) {
            $this->info('No pending emails to process.');
            return 0;
        }
        
        $this->info("Found {$pending->count()} pending email(s).");
        
        $sent = 0;
        $failed = 0;
        
        // Process each email
        foreach ($pending as $email) {
            $this->line("Processing email #{$email->id} to {$email->email}...");
            
            try {
                Mail::send('emails.notification', [
                    'subject' => $email->subject,
                    'message' => $email->body,
                    'data' => [],
                ], function ($message) use ($email) {
                    $message->to($email->email)
                           ->subject($email->subject);
                });
                
                $email->update(['status' => 'sent']);
                $this->info("  ✓ Email sent successfully");
                $sent++;
                
            } catch (\Exception $e) {
                $email->increment('attempts');
                
                if ($email->attempts >= 3) {
                    $email->update(['status' => 'failed']);
                    $this->error("  ✗ Email failed after 3 attempts: {$e->getMessage()}");
                    $failed++;
                } else {
                    $this->warn("  ⚠ Email failed (Attempt {$email->attempts}/3): {$e->getMessage()}");
                }
                
                Log::error('Email send error: ' . $e->getMessage());
            }
        }
        
        // Summary
        $this->newLine();
        $this->info('=== Email Processing Summary ===');
        $this->info("Processed: {$pending->count()}");
        $this->info("Sent: {$sent}");
        $this->info("Failed: {$failed}");
        
        Log::info("Email processing completed: {$sent} sent, {$failed} failed");
        
        return 0;
    }
}