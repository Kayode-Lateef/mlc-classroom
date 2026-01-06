<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Models\PendingEmail;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Log;

class ProcessPendingEmails extends Command
{
    /**
     * The name and signature of the console command.
     */
    protected $signature = 'email:process-pending 
                            {--limit=10 : Number of emails to process per run}
                            {--force : Force processing even if none are scheduled}';

    /**
     * The console command description.
     */
    protected $description = 'Process pending emails queued for sending';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $this->info('━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━');
        $this->info('  MLC Email Processing Started');
        $this->info('━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━');
        $this->newLine();
        
        $limit = (int) $this->option('limit');
        
        // Get pending emails
        $pending = PendingEmail::pending()->limit($limit)->get();
        
        if ($pending->isEmpty()) {
            $this->info('✓ No pending emails to process.');
            $this->newLine();
            return 0;
        }
        
        $this->info("Found {$pending->count()} pending email(s)");
        $this->newLine();
        
        $sent = 0;
        $failed = 0;
        
        // Progress bar
        $bar = $this->output->createProgressBar($pending->count());
        $bar->start();
        
        foreach ($pending as $email) {
            try {
                // Decode JSON data if it's a string
                $data = is_string($email->data) ? json_decode($email->data, true) : $email->data;
                $data = $data ?? [];
                
                Mail::send('emails.notification', [
                    'title' => $email->subject,
                    'content' => $email->body,
                    'url' => $data['url'] ?? null,
                    'data' => $data,
                    'type' => $data['type'] ?? 'general',
                ], function ($mail) use ($email) {
                    $mail->to($email->email)
                         ->subject($email->subject);
                });
                
                $email->markAsSent();
                $sent++;
                
            } catch (\Exception $e) {
                $errorMessage = $e->getMessage();
                $email->incrementAttempts($errorMessage);
                
                if ($email->attempts >= 3) {
                    $failed++;
                }
                
                Log::error("Email send error for #{$email->id}: " . $errorMessage);
            }
            
            $bar->advance();
        }
        
        $bar->finish();
        $this->newLine(2);
        
        // Summary
        $this->info('━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━');
        $this->info('  Processing Summary');
        $this->info('━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━');
        $this->table(
            ['Status', 'Count'],
            [
                ['✓ Sent Successfully', $sent],
                ['✗ Failed', $failed],
                ['⏳ Remaining Pending', $pending->count() - $sent - $failed],
            ]
        );
        $this->newLine();
        
        return 0;
    }
}