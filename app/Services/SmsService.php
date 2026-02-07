<?php

namespace App\Services;

use App\Models\SmsLog;
use App\Models\PendingSms;
use App\Models\SmsConfiguration;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Crypt;
use Carbon\Carbon;

class SmsService
{
    protected $config;
    
    public function __construct()
    {
        try {
            $this->config = SmsConfiguration::where('is_active', true)->first();
        } catch (\Exception $e) {
            // Table may not exist yet (pre-migration)
            $this->config = null;
        }
    }
    
    /**
     * Send SMS immediately (synchronous)
     * Use for 1-3 urgent SMS only to avoid timeout
     * 
     * @param string $to UK phone number (+44... or 07...)
     * @param string $message SMS content
     * @param string $type Message type (absence, homework, etc.)
     * @return array ['success' => bool, 'sid' => string|null, 'error' => string|null]
     */
    public function sendImmediate($to, $message, $type = 'general')
    {
        if (!$this->config || !$this->config->is_active) {
            Log::error('SMS Service: SMS not configured or not active');
            return ['success' => false, 'error' => 'SMS service not configured'];
        }
        
        // Format phone number to UK format
        $to = $this->formatUKPhone($to);
        
        if (!$to) {
            Log::error('SMS Service: Invalid UK phone number format');
            return ['success' => false, 'error' => 'Invalid UK phone number'];
        }
        
        // Check credit balance
        if ($this->config->credit_balance < $this->config->low_balance_threshold) {
            Log::warning('SMS Service: Low credit balance - £' . $this->config->credit_balance);
        }
        
        // Check daily limit
        if ($this->config->isDailyLimitReached()) {
            Log::warning('SMS Service: Daily limit reached');
            return ['success' => false, 'error' => 'Daily SMS limit reached'];
        }
        
        // Check monthly limit
        if ($this->config->isMonthlyLimitReached()) {
            Log::warning('SMS Service: Monthly limit reached');
            return ['success' => false, 'error' => 'Monthly SMS limit reached'];
        }
        
        try {
            // Decrypt credentials
            $apiKey = Crypt::decryptString($this->config->api_key);
            $apiSecret = $this->config->api_secret ? Crypt::decryptString($this->config->api_secret) : null;
            
            // Send via selected provider
            $result = $this->sendViaProvider(
                $this->config->provider,
                $apiKey,
                $apiSecret,
                $this->config->sender_id,
                $to,
                $message
            );
            
            if ($result['success']) {
                // Log success
                SmsLog::create([
                    'user_id' => auth()->id(),
                    'phone_number' => $to,
                    'message_type' => $type,
                    'message_content' => $message,
                    'provider' => $this->config->provider,
                    'provider_message_id' => $result['message_id'] ?? null,
                    'status' => 'sent',
                    'cost' => $result['cost'] ?? 0.04,
                    'sent_at' => now(),
                ]);
                
                // Deduct from balance
                if (isset($result['cost']) && $result['cost'] > 0) {
                    $this->config->deductBalance($result['cost']);
                }
                
                Log::info("SMS sent successfully to {$to} via {$this->config->provider}");
                
                return [
                    'success' => true,
                    'sid' => $result['message_id'] ?? null,
                    'cost' => $result['cost'] ?? 0.04
                ];
            } else {
                // Log failure
                SmsLog::create([
                    'user_id' => auth()->id(),
                    'phone_number' => $to,
                    'message_type' => $type,
                    'message_content' => $message,
                    'provider' => $this->config->provider,
                    'status' => 'failed',
                    'error_message' => $result['error'] ?? 'Unknown error',
                ]);
                
                Log::error('SMS send failed: ' . ($result['error'] ?? 'Unknown error'));
                
                return [
                    'success' => false,
                    'error' => $result['error'] ?? 'Failed to send SMS'
                ];
            }
            
        } catch (\Exception $e) {
            Log::error('SMS Service Error: ' . $e->getMessage());
            
            // Log exception
            SmsLog::create([
                'user_id' => auth()->id(),
                'phone_number' => $to,
                'message_type' => $type,
                'message_content' => $message,
                'provider' => $this->config->provider,
                'status' => 'failed',
                'error_message' => $e->getMessage(),
            ]);
            
            return [
                'success' => false,
                'error' => 'Failed to send SMS: ' . $e->getMessage()
            ];
        }
    }
    
