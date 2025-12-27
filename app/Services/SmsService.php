<?php

namespace App\Services;

use App\Models\SmsLog;
use App\Models\PendingSms;
use App\Models\SmsConfiguration;
use Twilio\Rest\Client;
use Illuminate\Support\Facades\Log;
use Carbon\Carbon;

class SmsService
{
    protected $client;
    protected $from;
    protected $config;
    
    public function __construct()
    {
        $this->config = SmsConfiguration::where('is_active', true)->first();
        
        if ($this->config && $this->config->provider === 'twilio') {
            $this->client = new Client(
                decrypt($this->config->api_key),
                decrypt($this->config->api_secret)
            );
            $this->from = $this->config->sender_id;
        }
    }
    
    /**
     * Send SMS immediately (synchronous)
     * Use for 1-3 urgent SMS only to avoid timeout
     * 
     * @param string $to UK phone number (+44...)
     * @param string $message SMS content
     * @param string $type Message type (absence, homework, etc.)
     * @return array ['success' => bool, 'sid' => string|null, 'error' => string|null]
     */
    public function sendImmediate($to, $message, $type = 'general')
    {
        if (!$this->client) {
            Log::error('SMS Service: Twilio not configured');
            return ['success' => false, 'error' => 'SMS service not configured'];
        }
        
        // Validate UK phone number
        if (!$this->isValidUKPhone($to)) {
            Log::error('SMS Service: Invalid UK phone number - ' . $to);
            return ['success' => false, 'error' => 'Invalid UK phone number'];
        }
        
        // Check credit balance
        if ($this->config->credit_balance < $this->config->low_balance_threshold) {
            Log::warning('SMS Service: Low credit balance - £' . $this->config->credit_balance);
        }
        
        try {
            $result = $this->client->messages->create($to, [
                'from' => $this->from,
                'body' => $message
            ]);
            
            // Calculate cost (approximate)
            $cost = 0.04; // £0.04 per SMS for UK
            
            // Log success
            SmsLog::create([
                'user_id' => auth()->id(),
                'phone_number' => $to,
                'message_type' => $type,
                'message_content' => $message,
                'provider' => 'twilio',
                'provider_message_id' => $result->sid,
                'status' => 'sent',
                'cost' => $cost,
                'sent_at' => now(),
            ]);
            
            // Deduct from balance
            $this->config->decrement('credit_balance', $cost);
            
            Log::info('SMS sent successfully to ' . $to . ' (SID: ' . $result->sid . ')');
            
            return [
                'success' => true,
                'sid' => $result->sid,
                'cost' => $cost
            ];
            
        } catch (\Twilio\Exceptions\RestException $e) {
            // Log failure
            SmsLog::create([
                'user_id' => auth()->id(),
                'phone_number' => $to,
                'message_type' => $type,
                'message_content' => $message,
                'provider' => 'twilio',
                'status' => 'failed',
                'failure_reason' => $e->getMessage(),
            ]);
            
            Log::error('SMS send failed: ' . $e->getMessage());
            
            return [
                'success' => false,
                'error' => $e->getMessage()
            ];
            
        } catch (\Exception $e) {
            Log::error('SMS Service Error: ' . $e->getMessage());
            
            return [
                'success' => false,
                'error' => 'Failed to send SMS'
            ];
        }
    }
    
    /**
     * Send multiple SMS with limit
     * Sends first $limit immediately, queues rest for cron processing
     * 
     * @param array $recipients Array of users with phone numbers
     * @param string $message SMS content
     * @param string $type Message type
     * @param int $limit Number to send immediately (default 3)
     * @return array ['sent' => int, 'queued' => int, 'failed' => int]
     */
    public function sendBulk($recipients, $message, $type = 'general', $limit = 3)
    {
        $sent = 0;
        $queued = 0;
        $failed = 0;
        
        foreach ($recipients as $index => $recipient) {
            if ($sent < $limit) {
                // Send immediately
                $result = $this->sendImmediate($recipient->phone, $message, $type);
                
                if ($result['success']) {
                    $sent++;
                } else {
                    $failed++;
                }
            } else {
                // Queue for cron processing
                $this->queueSms($recipient->id, $recipient->phone, $message, $type);
                $queued++;
            }
        }
        
        return [
            'sent' => $sent,
            'queued' => $queued,
            'failed' => $failed,
            'total' => count($recipients)
        ];
    }
    
