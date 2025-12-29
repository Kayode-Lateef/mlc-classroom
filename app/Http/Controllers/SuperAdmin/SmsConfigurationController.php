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
            'api_secret' => 'nullable|string|max:500',
            'sender_id' => 'required|string|max:50',
            'credit_balance' => 'nullable|numeric|min:0',
            'low_balance_threshold' => 'required|numeric|min:0',
            'is_active' => 'required|boolean',
            'daily_limit' => 'required|integer|min:1',
            'monthly_limit' => 'required|integer|min:1',
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
            $encryptedSecret = $request->api_secret ? Crypt::encryptString($request->api_secret) : null;

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
            // Use SmsService to send test SMS
            $result = $this->smsService->sendImmediate(
                $request->test_phone,
                $request->test_message,
                'test'
            );

            if ($result['success']) {
                return response()->json([
                    'success' => true,
                    'message' => 'Test SMS sent successfully via ' . $this->smsService->getActiveProvider() . '!',
                    'details' => [
                        'message_id' => $result['sid'] ?? null,
                        'cost' => $result['cost'] ?? 0,
                        'remaining_balance' => $this->smsService->getCreditBalance(),
                        'provider' => $this->smsService->getActiveProvider(),
                    ],
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
}