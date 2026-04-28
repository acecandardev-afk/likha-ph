<?php

namespace App\Models;

use App\Services\DeliveryService;
use App\Services\LedgerPostingService;
use App\Support\PublicMediaUrl;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;

class Order extends Model
{
    use HasFactory;

    protected $fillable = [
        'order_number',
        'customer_id',
        'artisan_id',
        'subtotal',
        'platform_fee',
        'shipping_amount',
        'tax_amount',
        'discount_amount',
        'voucher_code',
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
        'approved_at',
        'rider_id',
        'delivery_status',
        'delivery_assigned_at',
        'delivery_completed_at',
        'delivery_proof_image',
    ];

    protected $casts = [
        'subtotal' => 'decimal:2',
        'platform_fee' => 'decimal:2',
        'shipping_amount' => 'decimal:2',
        'tax_amount' => 'decimal:2',
        'discount_amount' => 'decimal:2',
        'total' => 'decimal:2',
        'approved_at' => 'datetime',
        'delivery_assigned_at' => 'datetime',
        'delivery_completed_at' => 'datetime',
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

    public function rider()
    {
        return $this->belongsTo(Rider::class);
    }

    public function deliveryHistory()
    {
        return $this->hasMany(OrderDeliveryHistory::class)->orderBy('status_at');
    }

    public function packages()
    {
        return $this->hasMany(OrderPackage::class)->orderBy('sequence');
    }

    public function ledgerJournals()
    {
        return $this->hasMany(LedgerJournal::class);
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

    public function scopeApproved($query)
    {
        return $query->where('status', 'approved');
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

    public function scopePendingDelivery($query)
    {
        return $query->where('delivery_status', 'pending_assignment');
    }

    public function scopeCompleted($query)
    {
        return $query->where('status', 'completed');
    }

    public function scopeConfirmed($query)
    {
        return $query->where('status', 'confirmed');
    }

    /**
     * Orders whose payment has been verified (COD at checkout or admin-approved proof).
     */
    public function scopeWherePaymentVerified($query)
    {
        return $query->whereHas('payment', fn ($q) => $q->where('verification_status', 'verified'));
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

    public function isApproved(): bool
    {
        return $this->status === 'approved';
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

    public function isDeliveryCompleted(): bool
    {
        return $this->delivery_status === 'delivered';
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

    public function canBeApproved(): bool
    {
        return $this->isPending() && ($this->payment?->isVerified() ?? false);
    }

    public function canBeCompleted(): bool
    {
        return $this->isDelivered() && $this->payment?->isVerified();
    }

    /**
     * Seller has confirmed the order; riders may be assigned and delivery can proceed.
     */
    public function isSellerApprovedForFulfillment(): bool
    {
        return $this->isShipped();
    }

    /**
     * Merchant portion of product sales after promotions and service fee (before delivery/tax lines).
     */
    public function artisanMerchandiseShare(): float
    {
        $discount = (float) ($this->discount_amount ?? 0);

        return round(max(0, (float) $this->subtotal - $discount - (float) $this->platform_fee), 2);
    }

    public function deliveryStatusLabel(): string
    {
        return match ($this->delivery_status) {
            'order_confirmed' => 'Order Confirmed',
            'preparing_package' => 'Preparing Package',
            'package_picked_up' => 'Package Picked Up',
            'arrived_sort_center' => 'Package Arrived at Sort Center',
            'out_for_delivery' => 'Out for Delivery',
            'delivered' => 'Delivered',
            'pending_assignment' => 'Pending Delivery Assignment',
            default => ucwords(str_replace('_', ' ', (string) $this->delivery_status)),
        };
    }

    public function getDeliveryProofImageUrlAttribute(): ?string
    {
        if (! $this->delivery_proof_image) {
            return null;
        }

        return PublicMediaUrl::url('delivery_proofs', $this->delivery_proof_image);
    }

    // Generate unique order number
    protected static function boot()
    {
        parent::boot();

        static::creating(function ($order) {
            if (empty($order->order_number)) {
                $order->order_number = 'ORD-'.strtoupper(uniqid());
            }
        });

        static::deleting(function ($order) {
            if ($order->delivery_proof_image) {
                Storage::disk('delivery_proofs')->delete($order->delivery_proof_image);
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

        return $earliest->format('M j').'–'.$latest->format('M j, Y');
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
            $addressParts[] = 'Barangay '.$this->barangay;
        }

        if ($this->street_address) {
            $addressParts[] = $this->street_address;
        }

        return implode(', ', $addressParts);
    }

    /**
     * Sync orders.rider_id, delivery_status, delivery_proof_image, etc. from child packages (bottleneck + totals).
     */
    public function refreshAggregateDeliveryFromPackages(): void
    {
        $this->loadMissing('packages');
        $packages = $this->packages;
        if ($packages->isEmpty()) {
            return;
        }

        if ($this->isCancelled()) {
            return;
        }

        $rank = DeliveryService::statusRank();
        $bottleneck = $packages->sortBy(fn ($p) => $rank[$p->delivery_status] ?? 0)->first();
        $allDelivered = $packages->every(fn ($p) => $p->delivery_status === DeliveryService::STATUS_DELIVERED);
        $riderIds = $packages->pluck('rider_id')->filter()->unique();

        $pendingRank = $rank[DeliveryService::STATUS_PENDING_ASSIGNMENT];
        $deliveredRank = $rank[DeliveryService::STATUS_DELIVERED];
        $anyInTransit = $packages->contains(function ($p) use ($rank, $pendingRank, $deliveredRank) {
            $r = $rank[$p->delivery_status] ?? 0;

            return $r > $pendingRank && $r < $deliveredRank;
        });

        $updates = [
            'delivery_status' => $bottleneck->delivery_status,
            'rider_id' => $riderIds->count() === 1 ? $riderIds->first() : $bottleneck->rider_id,
            'delivery_proof_image' => $packages->count() === 1 ? $packages->first()->delivery_proof_image : null,
            'delivery_completed_at' => $allDelivered ? $packages->max('delivery_completed_at') : null,
            'delivery_assigned_at' => $packages->whereNotNull('delivery_assigned_at')->min('delivery_assigned_at'),
        ];

        if ($allDelivered) {
            $updates['status'] = 'delivered';
        } elseif ($anyInTransit && ! $this->isPending()) {
            $updates['status'] = 'on_delivery';
        }

        $this->update($updates);

        if ($allDelivered) {
            try {
                app(LedgerPostingService::class)->postDeliverySettlementIfNeeded(
                    $this->fresh(['packages', 'payment'])
                );
            } catch (\Throwable $e) {
                Log::error('ledger_post_failed', [
                    'order_id' => $this->id,
                    'message' => $e->getMessage(),
                ]);
            }
        }
    }
}
