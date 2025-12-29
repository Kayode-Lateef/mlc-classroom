<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class SmsConfiguration extends Model
{
    use HasFactory;

    protected $table = 'sms_configuration';

    protected $fillable = [
        'provider',
        'api_key',
        'api_secret',
        'sender_id',
        'credit_balance',
        'low_balance_threshold',
        'is_active',
        'daily_limit',
        'monthly_limit',
    ];

    protected $casts = [
        'credit_balance' => 'decimal:2',
        'low_balance_threshold' => 'decimal:2',
        'is_active' => 'boolean',
        'daily_limit' => 'integer',
        'monthly_limit' => 'integer',
    ];

    /**
     * Check if balance is low
     */
    public function isBalanceLow(): bool
    {
        return $this->credit_balance <= $this->low_balance_threshold;
    }

    /**
     * Check if daily SMS limit has been reached
     */
    public function isDailyLimitReached(): bool
    {
        $todayCount = SmsLog::whereDate('sent_at', today())->count();
        return $todayCount >= $this->daily_limit;
    }

    /**
     * Check if monthly SMS limit has been reached
     */
    public function isMonthlyLimitReached(): bool
    {
        $monthCount = SmsLog::whereMonth('sent_at', now()->month)
            ->whereYear('sent_at', now()->year)
            ->count();
        return $monthCount >= $this->monthly_limit;
    }

    /**
     * Get remaining daily SMS count
     */
    public function getRemainingDailySms(): int
    {
        $todayCount = SmsLog::whereDate('sent_at', today())->count();
        return max(0, $this->daily_limit - $todayCount);
    }

    /**
     * Get remaining monthly SMS count
     */
    public function getRemainingSmsThisMonth(): int
    {
        $monthCount = SmsLog::whereMonth('sent_at', now()->month)
            ->whereYear('sent_at', now()->year)
            ->count();
        return max(0, $this->monthly_limit - $monthCount);
    }

    /**
     * Get today's SMS count
     */
    public function getTodaySmsCount(): int
    {
        return SmsLog::whereDate('sent_at', today())->count();
    }

    /**
     * Get this month's SMS count
     */
    public function getMonthSmsCount(): int
    {
        return SmsLog::whereMonth('sent_at', now()->month)
            ->whereYear('sent_at', now()->year)
            ->count();
    }

    /**
     * Get this month's SMS cost
     */
    public function getMonthSmsCost(): float
    {
        return SmsLog::whereMonth('sent_at', now()->month)
            ->whereYear('sent_at', now()->year)
            ->where('status', 'sent')
            ->sum('cost');
    }

    /**
     * Deduct from balance
     */
    public function deductBalance($amount)
    {
        $this->decrement('credit_balance', $amount);
    }

    /**
     * Add to balance
     */
    public function addBalance($amount)
    {
        $this->increment('credit_balance', $amount);
    }

    /**
     * Get SMS logs for this configuration
     */
    public function smsLogs()
    {
        return $this->hasMany(SmsLog::class, 'provider', 'provider');
    }

    /**
     * Check if provider requires API secret
     * 
     * @return bool
     */
    public function requiresApiSecret(): bool
    {
        return in_array($this->provider, ['twilio', 'vonage', 'bulksms']);
    }

    /**
     * Check if provider uses HTTP API (no SDK required)
     * 
     * @return bool
     */
    public function usesHttpApi(): bool
    {
        return in_array($this->provider, ['textlocal', 'bulksms']);
    }

    /**
     * Get provider display name
     * 
     * @return string
     */
    public function getProviderDisplayName(): string
    {
        $names = [
            'textlocal' => 'TextLocal',
            'messagebird' => 'MessageBird',
            'twilio' => 'Twilio',
            'vonage' => 'Vonage (Nexmo)',
            'bulksms' => 'BulkSMS',
        ];

        return $names[$this->provider] ?? ucfirst($this->provider);
    }

    /**
     * Get provider documentation URL
     * 
     * @return string|null
     */
    public function getProviderDocsUrl(): ?string
    {
        $docs = [
            'textlocal' => 'https://api.txtlocal.com/docs/',
            'messagebird' => 'https://developers.messagebird.com/',
            'twilio' => 'https://www.twilio.com/docs/sms',
            'vonage' => 'https://developer.vonage.com/messaging/sms/overview',
            'bulksms' => 'https://www.bulksms.com/developer/',
        ];

        return $docs[$this->provider] ?? null;
    }
}