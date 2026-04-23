<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;

class User extends Authenticatable
{
    use HasFactory, Notifiable;

    protected $fillable = [
        'name',
        'email',
        'google_id',
        'avatar_url',
        'password',
        'role',
        'phone',
        'address',
        'country',
        'region',
        'province',
        'city',
        'barangay',
        'street_address',
        'shipping_address',
        'shipping_barangay',
        'shipping_phone',
        'status',
    ];

    protected $hidden = [
        'password',
        'remember_token',
    ];

    protected function casts(): array
    {
        return [
            'email_verified_at' => 'datetime',
            'password' => 'hashed',
        ];
    }

    // Relationships
    public function artisanProfile()
    {
        return $this->hasOne(ArtisanProfile::class);
    }

    public function products()
    {
        return $this->hasMany(Product::class, 'artisan_id');
    }

    public function cart()
    {
        return $this->hasMany(Cart::class);
    }

    public function orders()
    {
        return $this->hasMany(Order::class, 'customer_id');
    }

    public function artisanOrders()
    {
        return $this->hasMany(Order::class, 'artisan_id');
    }

    public function reviews()
    {
        return $this->hasMany(Review::class, 'customer_id');
    }

    public function sentMessages()
    {
        return $this->hasMany(Message::class, 'sender_id');
    }

    public function notifications()
    {
        return $this->hasMany(UserNotification::class, 'user_id');
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
    public function scopeArtisans($query)
    {
        return $query->where('role', 'artisan');
    }

    public function scopeCustomers($query)
    {
        return $query->where('role', 'customer');
    }

    public function scopeActive($query)
    {
        return $query->where('status', 'active');
    }

    // Accessors & Helpers
    public function isAdmin(): bool
    {
        return $this->role === 'admin';
    }

    public function isArtisan(): bool
    {
        return $this->role === 'artisan';
    }

    public function isCustomer(): bool
    {
        // Treat explicit 'customer' role as customer.
        // Also treat users with no role set as customers by default,
        // so freshly-registered accounts behave as shoppers unless
        // promoted to admin/artisan.
        return $this->role === 'customer' || $this->role === null;
    }

    public function isSuspended(): bool
    {
        return $this->status === 'suspended';
    }

    /**
     * Human-readable shipping block for Guihulngan City deliveries.
     */
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

        if (!empty($addressParts)) {
            return implode(', ', $addressParts);
        }

        if ($this->shipping_barangay) {
            $fallbackParts = [
                'Philippines',
                config('guihulngan.province'),
                config('guihulngan.city_name'),
                'Barangay ' . $this->shipping_barangay,
            ];

            if ($this->shipping_address) {
                $fallbackParts[] = $this->shipping_address;
            }

            return implode(', ', array_filter($fallbackParts));
        }

        return (string) ($this->shipping_address ?? '');
    }
}