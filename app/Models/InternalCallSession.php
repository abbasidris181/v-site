<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class InternalCallSession extends Model
{
    use HasFactory;

    protected $fillable = [
        'caller_id',
        'receiver_id',
        'room_id',
        'is_support_call',
        'status',
        'started_at',
        'ended_at',
        'duration_seconds',
        'sdp_offer',
        'sdp_answer',
        'ice_candidates',
    ];

    protected $casts = [
        'is_support_call' => 'boolean',
        'started_at' => 'datetime',
        'ended_at' => 'datetime',
        'duration_seconds' => 'integer',
        'ice_candidates' => 'array',
    ];

    /**
     * User who placed the call.
     */
    public function caller(): BelongsTo
    {
        return $this->belongsTo(User::class, 'caller_id');
    }

    /**
     * User who received the call.
     */
    public function receiver(): BelongsTo
    {
        return $this->belongsTo(User::class, 'receiver_id');
    }

    /**
     * Optional chat room the call was initiated from.
     */
    public function room(): BelongsTo
    {
        return $this->belongsTo(InternalChatRoom::class, 'room_id');
    }

    /**
     * Helper to check if call is currently active or ringing.
     */
    public function isRinging(): bool
    {
        return $this->status === 'ringing';
    }

    public function isActive(): bool
    {
        return $this->status === 'active';
    }
}
