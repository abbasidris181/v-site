<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class InternalChatMessage extends Model
{
    use HasFactory;

    protected $fillable = [
        'room_id',
        'user_id',
        'message',
    ];

    /**
     * The chat room this message belongs to.
     */
    public function room(): BelongsTo
    {
        return $this->belongsTo(InternalChatRoom::class, 'room_id');
    }

    /**
     * The user who authored the message.
     */
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class, 'user_id');
    }
}
