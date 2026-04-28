<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Voucher extends Model
{
    use HasFactory;

    protected $fillable = [
        'code',
        'label',
        'discount_type',
        'discount_value',
        'min_order_amount',
        'maximum_discount_amount',
        'max_redemptions',
        'times_redeemed',
        'starts_at',
        'ends_at',
        'is_active',
    ];

    protected $casts = [
        'discount_value' => 'decimal:2',
        'min_order_amount' => 'decimal:2',
        'maximum_discount_amount' => 'decimal:2',
        'starts_at' => 'datetime',
        'ends_at' => 'datetime',
        'is_active' => 'boolean',
    ];

    protected static function booted(): void
    {
        static::saving(function (Voucher $voucher) {
            $voucher->code = strtoupper(trim((string) $voucher->code));
        });
    }

    public function isCurrentlyValid(?\Carbon\Carbon $at = null): bool
    {
        $at ??= now();

        if (! $this->is_active) {
            return false;
        }

        if ($this->starts_at && $at->lt($this->starts_at)) {
            return false;
        }

        if ($this->ends_at && $at->gt($this->ends_at)) {
            return false;
        }

        if ($this->max_redemptions !== null && $this->times_redeemed >= $this->max_redemptions) {
            return false;
        }

        return true;
    }

    public function computedDiscount(float $cartSubtotal): float
    {
        if ($cartSubtotal < (float) $this->min_order_amount) {
            return 0.0;
        }

        if ($this->discount_type === 'fixed') {
            return round(min((float) $this->discount_value, $cartSubtotal), 2);
        }

        $pct = max(0, (float) $this->discount_value) / 100;
        $amount = round($cartSubtotal * $pct, 2);
        $cap = $this->maximum_discount_amount;

        if ($cap !== null) {
            $amount = min($amount, (float) $cap);
        }

        return min($amount, $cartSubtotal);
    }
}
