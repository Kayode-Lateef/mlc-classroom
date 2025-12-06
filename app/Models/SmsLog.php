<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class SmsLog extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id',
        'phone_number',
        'message_type',
        'message_content',
        'provider',
        'provider_message_id',
        'status',
        'failure_reason',
        'cost',
        'sent_at',
        'delivered_at',
        'retry_count',
    ];

    protected $casts = [
        'cost' => 'decimal:4',
        'sent_at' => 'datetime',
        'delivered_at' => 'datetime',
        'retry_count' => 'integer',
    ];

    /**
     * Relationship: Log belongs to a user
     */
    public function user()
    {
        return $this->belongsTo(User::class);
    }

    /**
     * Scope: Failed SMS
     */
    public function scopeFailed($query)
    {
        return $query->where('status', 'failed');
    }

    /**
     * Scope: Delivered SMS
     */
    public function scopeDelivered($query)
    {
        return $query->where('status', 'delivered');
    }

    /**
     * Scope: By date range
     */
    public function scopeByDateRange($query, $startDate, $endDate)
    {
        return $query->whereBetween('sent_at', [$startDate, $endDate]);
    }
}