    /**
     * Queue SMS for cron processing
     * 
     * @param int $userId User ID
     * @param string $phone Phone number
     * @param string $message Message content
     * @param string $type Message type
     * @param int $delayMinutes Delay before sending (default 5)
     * @return PendingSms
     */
    public function queueSms($userId, $phone, $message, $type = 'general', $delayMinutes = 5)
    {
        return PendingSms::create([
            'user_id' => $userId,
            'phone_number' => $phone,
            'message_type' => $type,
            'message_content' => $message,
            'status' => 'pending',
            'scheduled_at' => now()->addMinutes($delayMinutes),
            'attempts' => 0,
        ]);
    }
    
    /**
     * Process pending SMS (called by cron command)
     * 
     * @param int $limit Number of SMS to process per run
     * @return array ['processed' => int, 'sent' => int, 'failed' => int]
     */
    public function processPending($limit = 10)
    {
        $pending = PendingSms::where('status', 'pending')
            ->where('scheduled_at', '<=', now())
            ->where('attempts', '<', 3)
            ->limit($limit)
            ->get();
        
        $processed = 0;
        $sent = 0;
        $failed = 0;
        
        foreach ($pending as $sms) {
            $result = $this->sendImmediate(
                $sms->phone_number,
                $sms->message_content,
                $sms->message_type
            );
            
            if ($result['success']) {
                $sms->update([
                    'status' => 'sent',
                ]);
                $sent++;
            } else {
                $sms->increment('attempts');
                
                if ($sms->attempts >= 3) {
                    $sms->update([
                        'status' => 'failed',
                    ]);
                    $failed++;
                }
            }
            
            $processed++;
        }
        
        return [
            'processed' => $processed,
            'sent' => $sent,
            'failed' => $failed,
        ];
    }
    
    /**
     * Validate UK phone number format
     * 
     * @param string $phone
     * @return bool
     */
    protected function isValidUKPhone($phone)
    {
        // UK format: +44 followed by 10 digits
        return preg_match('/^\+44[1-9]\d{9}$/', $phone);
    }
    
    /**
     * Format phone number to UK format
     * 
     * @param string $phone
     * @return string|null
     */
    public function formatUKPhone($phone)
    {
        // Remove spaces, dashes, parentheses
        $phone = preg_replace('/[\s\-\(\)]/', '', $phone);
        
        // If starts with 0, replace with +44
        if (substr($phone, 0, 1) === '0') {
            return '+44' . substr($phone, 1);
        }
        
        // If starts with 44, add +
        if (substr($phone, 0, 2) === '44') {
            return '+' . $phone;
        }
        
        // If already has +44, return as is
        if (substr($phone, 0, 3) === '+44') {
            return $phone;
        }
        
        return null;
    }
    
    /**
     * Get SMS statistics for a date range
     * 
     * @param string $dateFrom
     * @param string $dateTo
     * @return array
     */
    public function getStatistics($dateFrom, $dateTo)
    {
        $logs = SmsLog::whereBetween('created_at', [$dateFrom, $dateTo]);
        
        return [
            'total_sent' => $logs->where('status', 'sent')->count(),
            'total_failed' => $logs->whereIn('status', ['failed', 'undelivered'])->count(),
            'total_cost' => $logs->where('status', 'sent')->sum('cost'),
            'by_type' => SmsLog::whereBetween('created_at', [$dateFrom, $dateTo])
                ->where('status', 'sent')
                ->selectRaw('message_type, COUNT(*) as count, SUM(cost) as cost')
                ->groupBy('message_type')
                ->get(),
        ];
    }
    
    /**
     * Check if SMS service is configured and active
     * 
     * @return bool
     */
    public function isConfigured()
    {
        return $this->config && $this->config->is_active && $this->client !== null;
    }
    
    /**
     * Get current credit balance
     * 
     * @return float
     */
    public function getCreditBalance()
    {
        return $this->config ? $this->config->credit_balance : 0;
    }
    
    /**
     * Send test SMS
     * 
     * @param string $to
     * @return array
     */
    public function sendTest($to)
    {
        $message = "Test SMS from MLC Classroom Management System. If you receive this, SMS is working correctly.";
        
        return $this->sendImmediate($to, $message, 'test');
    }
} 