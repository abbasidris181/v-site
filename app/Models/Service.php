<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Service extends Model
{
    use HasFactory;

    /**
     * @var list<string>
     */
    protected $fillable = [
        'category_id',
        'name',
        'slug',
        'type',
        'price',
        'description',
        'input_label',
        'input_placeholder',
        'validation_rule',
        'provider_driver',
        'is_bulk_allowed',
        'is_active',
        'sort_order',
        'fields_schema',
    ];

    /**
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'price' => 'decimal:2',
            'is_bulk_allowed' => 'boolean',
            'is_active' => 'boolean',
            'sort_order' => 'integer',
            'fields_schema' => 'array',
        ];
    }

    /**
     * The category this service belongs to.
     */
    public function category(): BelongsTo
    {
        return $this->belongsTo(ServiceCategory::class, 'category_id');
    }

    /**
     * The individual service requests submitted for this service.
     */
    public function requests(): HasMany
    {
        return $this->hasMany(ServiceRequest::class);
    }

    /**
     * The bulk batches submitted for this service.
     */
    public function batches(): HasMany
    {
        return $this->hasMany(ServiceBatch::class);
    }

    /**
     * Formatted price in Naira (e.g. ₦500.00).
     */
    public function getFormattedPriceAttribute(): string
    {
        return '₦' . number_format((float) $this->price, 2);
    }

    /**
     * Check if service is processed automatically via API.
     */
    public function isApi(): bool
    {
        return $this->type === 'api';
    }

    /**
     * Check if service is processed manually via back-office.
     */
    public function isManual(): bool
    {
        return $this->type === 'manual';
    }
}
