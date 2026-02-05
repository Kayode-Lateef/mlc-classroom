<?php

namespace App\Http\Controllers\SuperAdmin;

use App\Http\Controllers\Controller;
use App\Models\SmsConfiguration;
use App\Models\SmsLog;
use App\Models\ActivityLog;
use App\Services\SmsService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\Crypt;

class SmsConfigurationController extends Controller
{
    protected $smsService;
    
    public function __construct(SmsService $smsService)
    {
        $this->smsService = $smsService;
    }
    
    /**
     * Display SMS configuration
     */
    public function index()
    {
        // Get or create SMS configuration (should only be one record)
        $config = SmsConfiguration::firstOrCreate(
            [],
            [
                'provider' => 'textlocal',
                'api_key' => '',
                'api_secret' => '',
                'sender_id' => '',
                'credit_balance' => 0,
                'low_balance_threshold' => 10.00,
                'is_active' => false,
                'daily_limit' => 500,
                'monthly_limit' => 5000,
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

        // Get success rate for this month
        $successfulSms = SmsLog::whereMonth('sent_at', now()->month)
            ->whereYear('sent_at', now()->year)
            ->where('status', 'sent')
            ->count();
        
        $successRate = $monthSms > 0 ? round(($successfulSms / $monthSms) * 100, 2) : 0;

        // Check Voodoo SMS balance if it's the active provider
        $voodooBalance = null;
        if ($config->is_active && $config->provider === 'voodoo') {
            $voodooResult = $this->smsService->checkVoodooBalance();
            if ($voodooResult['success']) {
                $voodooBalance = [
                    'credits_remaining' => $voodooResult['credits_remaining'] ?? 0,
                    'balance' => $voodooResult['balance'] ?? 0,
                ];
            }
        }

        // Statistics
        $stats = [
            'balance' => $config->credit_balance,
            'today_sms' => $todaySms,
            'month_sms' => $monthSms,
            'month_cost' => $monthCost,
            'success_rate' => $successRate,
            'is_balance_low' => $config->isBalanceLow(),
            'is_daily_limit_reached' => $config->isDailyLimitReached(),
            'is_monthly_limit_reached' => $config->isMonthlyLimitReached(),
            'daily_remaining' => max(0, $config->daily_limit - $todaySms),
            'monthly_remaining' => max(0, $config->monthly_limit - $monthSms),
            'voodoo_balance' => $voodooBalance,
        ];

        // Provider display names
        $providerNames = [
            'textlocal' => 'TextLocal (UK)',
            'messagebird' => 'MessageBird',
            'twilio' => 'Twilio',
            'vonage' => 'Vonage (Nexmo)',
            'bulksms' => 'BulkSMS',
            'voodoo' => 'Voodoo SMS',
        ];

        return view('superadmin.sms-config.index', compact('config', 'stats', 'providerNames'));
    }

    /**
     * Update SMS configuration
     */
    public function update(Request $request)
    {
        // Define validation rules based on provider
        $provider = $request->provider;
        
        $rules = [
            'provider' => 'required|in:twilio,vonage,messagebird,textlocal,bulksms,voodoo',
            'is_active' => 'required|boolean',
            'low_balance_threshold' => 'required|numeric|min:0',
            'daily_limit' => 'required|integer|min:1',
            'monthly_limit' => 'required|integer|min:1',
        ];

        // Conditional validation based on provider
        switch ($provider) {
            case 'twilio':
                $rules['api_key'] = 'required|string|max:500'; // Account SID
                $rules['api_secret'] = 'required|string|max:500'; // Auth Token
                $rules['sender_id'] = 'required|string|max:20'; // Twilio phone number
                break;
                
            case 'vonage':
            case 'messagebird':
            case 'voodoo':
                $rules['api_key'] = 'required|string|max:500'; // API Key/Username
                $rules['api_secret'] = 'required|string|max:500'; // API Secret/Password
                $rules['sender_id'] = 'required|string|max:50'; // Sender ID/Name
                break;
                
            case 'textlocal':
                $rules['api_key'] = 'required|string|max:500'; // API Key
                $rules['sender_id'] = 'required|string|max:11'; // Sender name (max 11 chars)
                break;
                
            case 'bulksms':
                $rules['api_key'] = 'required|string|max:500'; // Username
                $rules['api_secret'] = 'required|string|max:500'; // Password
                break;
        }

        // Add credit balance validation for non-Voodoo providers
        if ($provider !== 'voodoo') {
            $rules['credit_balance'] = 'nullable|numeric|min:0';
        }

        $validator = Validator::make($request->all(), $rules);

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
            $encryptedSecret = $request->api_secret ? Crypt::encryptString($request->api_secret) : null;

            // For Voodoo SMS, set credit_balance to 0 since it uses credits
            $creditBalance = $provider === 'voodoo' ? 0 : ($request->credit_balance ?? $config->credit_balance);

            $config->update([
                'provider' => $request->provider,
                'api_key' => $encryptedKey,
                'api_secret' => $encryptedSecret,
                'sender_id' => $request->sender_id ?? '',
                'credit_balance' => $creditBalance,
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
     * Test SMS configuration using SmsService
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
                'errors' => $validator->errors(),
            ], 422);
        }

        try {
            // Get config to check provider
            $config = SmsConfiguration::first();
            
            if (!$config || !$config->is_active) {
                return response()->json([
                    'success' => false,
                    'message' => 'SMS service is not configured or not active.',
                ], 400);
            }

            // For Voodoo SMS, add test identifier to message
            $testMessage = $request->test_message;
            if ($config->provider === 'voodoo') {
                $testMessage = "[TEST] " . $testMessage;
            }

            // Use SmsService to send test SMS
            $result = $this->smsService->sendImmediate(
                $request->test_phone,
                $testMessage,
                'test'
            );

            if ($result['success']) {
                $response = [
                    'success' => true,
                    'message' => 'Test SMS sent successfully via ' . $this->smsService->getActiveProvider() . '!',
                    'details' => [
                        'message_id' => $result['sid'] ?? $result['message_id'] ?? null,
                        'cost' => $result['cost'] ?? 0,
                        'remaining_balance' => $this->smsService->getCreditBalance(),
                        'provider' => $this->smsService->getActiveProvider(),
                    ],
                ];

                // Add Voodoo-specific details
                if ($config->provider === 'voodoo' && isset($result['credits_remaining'])) {
                    $response['details']['credits_remaining'] = $result['credits_remaining'];
                }

                return response()->json($response);
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
     * Add credit balance (for manual top-ups)
     */
    public function addBalance(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'amount' => 'required|numeric|min:0.01',
        ]);

        if ($validator->fails()) {
            return redirect()->back()
                ->withErrors($validator)
                ->with('error', 'Invalid amount.');
        }

        try {
            $config = SmsConfiguration::firstOrFail();

            // Check if provider is Voodoo (Voodoo doesn't use monetary balance)
            if ($config->provider === 'voodoo') {
                return redirect()->back()
                    ->with('error', 'Cannot add balance to Voodoo SMS. Credits are managed directly via Voodoo dashboard.');
            }

            $config->addBalance($request->amount);

            // Log activity
            ActivityLog::create([
                'user_id' => auth()->id(),
                'action' => 'added_sms_balance',
                'model_type' => 'SmsConfiguration',
                'model_id' => $config->id,
                'description' => "Added £{$request->amount} to SMS balance",
                'ip_address' => $request->ip(),
                'user_agent' => $request->userAgent(),
            ]);

            return redirect()->route('superadmin.sms-config.index')
                ->with('success', "Successfully added £{$request->amount} to SMS balance!");

        } catch (\Exception $e) {
            return redirect()->back()
                ->with('error', 'Failed to add balance. Please try again.');
        }
    }

    /**
     * Check Voodoo SMS balance (for Voodoo SMS only)
     */
    public function checkVoodooBalance(Request $request)
    {
        try {
            $config = SmsConfiguration::first();
            
            if (!$config || $config->provider !== 'voodoo') {
                return response()->json([
                    'success' => false,
                    'message' => 'Voodoo SMS is not the active provider.',
                ], 400);
            }

            $result = $this->smsService->checkVoodooBalance();

            if ($result['success']) {
                return response()->json([
                    'success' => true,
                    'message' => 'Voodoo SMS balance retrieved successfully.',
                    'balance' => [
                        'credits_remaining' => $result['credits_remaining'] ?? 0,
                        'monetary_balance' => $result['balance'] ?? 0,
                    ],
                ]);
            } else {
                return response()->json([
                    'success' => false,
                    'message' => $result['error'] ?? 'Failed to check Voodoo SMS balance.',
                ], 400);
            }

        } catch (\Exception $e) {
            \Log::error('Voodoo balance check failed: ' . $e->getMessage());
            
            return response()->json([
                'success' => false,
                'message' => 'Failed to check balance: ' . $e->getMessage(),
            ], 500);
        }
    }

    /**
     * Refresh Voodoo SMS balance (AJAX request)
     */
    public function refreshVoodooBalance()
    {
        try {
            $config = SmsConfiguration::first();
            
            if (!$config || $config->provider !== 'voodoo') {
                return response()->json([
                    'success' => false,
                    'message' => 'Voodoo SMS is not the active provider.',
                ], 400);
            }

            $result = $this->smsService->checkVoodooBalance();

            if ($result['success']) {
                return response()->json([
                    'success' => true,
                    'credits_remaining' => $result['credits_remaining'] ?? 0,
                    'monetary_balance' => $result['balance'] ?? 0,
                    'formatted' => 'Credits: ' . ($result['credits_remaining'] ?? 0) . ' | Balance: $' . ($result['balance'] ?? 0),
                ]);
            } else {
                return response()->json([
                    'success' => false,
                    'message' => $result['error'] ?? 'Failed to refresh balance.',
                ], 400);
            }

        } catch (\Exception $e) {
            \Log::error('Voodoo balance refresh failed: ' . $e->getMessage());
            
            return response()->json([
                'success' => false,
                'message' => 'Failed to refresh balance: ' . $e->getMessage(),
            ], 500);
        }
    }
}