<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Support\Str;

class ServiceRequest extends Model
{
    use HasFactory;

    /**
     * Bootstrap the model and its traits.
     */
    protected static function booted(): void
    {
        static::creating(function (ServiceRequest $model) {
            if (empty($model->reference)) {
                $model->reference = static::generateReference();
            }
        });
    }

    /**
     * Generate a unique service reference in the format REF-00000405-ABCD.
     * (Prefix 'REF-', 8-digit zero-padded integer, and 4 uppercase random characters).
     */
    public static function generateReference(?int $sequenceNumber = null): string
    {
        do {
            if ($sequenceNumber !== null) {
                $num = $sequenceNumber;
            } else {
                $maxId = (int) (static::max('id') ?? 0);
                $num = $maxId + 1;
            }

            $formatted = str_pad((string) $num, 8, '0', STR_PAD_LEFT);

            $suffix = '';
            for ($i = 0; $i < 4; $i++) {
                $suffix .= chr(random_int(65, 90));
            }

            $reference = "REF-{$formatted}-{$suffix}";
        } while (static::where('reference', $reference)->exists());

        return $reference;
    }

    /**
     * @var list<string>
     */
    protected $fillable = [
        'user_id',
        'service_id',
        'batch_id',
        'reference',
        'tracking_input',
        'input_payload',
        'amount_charged',
        'status',
        'assigned_to',
        'processed_by',
        'result_payload',
        'admin_notes',
        'rejection_reason',
        'refunded_at',
        'refunded_by',
        'completed_at',
    ];

    /**
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'amount_charged' => 'decimal:2',
            'input_payload' => 'array',
            'result_payload' => 'array',
            'refunded_at' => 'datetime',
            'completed_at' => 'datetime',
        ];
    }

    /**
     * The customer who submitted the request.
     */
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    /**
     * The service associated with this request.
     */
    public function service(): BelongsTo
    {
        return $this->belongsTo(Service::class);
    }

    /**
     * The parent bulk batch if originated from a bulk submission.
     */
    public function batch(): BelongsTo
    {
        return $this->belongsTo(ServiceBatch::class, 'batch_id');
    }

    /**
     * Staff member assigned to pick and process this manual request.
     */
    public function assignedStaff(): BelongsTo
    {
        return $this->belongsTo(User::class, 'assigned_to');
    }

    /**
     * Staff or Admin who completed or failed this request.
     */
    public function processedBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'processed_by');
    }

    /**
     * Staff or Admin who manually issued a refund for this request.
     */
    public function refundedBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'refunded_by');
    }

    /**
     * Status check helpers.
     */
    public function isPending(): bool
    {
        return $this->status === 'pending';
    }

    public function isProcessing(): bool
    {
        return $this->status === 'processing';
    }

    public function isCompleted(): bool
    {
        return $this->status === 'completed';
    }

    public function isFailed(): bool
    {
        return $this->status === 'failed';
    }

    public function isRefunded(): bool
    {
        return !is_null($this->refunded_at);
    }

    /**
     * Formatted amount in Naira.
     */
    public function getFormattedAmountAttribute(): string
    {
        return '₦' . number_format((float) $this->amount_charged, 2);
    }

    /**
     * 8-digit integer identifier for the batch.
     */
    public function getBatchNumberAttribute(): string
    {
        if ($this->batch) {
            return $this->batch->batch_number;
        }
        if ($this->batch_id) {
            return str_pad((string) (10000000 + ($this->batch_id % 90000000)), 8, '0', STR_PAD_LEFT);
        }
        return str_pad((string) (10000000 + ($this->id % 90000000)), 8, '0', STR_PAD_LEFT);
    }

    /**
     * Text representation of the admin or system reply.
     */
    public function getReplyTextAttribute(): string
    {
        if ($this->status === 'failed' && $this->rejection_reason) {
            return $this->rejection_reason;
        }
        if ($this->admin_notes) {
            return $this->admin_notes;
        }
        if (! empty($this->result_payload['resolution_reference'])) {
            return (string) $this->result_payload['resolution_reference'];
        }
        if ($this->status === 'completed') {
            return 'Cleared';
        }
        if ($this->status === 'processing') {
            return 'Processing';
        }
        return 'Pending';
    }
}
