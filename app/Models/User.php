<?php

namespace App\Models;

use Database\Factories\UserFactory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;

class User extends Authenticatable
{
    /** @use HasFactory<UserFactory> */
    use HasFactory, Notifiable;

    /**
     * The attributes that are mass assignable.
     *
     * @var list<string>
     */
    protected $fillable = [
        'first_name',
        'surname',
        'middle_name',
        'email',
        'phone_number',
        'business',
        'password',
        'email_verified_at',
        'phone_verified_at',
        'is_active',
        'last_seen_at',
    ];

    /**
     * The attributes that should be hidden for serialization.
     *
     * @var list<string>
     */
    protected $hidden = [
        'password',
        'remember_token',
    ];

    /**
     * Get the attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'email_verified_at' => 'datetime',
            'phone_verified_at' => 'datetime',
            'password' => 'hashed',
            'is_active' => 'boolean',
            'last_seen_at' => 'datetime',
        ];
    }

    /**
     * Full name accessor combining first name, optional middle name, and surname.
     */
    public function getFullNameAttribute(): string
    {
        $parts = array_filter([$this->first_name, $this->middle_name, $this->surname]);
        return implode(' ', $parts);
    }

    /**
     * Roles relationship.
     */
    public function roles(): BelongsToMany
    {
        return $this->belongsToMany(Role::class, 'role_user');
    }

    /**
     * Check if user holds a specific role slug or any from an array of slugs.
     */
    public function hasRole(string|array $roles): bool
    {
        $roles = (array) $roles;
        return $this->roles->pluck('slug')->intersect($roles)->isNotEmpty();
    }

    /**
     * Check if user holds any of the given role slugs.
     */
    public function hasAnyRole(array $roles): bool
    {
        return $this->hasRole($roles);
    }

    /**
     * Check if user has a specific permission via any of their assigned roles.
     */
    public function hasPermission(string $permissionSlug): bool
    {
        // Super Admin bypasses all specific permission gates
        if ($this->hasRole('super_admin')) {
            return true;
        }

        foreach ($this->roles as $role) {
            if ($role->permissions->contains('slug', $permissionSlug)) {
                return true;
            }
        }

        return false;
    }

    /**
     * Determines whether the user is authorized to see the "Admin" button
     * and access the administrative backend section (/admin/*).
     *
     * Per SRS Section 9:
     * Authorized roles: Super Admin, Admin, Staff.
     * Unauthorized roles: Agent, End User.
     */
    public function hasAdminBackendAccess(): bool
    {
        return $this->hasAnyRole(['super_admin', 'admin', 'staff']);
    }

    /**
     * Check whether both email and phone verifications are complete.
     */
    public function isFullyVerified(): bool
    {
        return !is_null($this->email_verified_at) && !is_null($this->phone_verified_at);
    }

    /**
     * Check whether email verification is complete.
     */
    public function isEmailVerified(): bool
    {
        return !is_null($this->email_verified_at);
    }

    /**
     * Check whether phone verification is complete.
     */
    public function isPhoneVerified(): bool
    {
        return !is_null($this->phone_verified_at);
    }

    /**
     * User's wallet relationship.
     */
    public function wallet(): \Illuminate\Database\Eloquent\Relations\HasOne
    {
        return $this->hasOne(Wallet::class);
    }

    /**
     * User's wallet transactions ledger.
     */
    public function walletTransactions(): \Illuminate\Database\Eloquent\Relations\HasMany
    {
        return $this->hasMany(WalletTransaction::class);
    }

    /**
     * Get the current wallet balance formatted as decimal float.
     */
    public function getWalletBalanceAttribute(): float
    {
        return (float) ($this->wallet?->balance ?? 0.00);
    }

    /**
     * Check if user is authorized to fund other users from their own wallet balance.
     * Per SRS:
     * Check if the user is authorized to send funds to other users:
     * - All verified portal users can send funds from their own wallet balance.
     */
    public function canFundOtherUsers(): bool
    {
        return true;
    }

    /**
     * Internal chat rooms the user belongs to.
     */
    public function chatRooms(): BelongsToMany
    {
        return $this->belongsToMany(InternalChatRoom::class, 'internal_chat_room_user', 'user_id', 'room_id')
            ->withPivot('last_read_at')
            ->withTimestamps();
    }

    /**
     * Chat messages authored by this user.
     */
    public function sentChatMessages(): HasMany
    {
        return $this->hasMany(InternalChatMessage::class, 'user_id');
    }

