<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Order extends Model
{
    use HasFactory;

    protected $fillable = [
        'order_number',
        'customer_id',
        'artisan_id',
        'subtotal',
        'platform_fee',
        'total',
        'status',
        'customer_notes',
        'shipping_address',
        'shipping_barangay',
        'shipping_phone',
    ];

    protected $casts = [
        'subtotal' => 'decimal:2',
        'platform_fee' => 'decimal:2',
        'total' => 'decimal:2',
    ];

    // Relationships
    public function customer()
    {
        return $this->belongsTo(User::class, 'customer_id');
    }

    public function artisan()
    {
        return $this->belongsTo(User::class, 'artisan_id');
    }

    public function items()
    {
        return $this->hasMany(OrderItem::class);
    }

    public function payment()
    {
        return $this->hasOne(Payment::class);
    }

    public function messages()
    {
        return $this->hasMany(Message::class);
    }

    public function reviews()
    {
        return $this->hasMany(Review::class);
    }

    // Scopes
    public function scopePending($query)
    {
        return $query->where('status', 'pending');
    }

    public function scopeConfirmed($query)
    {
        return $query->where('status', 'confirmed');
    }

    public function scopeCompleted($query)
    {
        return $query->where('status', 'completed');
    }

    // Helpers
    public function isPending(): bool
    {
        return $this->status === 'pending';
    }

    public function isConfirmed(): bool
    {
        return $this->status === 'confirmed';
    }

    public function isCompleted(): bool
    {
        return $this->status === 'completed';
    }

    public function isCancelled(): bool
    {
        return $this->status === 'cancelled';
    }

    public function canBeCancelled(): bool
    {
        return $this->isPending() || $this->isConfirmed();
    }

    public function canBeCompleted(): bool
    {
        return $this->isConfirmed() && $this->payment?->isVerified();
    }

    // Generate unique order number
    protected static function boot()
    {
        parent::boot();

        static::creating(function ($order) {
            if (empty($order->order_number)) {
                $order->order_number = 'ORD-' . strtoupper(uniqid());
            }
        });
    }

    public function getRouteKeyName(): string
    {
        return 'order_number';
    }

    /**
     * Estimated delivery date: 2–3 days from order date.
     */
    public function getEstimatedDeliveryDateAttribute(): string
    {
        $orderDate = $this->created_at ?? now();
        $earliest = $orderDate->copy()->addDays(2);
        $latest = $orderDate->copy()->addDays(3);

        if ($earliest->format('M j') === $latest->format('M j')) {
            return $earliest->format('M j, Y');
        }

        return $earliest->format('M j') . '–' . $latest->format('M j, Y');
    }

    public function formattedShippingAddress(): string
    {
        if ($this->shipping_barangay) {
            $line = 'Barangay '.$this->shipping_barangay.', '.config('guihulngan.city_name').', '.config('guihulngan.province');
            if ($this->shipping_address) {
                return $line."\n".$this->shipping_address;
            }

            return $line;
        }

        return (string) ($this->shipping_address ?? '');
    }
}