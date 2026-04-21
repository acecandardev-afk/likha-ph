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

        if ($this->street_address) {
            $addressParts[] = $this->street_address;
        }

        if ($this->barangay) {
            $addressParts[] = 'Barangay ' . $this->barangay;
        }

        if ($this->city) {
            $addressParts[] = $this->city;
        }

        if ($this->province) {
            $addressParts[] = $this->province;
        }

        if ($this->region) {
            $addressParts[] = $this->region;
        }

        if ($this->country) {
            $addressParts[] = $this->country;
        }

        if (!empty($addressParts)) {
            return implode(', ', array_reverse($addressParts));
        }

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