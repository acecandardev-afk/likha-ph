<?php

namespace App\Models;

use App\Services\NotificationService;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Cache;

class Product extends Model
{
    protected $fillable = [
        'artisan_id',
        'category_id',
        'name',
        'description',
        'price',
        'stock',
        'approval_status',
        'is_active',
    ];

    protected $casts = [
        'price' => 'decimal:2',
        'is_active' => 'boolean',
    ];

    protected static function booted(): void
    {
        static::deleting(function (Product $product) {
            $cartUserIds = Cart::query()
                ->where('product_id', $product->id)
                ->pluck('user_id')
                ->map(fn ($id) => (int) $id)
                ->unique()
                ->all();

            app(NotificationService::class)->removeNotificationsForDeletedProduct($product->id);

            foreach ($cartUserIds as $uid) {
                Cache::forget("ui:cartCount:{$uid}");
            }
        });
    }

    // Relationships
    public function artisan()
    {
        return $this->belongsTo(User::class, 'artisan_id');
    }

    public function category()
    {
        return $this->belongsTo(Category::class);
    }

    public function images()
    {
        return $this->hasMany(ProductImage::class);
    }

    public function primaryImage()
    {
        return $this->hasOne(ProductImage::class)->where('is_primary', true);
    }

    public function approvals()
    {
        return $this->hasMany(ProductApproval::class);
    }

    public function latestApproval()
    {
        return $this->hasOne(ProductApproval::class)->latestOfMany();
    }

    public function reviews()
    {
        return $this->hasMany(Review::class);
    }

    public function approvedReviews()
    {
        return $this->hasMany(Review::class)->where('is_approved', true)->orderBy('created_at', 'desc');
    }

    public function orderItems()
    {
        return $this->hasMany(OrderItem::class);
    }

    // Scopes
    public function scopeApproved($query)
    {
        return $query->where('approval_status', 'approved');
    }

    public function scopePendingApproval($query)
    {
        return $query->where('approval_status', 'pending');
    }

    /**
     * Scope a query to only include products that are waiting for admin approval.
     *
     * Alias for scopePendingApproval to match usage like Product::pending().
     */
    public function scopePending($query)
    {
        return $this->scopePendingApproval($query);
    }

    public function scopeRejected($query)
    {
        return $query->where('approval_status', 'rejected');
    }

    public function scopeActive($query)
    {
        return $query->where('is_active', true);
    }

    /**
     * Scope a query to only include products that are visible publicly
     * on the storefront.
     */
    public function scopePublic($query)
    {
        return $query
            ->approved()
            ->active()
            ->where('stock', '>', 0);
    }

    /**
     * On the public marketplace, artisans must not see their own listings (shop, home, profile).
     */
    public function scopeVisibleToShopper($query, ?User $viewer = null)
    {
        if ($viewer?->isArtisan()) {
            $query->where('artisan_id', '!=', $viewer->id);
        }

        return $query;
    }

    public function isOwnedBy(User|int $user): bool
    {
        $id = $user instanceof User ? $user->id : $user;

        return (int) $this->artisan_id === (int) $id;
    }

    // Helpers
    public function isApproved(): bool
    {
        return $this->approval_status === 'approved' && $this->is_active;
    }

    public function isAvailable(): bool
    {
        // A product is available if it's approved, active, and has stock
        return $this->isApproved() && $this->stock > 0;
    }
}
