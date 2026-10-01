<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class MonnifyVirtualAccount extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id',
        'account_reference',
        'account_name',
        'bank_name',
        'bank_code',
        'bank_slug',
        'account_number',
        'reservation_status',
        'raw_response',
    ];

    protected $casts = [
        'raw_response' => 'array',
    ];

    protected $appends = [
        'logo',
        'badge_color',
        'border_accent',
        'gradient',
    ];

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function getLogoAttribute(): string
    {
        $slug = $this->bank_slug ?: self::resolveBankSlug($this->bank_code, $this->bank_name);
        return asset("images/banks/{$slug}.png");
    }

    public function getBadgeColorAttribute(): string
    {
        $slug = $this->bank_slug ?: self::resolveBankSlug($this->bank_code, $this->bank_name);
        return match ($slug) {
            'moniepoint' => 'bg-blue-50 dark:bg-blue-500/10 text-blue-700 dark:text-blue-300 border border-blue-200 dark:border-blue-500/30',
            'sterling' => 'bg-red-50 dark:bg-red-500/10 text-red-700 dark:text-red-300 border border-red-200 dark:border-red-500/30',
            'wema' => 'bg-purple-50 dark:bg-purple-500/10 text-purple-700 dark:text-purple-300 border border-purple-200 dark:border-purple-500/30',
            default => 'bg-slate-50 dark:bg-slate-700 text-slate-700 dark:text-slate-300 border border-slate-200',
        };
    }

    public function getBorderAccentAttribute(): string
    {
        $slug = $this->bank_slug ?: self::resolveBankSlug($this->bank_code, $this->bank_name);
        return match ($slug) {
            'moniepoint' => 'hover:border-blue-500/50 dark:hover:border-blue-500/50',
            'sterling' => 'hover:border-red-500/50 dark:hover:border-red-500/50',
            'wema' => 'hover:border-purple-500/50 dark:hover:border-purple-500/50',
            default => 'hover:border-slate-400',
        };
    }

    public function getGradientAttribute(): string
    {
        $slug = $this->bank_slug ?: self::resolveBankSlug($this->bank_code, $this->bank_name);
        return match ($slug) {
            'moniepoint' => 'from-blue-50/40 via-white to-blue-50/10 dark:from-slate-900 dark:via-slate-900 dark:to-blue-950/20',
            'sterling' => 'from-red-50/40 via-white to-red-50/10 dark:from-slate-900 dark:via-slate-900 dark:to-red-950/20',
            'wema' => 'from-purple-50/40 via-white to-purple-50/10 dark:from-slate-900 dark:via-slate-900 dark:to-purple-950/20',
            default => 'from-slate-50 via-white to-slate-50 dark:from-slate-900 dark:via-slate-900 dark:to-slate-800',
        };
    }

    public static function resolveBankSlug(?string $code, ?string $name = ''): string
    {
        $nameLower = strtolower($name ?? '');
        if ($code === '50515' || str_contains($nameLower, 'moniepoint')) {
            return 'moniepoint';
        }
        if ($code === '232' || str_contains($nameLower, 'sterling')) {
            return 'sterling';
        }
        if ($code === '035' || str_contains($nameLower, 'wema')) {
            return 'wema';
        }
        return 'default';
    }
}
