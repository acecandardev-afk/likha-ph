<?php

namespace App\Models;

use App\Support\PublicMediaUrl;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Support\Facades\Storage;

class OrderItemReturn extends Model
{
    public const STATUS_PENDING_ADMIN = 'pending_admin';

    public const STATUS_APPROVED = 'approved';

    public const STATUS_REJECTED = 'rejected';

    public const REASON_WRONG_ITEM = 'wrong_item';

    public const REASON_MISSING_PARTS = 'missing_parts';

    public const REASON_EXPIRED = 'expired';

    public const REASON_DAMAGED = 'damaged';

    /** @return array<string, string> */
    public static function reasonLabels(): array
    {
        return [
            self::REASON_WRONG_ITEM => 'Wrong item',
            self::REASON_MISSING_PARTS => 'Missing parts',
            self::REASON_EXPIRED => 'Expired',
            self::REASON_DAMAGED => 'Damaged',
        ];
    }

    public static function reasonKeys(): array
    {
        return array_keys(self::reasonLabels());
    }

    protected $fillable = [
        'order_id',
        'order_item_id',
        'customer_id',
        'artisan_id',
        'quantity',
        'reason',
        'notes',
        'proof_image',
        'status',
        'admin_resolution_notes',
        'reviewed_by',
        'reviewed_at',
        'stock_restored_at',
    ];

    protected function casts(): array
    {
        return [
            'reviewed_at' => 'datetime',
            'stock_restored_at' => 'datetime',
        ];
    }

    public function order(): BelongsTo
    {
        return $this->belongsTo(Order::class);
    }

    public function orderItem(): BelongsTo
    {
        return $this->belongsTo(OrderItem::class);
    }

    public function customer(): BelongsTo
    {
        return $this->belongsTo(User::class, 'customer_id');
    }

    public function artisan(): BelongsTo
    {
        return $this->belongsTo(User::class, 'artisan_id');
    }

    public function reviewer(): BelongsTo
    {
        return $this->belongsTo(User::class, 'reviewed_by');
    }

    public function reasonLabel(): string
    {
        return self::reasonLabels()[$this->reason] ?? ucfirst(str_replace('_', ' ', (string) $this->reason));
    }

    public function statusLabel(): string
    {
        return match ($this->status) {
            self::STATUS_PENDING_ADMIN => 'Pending review',
            self::STATUS_APPROVED => 'Approved',
            self::STATUS_REJECTED => 'Rejected',
            default => ucfirst((string) $this->status),
        };
    }

    public function getProofImageUrlAttribute(): string
    {
        return PublicMediaUrl::url('order_returns', $this->proof_image);
    }

    public function isPendingAdmin(): bool
    {
        return $this->status === self::STATUS_PENDING_ADMIN;
    }

    public function isApproved(): bool
    {
        return $this->status === self::STATUS_APPROVED;
    }

    public function scopePendingAdmin($query)
    {
        return $query->where('status', self::STATUS_PENDING_ADMIN);
    }

    protected static function boot(): void
    {
        parent::boot();

        static::deleting(function (OrderItemReturn $return) {
            if ($return->proof_image) {
                Storage::disk('order_returns')->delete($return->proof_image);
            }
        });
    }
}
