<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;

class InternalChatRoom extends Model
{
    use HasFactory;

    protected $fillable = [
        'name',
        'type',
        'created_by',
        'is_support',
        'assigned_staff_id',
    ];

    protected $casts = [
        'is_support' => 'boolean',
        'assigned_staff_id' => 'integer',
    ];

    /**
     * Staff member assigned to attend to this customer support room.
     */
    public function assignedStaff(): BelongsTo
    {
        return $this->belongsTo(User::class, 'assigned_staff_id');
    }

    /**
     * Users who are members of this chat room.
     */
    public function users(): BelongsToMany
    {
        return $this->belongsToMany(User::class, 'internal_chat_room_user', 'room_id', 'user_id')
            ->withPivot('last_read_at')
            ->withTimestamps();
    }

    /**
     * Messages posted in this room.
     */
    public function messages(): HasMany
    {
        return $this->hasMany(InternalChatMessage::class, 'room_id')->orderBy('created_at', 'asc');
    }

    /**
     * User who created the room.
     */
    public function creator(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    /**
     * Call sessions associated with this room.
     */
    public function callSessions(): HasMany
    {
        return $this->hasMany(InternalCallSession::class, 'room_id');
    }

    /**
     * Calculate unread message count for a specific user.
     */
    public function unreadCountForUser(int|User $user): int
    {
        $userId = $user instanceof User ? $user->id : $user;
        $pivot = $this->users()->where('users.id', $userId)->first()?->pivot;
        $lastRead = $pivot?->last_read_at;

        $query = $this->messages()->where('user_id', '!=', $userId);
        if ($lastRead) {
            $query->where('created_at', '>', $lastRead);
        }

        return $query->count();
    }

    /**
     * Format display name for the room depending on type and current viewing user.
     */
    public function getDisplayNameForUser(User $user): string
    {
        if ($this->is_support) {
            if ($user->hasAdminBackendAccess()) {
                $customer = $this->relationLoaded('users')
                    ? $this->users->first(fn ($u) => ! $u->hasAdminBackendAccess())
                    : $this->users()->whereDoesntHave('roles', fn ($q) => $q->whereIn('slug', ['super_admin', 'admin', 'staff']))->first();
                return $customer ? $customer->full_name . ' (Customer)' : ($this->name ?? 'Customer Support');
            } else {
                $attending = $this->assignedStaff;
                return $attending ? $attending->full_name . ' • Support Representative' : 'Customer Support Desk';
            }
        }

        if ($this->type === 'channel') {
            return $this->name ?? 'Channel';
        }

        // For direct chats, show the other party's full name
        $otherUser = $this->relationLoaded('users')
            ? $this->users->firstWhere('id', '!=', $user->id)
            : $this->users()->where('users.id', '!=', $user->id)->first();
        return $otherUser ? $otherUser->full_name : ($this->name ?? 'Direct Chat');
    }
}
