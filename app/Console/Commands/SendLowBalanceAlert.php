<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Models\SmsConfiguration;
use App\Models\User;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Log;

class SendLowBalanceAlert extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'sms:check-balance';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Check SMS credit balance and alert admins if low';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $config = SmsConfiguration::where('is_active', true)->first();
        
        if (!$config) {
            $this->warn('No active SMS configuration found.');
            return 1;
        }
        
        $balance = $config->credit_balance;
        $threshold = $config->low_balance_threshold;
        
        $this->info("Current SMS Balance: £{$balance}");
        $this->info("Low Balance Threshold: £{$threshold}");
        
        if ($balance < $threshold) {
            $this->warn('⚠ SMS credit balance is LOW!');
            
            // Get all admins and superadmins
            $admins = User::whereIn('role', ['admin', 'superadmin'])->get();
            
            if ($admins->isEmpty()) {
                $this->error('No admins found to send alert to.');
                return 1;
            }
            
            // Send email alerts
            foreach ($admins as $admin) {
                try {
                    Mail::send('emails.low-balance-alert', [
                        'balance' => $balance,
                        'threshold' => $threshold,
                        'admin_name' => $admin->name,
                    ], function ($message) use ($admin) {
                        $message->to($admin->email)
                               ->subject('⚠ Low SMS Credit Balance Alert');
                    });
                    
                    $this->info("Alert sent to {$admin->name} ({$admin->email})");
                    
                } catch (\Exception $e) {
                    $this->error("Failed to send alert to {$admin->email}: {$e->getMessage()}");
                    Log::error('Low balance alert failed: ' . $e->getMessage());
                }
            }
            
            Log::warning("Low SMS balance alert sent: £{$balance} (threshold: £{$threshold})");
            
        } else {
            $this->info('✓ Balance is sufficient.');
        }
        
        return 0;
    }
}