<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class PendingEmail extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id',
        'email',
        'subject',
        'body',
        'status',
        'scheduled_at',
        'attempts',
    ];

    protected $casts = [
        'scheduled_at' => 'datetime',
        'attempts' => 'integer',
    ];

    /**
     * Relationship: Pending email belongs to a user
     */
    public function user()
    {
        return $this->belongsTo(User::class);
    }

    /**
     * Scope: Pending emails ready to send
     */
    public function scopeReadyToSend($query)
    {
        return $query->where('status', 'pending')
                     ->where('scheduled_at', '<=', now())
                     ->where('attempts', '<', 3);
    }
}