    /**
     * Route SMS to appropriate provider
     */
    protected function sendViaProvider($provider, $apiKey, $apiSecret, $senderId, $to, $message)
    {
        try {
            switch ($provider) {
                case 'voodoo':
                    return $this->sendViaVoodoo($apiKey, $apiSecret, $senderId, $to, $message);
                case 'textlocal':
                    return $this->sendViaTextLocal($apiKey, $senderId, $to, $message);
                    
                case 'messagebird':
                    return $this->sendViaMessageBird($apiKey, $senderId, $to, $message);
                    
                case 'twilio':
                    return $this->sendViaTwilio($apiKey, $apiSecret, $senderId, $to, $message);
                    
                case 'vonage':
                    return $this->sendViaVonage($apiKey, $apiSecret, $senderId, $to, $message);
                    
                case 'bulksms':
                    return $this->sendViaBulkSMS($apiKey, $apiSecret, $to, $message);
                    
                default:
                    return [
                        'success' => false,
                        'error' => 'Unsupported SMS provider: ' . $provider,
                    ];
            }
        } catch (\Exception $e) {
            return [
                'success' => false,
                'error' => $e->getMessage(),
            ];
        }
    }

    /**
     * Send via Voodoo SMS
     */
    protected function sendViaVoodoo($username, $password, $senderId, $to, $message)
    {
        try {
            // Format phone number for Voodoo (international format without +)
            $formattedNumber = $this->formatForVoodoo($to);
            
            $response = Http::asForm()->post('https://www.voodoosms.com/vapi/server/sendSMS', [
                'username' => $username,
                'password' => $password,
                'from' => $senderId,
                'to' => $formattedNumber,
                'text' => $message,
                'reference' => uniqid(), // Unique reference for tracking
                'delivery_report' => 1, // Request delivery report
                'flash' => 0, // 0 = normal SMS, 1 = flash SMS
                'unicode' => $this->containsUnicode($message) ? 1 : 0,
            ]);

            $result = $response->json();
            
            Log::info('Voodoo SMS Response:', $result ?? []);

            if (isset($result['success']) && $result['success'] === true) {
                return [
                    'success' => true,
                    'message_id' => $result['message_id'] ?? null,
                    'cost' => $result['credits_used'] ?? 0.04, // Credits used
                    'credits_remaining' => $result['credits_remaining'] ?? null,
                ];
            } else {
                $error = $this->parseVoodooError($result);
                return [
                    'success' => false,
                    'error' => $error,
                ];
            }

        } catch (\Exception $e) {
            return [
                'success' => false,
                'error' => 'Voodoo SMS Error: ' . $e->getMessage(),
            ];
        }
    }

    /**
     * Format phone number for Voodoo SMS
     * Voodoo expects international format without +
     */
    protected function formatForVoodoo($phone)
    {
        // Remove all non-numeric characters except +
        $phone = preg_replace('/[^\d+]/', '', $phone);
        
        // Remove leading +
        $phone = ltrim($phone, '+');
        
        // Convert UK format to international
        if (substr($phone, 0, 2) === '44') {
            return $phone;
        }
        
        if (substr($phone, 0, 1) === '0') {
            return '44' . substr($phone, 1);
        }
        
        return $phone;
    }

    /**
     * Parse Voodoo SMS error messages
     */
    protected function parseVoodooError($result)
    {
        $errorMessages = [
            1001 => 'Authentication failed',
            1002 => 'Insufficient credits',
            1003 => 'Invalid sender ID',
            1004 => 'Invalid recipient number',
            1005 => 'Invalid message',
            1006 => 'Network error',
            1007 => 'Invalid request',
            1008 => 'Service unavailable',
        ];
        
        if (isset($result['error_code'])) {
            return $errorMessages[$result['error_code']] ?? 'Unknown error (Code: ' . $result['error_code'] . ')';
        }
        
        return $result['error'] ?? 'Unknown Voodoo SMS error';
    }

    /**
     * Check if message contains Unicode characters
     */
    protected function containsUnicode($message)
    {
        return strlen($message) != mb_strlen($message);
    }

    /**
     * Check Voodoo SMS balance (optional method for admin dashboard)
     */
    public function checkVoodooBalance()
    {
        if (!$this->config || $this->config->provider !== 'voodoo') {
            return ['success' => false, 'error' => 'Voodoo SMS is not the active provider'];
        }
        
        try {
            $apiKey = Crypt::decryptString($this->config->api_key);
            $apiSecret = $this->config->api_secret ? Crypt::decryptString($this->config->api_secret) : null;
            
            $response = Http::asForm()->post('https://www.voodoosms.com/vapi/server/getBalance', [
                'username' => $apiKey, // For Voodoo, api_key is username
                'password' => $apiSecret, // For Voodoo, api_secret is password
            ]);

            $result = $response->json();
            
            if (isset($result['success']) && $result['success'] === true) {
                return [
                    'success' => true,
                    'balance' => $result['balance'] ?? 0,
                    'credits_remaining' => $result['credits_remaining'] ?? 0,
                ];
            } else {
                return [
                    'success' => false,
                    'error' => $this->parseVoodooError($result),
                ];
            }

        } catch (\Exception $e) {
            return [
                'success' => false,
                'error' => 'Failed to check balance: ' . $e->getMessage(),
            ];
        }
    }
    
