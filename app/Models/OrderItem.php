<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class OrderItem extends Model
{
    use HasFactory;

    protected $fillable = [
        'order_id',
        'product_id',
        'product_name',
        'product_description',
        'price',
        'quantity',
        'subtotal',
    ];

    protected $casts = [
        'price' => 'decimal:2',
        'subtotal' => 'decimal:2',
    ];

    // Relationships
    public function order()
    {
        return $this->belongsTo(Order::class);
    }

    public function product()
    {
        return $this->belongsTo(Product::class);
    }

    public function returns()
    {
        return $this->hasMany(OrderItemReturn::class);
    }

    // Helpers
    public function calculateSubtotal(): float
    {
        return $this->price * $this->quantity;
    }

    /**
     * Units of this line still available to request a return for (excludes approved & pending admin returns).
     */
    public function returnableQuantity(): int
    {
        if ($this->relationLoaded('returns')) {
            $reserved = (int) $this->returns
                ->whereIn('status', [OrderItemReturn::STATUS_PENDING_ADMIN, OrderItemReturn::STATUS_APPROVED])
                ->sum('quantity');

            return max(0, (int) $this->quantity - $reserved);
        }

        $reserved = (int) $this->returns()
            ->whereIn('status', [OrderItemReturn::STATUS_PENDING_ADMIN, OrderItemReturn::STATUS_APPROVED])
            ->sum('quantity');

        return max(0, (int) $this->quantity - $reserved);
    }
}
