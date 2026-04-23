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
        'country',
        'region',
        'province',
        'city',
        'barangay',
        'street_address',
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

    public function region()
    {
        return $this->belongsTo(Region::class, 'region', 'name');
    }

    public function province()
    {
        return $this->belongsTo(Province::class, 'province', 'name');
    }

    public function city()
    {
        return $this->belongsTo(City::class, 'city', 'name');
    }

    public function barangay()
    {
        return $this->belongsTo(Barangay::class, 'barangay', 'name');
    }

    // Scopes
    public function scopePending($query)
    {
        return $query->where('status', 'pending');
    }

    public function scopeShipped($query)
    {
        return $query->where('status', 'shipped');
    }

    public function scopeOnDelivery($query)
    {
        return $query->where('status', 'on_delivery');
    }

    public function scopeReceived($query)
    {
        return $query->where('status', 'received');
    }

    public function scopeDelivered($query)
    {
        return $query->where('status', 'delivered');
    }

    public function scopeCompleted($query)
    {
        return $query->where('status', 'completed');
    }

    public function scopeConfirmed($query)
    {
        return $query->where('status', 'confirmed');
    }

    // Helpers
    public function isPending(): bool
    {
        return $this->status === 'pending';
    }

    public function isShipped(): bool
    {
        return $this->status === 'shipped';
    }

    public function isOnDelivery(): bool
    {
        return $this->status === 'on_delivery';
    }

    public function isReceived(): bool
    {
        return $this->status === 'received';
    }

    public function isDelivered(): bool
    {
        return $this->status === 'delivered';
    }

    public function isCompleted(): bool
    {
        return $this->status === 'completed';
    }

    public function isConfirmed(): bool
    {
        return $this->status === 'confirmed';
    }

    public function isCancelled(): bool
    {
        return $this->status === 'cancelled';
    }

    /**
     * Buyers may only cancel while the order is still pending (not shipped or beyond).
     */
    public function canBeCancelled(): bool
    {
        return $this->isPending();
    }

    public function canBeReceived(): bool
    {
        return $this->isOnDelivery();
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
        $addressParts = [];

        if ($this->country) {
            $addressParts[] = $this->country;
        }

        if ($this->region) {
            $addressParts[] = $this->region;
        }

        if ($this->province) {
            $addressParts[] = $this->province;
        }

        if ($this->city) {
            $addressParts[] = $this->city;
        }

        if ($this->barangay) {
            $addressParts[] = 'Barangay ' . $this->barangay;
        }

        if ($this->street_address) {
            $addressParts[] = $this->street_address;
        }

        return implode(', ', $addressParts);
    }
}