    /**
     * Send via TextLocal (UK) - No SDK Required
     */
    protected function sendViaTextLocal($apiKey, $sender, $to, $message)
    {
        try {
            // Format for TextLocal (without +)
            $numbers = $this->formatForTextLocal($to);
            
            $response = Http::asForm()->post('https://api.txtlocal.com/send/', [
                'apikey' => $apiKey,
                'numbers' => $numbers,
                'sender' => $sender,
                'message' => $message,
            ]);

            $result = $response->json();

            if ($response->successful() && isset($result['status']) && $result['status'] === 'success') {
                return [
                    'success' => true,
                    'message_id' => $result['messages'][0]['id'] ?? null,
                    'cost' => $result['cost'] ?? 0.04, // ~4p per SMS
                ];
            } else {
                return [
                    'success' => false,
                    'error' => $result['errors'][0]['message'] ?? 'TextLocal API error',
                ];
            }

        } catch (\Exception $e) {
            return [
                'success' => false,
                'error' => 'TextLocal Error: ' . $e->getMessage(),
            ];
        }
    }
    
    /**
     * Send via MessageBird - Requires SDK
     */
    protected function sendViaMessageBird($apiKey, $originator, $to, $message)
    {
        try {
            if (!class_exists(\MessageBird\Client::class)) {
                return [
                    'success' => false,
                    'error' => 'MessageBird SDK not installed. Run: composer require messagebird/php-rest-api',
                ];
            }

            $messageBird = new \MessageBird\Client($apiKey);
            
            $mbMessage = new \MessageBird\Objects\Message();
            $mbMessage->originator = $originator;
            $mbMessage->recipients = [$to];
            $mbMessage->body = $message;

            $result = $messageBird->messages->create($mbMessage);

            return [
                'success' => true,
                'message_id' => $result->id,
                'cost' => 0.05, // ~5p per SMS
            ];

        } catch (\MessageBird\Exceptions\AuthenticateException $e) {
            return [
                'success' => false,
                'error' => 'MessageBird Authentication Error: ' . $e->getMessage(),
            ];
        } catch (\Exception $e) {
            return [
                'success' => false,
                'error' => 'MessageBird Error: ' . $e->getMessage(),
            ];
        }
    }
    
    /**
     * Send via Twilio - Requires SDK
     */
    protected function sendViaTwilio($accountSid, $authToken, $from, $to, $message)
    {
        try {
            if (!class_exists(\Twilio\Rest\Client::class)) {
                return [
                    'success' => false,
                    'error' => 'Twilio SDK not installed. Run: composer require twilio/sdk',
                ];
            }

            $client = new \Twilio\Rest\Client($accountSid, $authToken);
            
            $twilioMessage = $client->messages->create(
                $to,
                [
                    'from' => $from,
                    'body' => $message,
                ]
            );

            return [
                'success' => true,
                'message_id' => $twilioMessage->sid,
                'cost' => abs((float) $twilioMessage->price),
            ];

        } catch (\Twilio\Exceptions\RestException $e) {
            return [
                'success' => false,
                'error' => 'Twilio Error: ' . $e->getMessage(),
            ];
        } catch (\Exception $e) {
            return [
                'success' => false,
                'error' => 'Error: ' . $e->getMessage(),
            ];
        }
    }
    
    /**
     * Send via Vonage (Nexmo) - Requires SDK
     */
    protected function sendViaVonage($apiKey, $apiSecret, $from, $to, $message)
    {
        try {
            if (!class_exists(\Vonage\Client::class)) {
                return [
                    'success' => false,
                    'error' => 'Vonage SDK not installed. Run: composer require vonage/client',
                ];
            }

            $basic = new \Vonage\Client\Credentials\Basic($apiKey, $apiSecret);
            $client = new \Vonage\Client($basic);

            $response = $client->sms()->send(
                new \Vonage\SMS\Message\SMS($to, $from, $message)
            );

            $vonageMessage = $response->current();

            if ($vonageMessage->getStatus() == 0) {
                return [
                    'success' => true,
                    'message_id' => $vonageMessage->getMessageId(),
                    'cost' => (float) $vonageMessage->getMessagePrice(),
                ];
            } else {
                return [
                    'success' => false,
                    'error' => 'Vonage Error Code: ' . $vonageMessage->getStatus(),
                ];
            }

        } catch (\Exception $e) {
            return [
                'success' => false,
                'error' => 'Vonage Error: ' . $e->getMessage(),
            ];
        }
    }
    
