<?php

namespace App\Models;

use App\Services\DeliveryService;
use App\Support\PublicMediaUrl;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Storage;

class OrderPackage extends Model
{
    use HasFactory;

    protected $fillable = [
        'order_id',
        'sequence',
        'rider_id',
        'delivery_status',
        'delivery_assigned_at',
        'delivery_completed_at',
        'delivery_proof_image',
        'platform_fee_share',
        'platform_fee_realized_at',
        'rider_fee_amount',
    ];

    protected $casts = [
        'delivery_assigned_at' => 'datetime',
        'delivery_completed_at' => 'datetime',
        'platform_fee_share' => 'decimal:2',
        'platform_fee_realized_at' => 'datetime',
        'rider_fee_amount' => 'decimal:2',
    ];

    public function order()
    {
        return $this->belongsTo(Order::class);
    }

    public function rider()
    {
        return $this->belongsTo(Rider::class);
    }

    public function items()
    {
        return $this->hasMany(OrderPackageItem::class);
    }

    public function deliveryReports()
    {
        return $this->hasMany(DeliveryReport::class);
    }

    public function scopePendingAssignment($query)
    {
        return $query->where('delivery_status', DeliveryService::STATUS_PENDING_ASSIGNMENT);
    }

    public function scopeDelivered($query)
    {
        return $query->where('delivery_status', DeliveryService::STATUS_DELIVERED);
    }

    public function scopeNotDelivered($query)
    {
        return $query->where('delivery_status', '!=', DeliveryService::STATUS_DELIVERED);
    }

    public function label(): string
    {
        return 'Pkg #'.$this->sequence;
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

    public function isDelivered(): bool
    {
        return $this->delivery_status === DeliveryService::STATUS_DELIVERED;
    }

    /**
     * Sum of merchandise value for this package (order line price × qty in package).
     */
    public function merchandiseTotal(): float
    {
        $this->loadMissing(['items.orderItem']);

        return (float) $this->items->sum(function (OrderPackageItem $opi) {
            $oi = $opi->orderItem;
            if (! $oi) {
                return 0;
            }

            return (float) $oi->price * (int) $opi->quantity;
        });
    }

    /**
     * Human-readable delivered timestamp with timezone (for admin rider sales profile).
     */
    public function deliveredAtLabel(): string
    {
        $at = $this->delivery_completed_at;
        if (! $at) {
            return '—';
        }

        $tz = config('app.timezone');

        return $at->timezone($tz)->format('M j, Y').' '.$at->timezone($tz)->format('g:i:s A').' ('.$tz.')';
    }

    protected static function boot()
    {
        parent::boot();

        static::deleting(function (OrderPackage $package) {
            if ($package->delivery_proof_image) {
                Storage::disk('delivery_proofs')->delete($package->delivery_proof_image);
            }
        });
    }
}
