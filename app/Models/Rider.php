<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Rider extends Model
{
    use HasFactory;

    public const STATUS_AVAILABLE = 'available';

    public const STATUS_BUSY = 'busy';

    public const STATUS_OFFLINE = 'offline';

    protected $fillable = [
        'rider_id',
        'user_id',
        'full_name',
        'contact_number',
        'email',
        'address',
        'vehicle_type',
        'status',
        'date_created',
        'birth_date',
        'emergency_contact_name',
        'emergency_contact_phone',
        'license_number',
        'license_expiry',
        'vehicle_plate',
        'bio',
        'license_image',
        'id_document_image',
        'clearance_document_image',
    ];

    protected $casts = [
        'date_created' => 'datetime',
        'birth_date' => 'date',
    ];

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function orders()
    {
        return $this->hasMany(Order::class);
    }

    public function packages()
    {
        return $this->hasMany(OrderPackage::class);
    }

    public function scopeAvailable($query)
    {
        return $query->where('status', self::STATUS_AVAILABLE);
    }
}