    /**
     * Send via BulkSMS - No SDK Required
     */
    protected function sendViaBulkSMS($username, $password, $to, $message)
    {
        try {
            $response = Http::withBasicAuth($username, $password)
                ->post('https://api.bulksms.com/v1/messages', [
                    'to' => $to,
                    'body' => $message,
                ]);

            if ($response->successful()) {
                $result = $response->json();
                return [
                    'success' => true,
                    'message_id' => $result['id'] ?? null,
                    'cost' => 0.04, // Estimate
                ];
            } else {
                return [
                    'success' => false,
                    'error' => 'BulkSMS Error: ' . $response->body(),
                ];
            }

        } catch (\Exception $e) {
            return [
                'success' => false,
                'error' => 'BulkSMS Error: ' . $e->getMessage(),
            ];
        }
    }
    
    /**
     * Format phone number for TextLocal (without +)
     * 
     * @param string $number
     * @return string
     */
    protected function formatForTextLocal($number)
    {
        // Remove all non-numeric characters except +
        $number = preg_replace('/[^\d+]/', '', $number);
        
        // Remove leading + if present
        $number = ltrim($number, '+');
        
        // If starts with 0, replace with 44
        if (substr($number, 0, 1) === '0') {
            $number = '44' . substr($number, 1);
        }
        
        // If doesn't start with 44, add it
        if (substr($number, 0, 2) !== '44') {
            $number = '44' . $number;
        }
        
        return $number;
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
        // UK format: +44 followed by 10 digits OR 07 followed by 9 digits
        return preg_match('/^(\+44[1-9]\d{9}|0[1-9]\d{9})$/', $phone);
    }
    
    /**
     * Format phone number to UK E.164 format
     * Handles: 07123456789, +447123456789, 447123456789
     * Returns: +447123456789
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
            'success_rate' => $this->calculateSuccessRate($dateFrom, $dateTo),
            'by_type' => SmsLog::whereBetween('created_at', [$dateFrom, $dateTo])
                ->where('status', 'sent')
                ->selectRaw('message_type, COUNT(*) as count, SUM(cost) as cost')
                ->groupBy('message_type')
                ->get(),
            'by_provider' => SmsLog::whereBetween('created_at', [$dateFrom, $dateTo])
                ->where('status', 'sent')
                ->selectRaw('provider, COUNT(*) as count, SUM(cost) as cost')
                ->groupBy('provider')
                ->get(),
        ];
    }
    
    /**
     * Calculate success rate for a date range
     * 
     * @param string $dateFrom
     * @param string $dateTo
     * @return float
     */
    protected function calculateSuccessRate($dateFrom, $dateTo)
    {
        $total = SmsLog::whereBetween('created_at', [$dateFrom, $dateTo])->count();
        
        if ($total === 0) {
            return 0;
        }
        
        $successful = SmsLog::whereBetween('created_at', [$dateFrom, $dateTo])
            ->where('status', 'sent')
            ->count();
        
        return round(($successful / $total) * 100, 2);
    }
    
    /**
     * Check if SMS service is configured and active
     * 
     * @return bool
     */
    public function isConfigured()
    {
        return $this->config && $this->config->is_active;
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
     * Get active provider name
     * 
     * @return string|null
     */
    public function getActiveProvider()
    {
        return $this->config ? $this->config->provider : null;
    }
    
    /**
     * Check if balance is low
     * 
     * @return bool
     */
    public function isBalanceLow()
    {
        return $this->config && $this->config->isBalanceLow();
    }
    
    /**
     * Send test SMS
     * 
     * @param string $to
     * @return array
     */
    public function sendTest($to)
    {
        $providerName = $this->config->provider ?? 'unknown provider';
        
        if ($providerName === 'voodoo') {
            $message = "Test SMS from MLC Classroom via Voodoo SMS. Delivery confirmation requested.";
        } else {
            $message = "Test SMS from MLC Classroom Management System. If you receive this, SMS is working correctly via " . $providerName . ".";
        }
        
        return $this->sendImmediate($to, $message, 'test');
    }
}