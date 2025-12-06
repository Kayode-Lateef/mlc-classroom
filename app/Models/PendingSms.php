<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class PendingSms extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id',
        'phone_number',
        'message_type',
        'message_content',
        'status',
        'scheduled_at',
        'attempts',
    ];

    protected $casts = [
        'scheduled_at' => 'datetime',
        'attempts' => 'integer',
    ];

    /**
     * Relationship: Pending SMS belongs to a user
     */
    public function user()
    {
        return $this->belongsTo(User::class);
    }

    /**
     * Scope: Pending SMS ready to send
     */
    public function scopeReadyToSend($query)
    {
        return $query->where('status', 'pending')
                     ->where('scheduled_at', '<=', now())
                     ->where('attempts', '<', 3);
    }
}