    /**
     * Internal calls placed by this user.
     */
    public function placedCalls(): HasMany
    {
        return $this->hasMany(InternalCallSession::class, 'caller_id');
    }

    /**
     * Internal calls received by this user.
     */
    public function receivedCalls(): HasMany
    {
        return $this->hasMany(InternalCallSession::class, 'receiver_id');
    }

    /**
     * Check if user was active recently (within last 4 minutes).
     */
    public function isOnline(): bool
    {
        return $this->last_seen_at && $this->last_seen_at->gte(now()->subMinutes(4));
    }

    /**
     * Format presence status in WhatsApp format ("online" or "last seen ...").
     */
    public function getPresenceStatusAttribute(): string
    {
        if ($this->isOnline()) {
            return 'online';
        }

        if ($this->last_seen_at) {
            return 'last seen ' . $this->last_seen_at->diffForHumans();
        }

        return 'offline';
    }

    /**
     * Check if at least one administrator or staff member is online.
     */
    public static function isAnyAdminOnline(): bool
    {
        return self::whereHas('roles', function ($q) {
            $q->whereIn('slug', ['super_admin', 'admin', 'staff']);
        })
            ->where('last_seen_at', '>=', now()->subMinutes(4))
            ->exists();
    }

    /**
     * Get aggregate support presence info (WhatsApp style) and attending admin attribution.
     */
    public static function getSupportPresenceInfo(?User $attendingStaff = null): array
    {
        if ($attendingStaff) {
            $isOnline = $attendingStaff->isOnline();
            return [
                'is_online' => $isOnline,
                'status' => $isOnline ? 'online' : 'offline',
                'label' => $attendingStaff->presence_status,
                'attending_admin' => [
                    'id' => $attendingStaff->id,
                    'name' => $attendingStaff->full_name,
                    'role' => $attendingStaff->roles->pluck('name')->first() ?? 'Support Representative',
                    'is_online' => $isOnline,
                ],
            ];
        }

        $anyOnline = self::isAnyAdminOnline();
        return [
            'is_online' => $anyOnline,
            'status' => $anyOnline ? 'online' : 'offline',
            'label' => $anyOnline ? 'online' : 'offline (last seen recently)',
            'attending_admin' => null,
        ];
    }

    /**
     * Monnify virtual accounts assigned to this user.
     */
    public function monnifyVirtualAccounts(): HasMany
    {
        return $this->hasMany(MonnifyVirtualAccount::class);
    }

    /**
     * Get the dedicated static Monnify virtual accounts for this user (Moniepoint, Sterling, and Wema).
     *
     * @return array<int, array<string, mixed>>
     */
    public function getMonnifyVirtualAccounts(): array
    {
        $accounts = $this->monnifyVirtualAccounts()->get();

        if ($accounts->isEmpty()) {
            app(\App\Services\Payment\MonnifyService::class)->getOrCreateReservedAccounts($this);
            $accounts = $this->monnifyVirtualAccounts()->get();
        }

        $bankOrder = ['50515' => 1, '232' => 2, '035' => 3];
        $sorted = $accounts->sortBy(function (MonnifyVirtualAccount $acc) use ($bankOrder) {
            return $bankOrder[$acc->bank_code] ?? 99;
        })->values();

        return $sorted->map(function (MonnifyVirtualAccount $acc) {
            return [
                'id' => $acc->id,
                'bank_name' => $acc->bank_name,
                'bank_code' => $acc->bank_code,
                'bank_slug' => $acc->bank_slug,
                'account_number' => $acc->account_number,
                'account_name' => $acc->account_name,
                'account_reference' => $acc->account_reference,
                'reservation_status' => $acc->reservation_status,
                'logo' => $acc->logo,
                'badge_color' => $acc->badge_color,
                'border_accent' => $acc->border_accent,
                'gradient' => $acc->gradient,
            ];
        })->toArray();
    }

    /**
     * Find a user by one of their Monnify virtual account numbers.
     */
    public static function findByVirtualAccount(string $accountNumber): ?self
    {
        $accountNumber = trim($accountNumber);
        if (empty($accountNumber)) {
            return null;
        }

        $virtualAcc = MonnifyVirtualAccount::where('account_number', $accountNumber)->first();
        if ($virtualAcc) {
            return $virtualAcc->user;
        }

        return null;
    }
}

