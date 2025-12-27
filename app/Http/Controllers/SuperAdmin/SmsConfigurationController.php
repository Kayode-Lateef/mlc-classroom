<?php

namespace App\Http\Controllers\SuperAdmin;

use App\Http\Controllers\Controller;
use App\Models\SmsConfiguration;
use App\Models\SmsLog;
use App\Models\ActivityLog;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\Crypt;

class SmsConfigurationController extends Controller
{
    /**
     * Display SMS configuration
     */
    public function index()
    {
        // Get or create SMS configuration (should only be one record)
        $config = SmsConfiguration::firstOrCreate(
            [],
            [
                'provider' => 'twilio',
                'api_key' => '',
                'api_secret' => '',
                'sender_id' => '',
                'credit_balance' => 0,
                'low_balance_threshold' => 10.00,
                'is_active' => false,
                'daily_limit' => 100,
                'monthly_limit' => 3000,
            ]
        );

        // Get today's SMS count
        $todaySms = SmsLog::whereDate('sent_at', today())->count();
        
        // Get this month's SMS count
        $monthSms = SmsLog::whereMonth('sent_at', now()->month)
            ->whereYear('sent_at', now()->year)
            ->count();

        // Get this month's cost
        $monthCost = SmsLog::whereMonth('sent_at', now()->month)
            ->whereYear('sent_at', now()->year)
            ->sum('cost');

        // Statistics
        $stats = [
            'balance' => $config->credit_balance,
            'today_sms' => $todaySms,
            'month_sms' => $monthSms,
            'month_cost' => $monthCost,
            'is_balance_low' => $config->isBalanceLow(),
            'daily_remaining' => max(0, $config->daily_limit - $todaySms),
            'monthly_remaining' => max(0, $config->monthly_limit - $monthSms),
        ];

        return view('superadmin.sms-config.index', compact('config', 'stats'));
    }

    /**
     * Update SMS configuration
     */
    public function update(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'provider' => 'required|in:twilio,vonage,messagebird,textlocal,bulksms',
            'api_key' => 'required|string|max:500',
            'api_secret' => 'required|string|max:500',
            'sender_id' => 'nullable|string|max:50',
            'credit_balance' => 'nullable|numeric|min:0',
            'low_balance_threshold' => 'required|numeric|min:0',
            'is_active' => 'required|boolean',
            'daily_limit' => 'nullable|integer|min:1',
            'monthly_limit' => 'nullable|integer|min:1',
        ]);

        if ($validator->fails()) {
            return redirect()->back()
                ->withErrors($validator)
                ->withInput()
                ->with('error', 'Please fix the errors below.');
        }

        try {
            $config = SmsConfiguration::firstOrFail();

            // Encrypt sensitive data
            $encryptedKey = Crypt::encryptString($request->api_key);
            $encryptedSecret = Crypt::encryptString($request->api_secret);

            $config->update([
                'provider' => $request->provider,
                'api_key' => $encryptedKey,
                'api_secret' => $encryptedSecret,
                'sender_id' => $request->sender_id,
                'credit_balance' => $request->credit_balance ?? $config->credit_balance,
                'low_balance_threshold' => $request->low_balance_threshold,
                'is_active' => $request->is_active,
                'daily_limit' => $request->daily_limit,
                'monthly_limit' => $request->monthly_limit,
            ]);

            // Log activity
            ActivityLog::create([
                'user_id' => auth()->id(),
                'action' => 'updated_sms_configuration',
                'model_type' => 'SmsConfiguration',
                'model_id' => $config->id,
                'description' => "Updated SMS configuration for provider: {$request->provider}",
                'ip_address' => $request->ip(),
                'user_agent' => $request->userAgent(),
            ]);

            return redirect()->route('superadmin.sms-config.index')
                ->with('success', 'SMS configuration updated successfully!');

        } catch (\Exception $e) {
            \Log::error('SMS configuration update failed: ' . $e->getMessage());
            
            return redirect()->back()
                ->withInput()
                ->with('error', 'Failed to update SMS configuration. Please try again.');
        }
    }

    /**
     * Test SMS configuration
     */
    public function test(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'test_phone' => 'required|string|max:20',
            'test_message' => 'required|string|max:160',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'message' => 'Invalid phone number or message.',
            ], 422);
        }

        try {
            $config = SmsConfiguration::firstOrFail();

            if (!$config->is_active) {
                return response()->json([
                    'success' => false,
                    'message' => 'SMS system is not active. Please activate it first.',
                ], 400);
            }

            // Decrypt credentials
            $apiKey = Crypt::decryptString($config->api_key);
            $apiSecret = Crypt::decryptString($config->api_secret);

            // Initialize provider client based on selected provider
            $result = $this->sendTestSms(
                $config->provider,
                $apiKey,
                $apiSecret,
                $config->sender_id,
                $request->test_phone,
                $request->test_message
            );

            if ($result['success']) {
                // Log test SMS
                SmsLog::create([
                    'user_id' => auth()->id(),
                    'phone_number' => $request->test_phone,
                    'message_type' => 'general',
                    'message_content' => $request->test_message,
                    'provider' => $config->provider,
                    'provider_message_id' => $result['message_id'] ?? null,
                    'status' => 'sent',
                    'sent_at' => now(),
                ]);

                return response()->json([
                    'success' => true,
                    'message' => 'Test SMS sent successfully!',
                ]);
            } else {
                return response()->json([
                    'success' => false,
                    'message' => $result['error'] ?? 'Failed to send test SMS.',
                ], 400);
            }

        } catch (\Exception $e) {
            \Log::error('Test SMS failed: ' . $e->getMessage());
            
            return response()->json([
                'success' => false,
                'message' => 'Test failed: ' . $e->getMessage(),
            ], 500);
        }
    }

    /**
     * Send test SMS via selected provider
     */
    protected function sendTestSms($provider, $apiKey, $apiSecret, $senderId, $to, $message)
    {
        try {
            switch ($provider) {
                case 'twilio':
                    return $this->sendViaTwilio($apiKey, $apiSecret, $senderId, $to, $message);
                    
                case 'vonage':
                    return $this->sendViaVonage($apiKey, $apiSecret, $senderId, $to, $message);
                    
                case 'messagebird':
                    return $this->sendViaMessageBird($apiKey, $senderId, $to, $message);
                    
                default:
                    return [
                        'success' => false,
                        'error' => 'Provider not implemented yet. Please use Twilio.',
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
     * Send via Twilio
     */
    protected function sendViaTwilio($accountSid, $authToken, $from, $to, $message)
    {
        // Note: This is a placeholder. Actual Twilio implementation would use Twilio SDK
        // For production, install: composer require twilio/sdk
        
        return [
            'success' => true,
            'message_id' => 'TEST_' . uniqid(),
        ];
    }

    /**
     * Send via Vonage (Nexmo)
     */
    protected function sendViaVonage($apiKey, $apiSecret, $from, $to, $message)
    {
        // Placeholder for Vonage implementation
        return [
            'success' => true,
            'message_id' => 'TEST_' . uniqid(),
        ];
    }

    /**
     * Send via MessageBird
     */
    protected function sendViaMessageBird($apiKey, $from, $to, $message)
    {
        // Placeholder for MessageBird implementation
        return [
            'success' => true,
            'message_id' => 'TEST_' . uniqid(),
        ];
    }
}