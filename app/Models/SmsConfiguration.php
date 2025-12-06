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
}