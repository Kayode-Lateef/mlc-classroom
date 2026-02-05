<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Models\SmsConfiguration;
use App\Models\User;
use App\Services\SmsService;
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

    protected $smsService;
    
    public function __construct(SmsService $smsService)
    {
        parent::__construct();
        $this->smsService = $smsService;
    }

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
        
        $provider = $config->provider;
        
        if ($provider === 'voodoo') {
            return $this->checkVoodooBalance($config);
        }
        
        return $this->checkMonetaryBalance($config);
    }
    
    /**
     * Check monetary balance for non-Voodoo providers
     */
    protected function checkMonetaryBalance($config)
    {
        $balance = $config->credit_balance;
        $threshold = $config->low_balance_threshold;
        
        $this->info("Current SMS Balance: £{$balance}");
        $this->info("Low Balance Threshold: £{$threshold}");
        
        if ($balance < $threshold) {
            $this->warn('⚠ SMS credit balance is LOW!');
            $this->sendAlerts("SMS credit balance is low (£{$balance}). Please top up immediately.");
            return 0;
        }
        
        $this->info('✓ Balance is sufficient.');
        return 0;
    }
    
    /**
     * Check Voodoo SMS balance
     */
    protected function checkVoodooBalance($config)
    {
        $this->info('Checking Voodoo SMS balance...');
        
        $result = $this->smsService->checkVoodooBalance();
        
        if (!$result['success']) {
            $this->error('Failed to check Voodoo balance: ' . $result['error']);
            Log::error('Voodoo balance check failed: ' . $result['error']);
            return 1;
        }
        
        $credits = $result['credits_remaining'] ?? 0;
        $monetaryBalance = $result['balance'] ?? 0;
        
        $this->info("Voodoo Credits Remaining: {$credits}");
        $this->info("Voodoo Monetary Balance: $" . $monetaryBalance);
        
        // Set a threshold for Voodoo credits (e.g., 100 credits)
        $creditThreshold = 100;
        $monetaryThreshold = 10; // $10
        
        if ($credits < $creditThreshold || $monetaryBalance < $monetaryThreshold) {
            $this->warn('⚠ Voodoo SMS balance is LOW!');
            
            $message = "Voodoo SMS balance is low:\n";
            $message .= "• Credits remaining: {$credits} (threshold: {$creditThreshold})\n";
            $message .= "• Monetary balance: $" . $monetaryBalance . " (threshold: $" . $monetaryThreshold . ")";
            
            $this->sendAlerts($message);
            return 0;
        }
        
        $this->info('✓ Voodoo balance is sufficient.');
        return 0;
    }
    
    /**
     * Send alert emails to admins
     */
    protected function sendAlerts($message)
    {
        // Get all admins and superadmins
        $admins = User::whereIn('role', ['admin', 'superadmin'])->get();
        
        if ($admins->isEmpty()) {
            $this->error('No admins found to send alert to.');
            return;
        }
        
        // Send email alerts
        foreach ($admins as $admin) {
            try {
                Mail::send('emails.low-balance-alert', [
                    'message' => $message,
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
        
        Log::warning("Low SMS balance alert sent: {$message}");
    }
}