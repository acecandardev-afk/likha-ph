<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Sale extends Model
{
    use HasFactory;

    protected $fillable = [
        'receipt_number',
        'user_id',
        'total_amount',
        'amount_paid',
        'change_amount',
        'payment_method',
        'payment_reference',
    ];

    protected $casts = [
        'total_amount' => 'decimal:2',
        'amount_paid' => 'decimal:2',
        'change_amount' => 'decimal:2',
    ];

    protected static function boot()
    {
        parent::boot();

        static::creating(function ($sale) {
            if (empty($sale->receipt_number)) {
                $todayCount = Sale::whereDate('created_at', today())->count();
                $sale->receipt_number = 'RCP-' . date('Ymd') . '-' . str_pad($todayCount + 1, 4, '0', STR_PAD_LEFT);
            }
        });
    }

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function items()
    {
        return $this->hasMany(SaleItem::class);
    }

    public function isCash()
    {
        return $this->payment_method === 'cash';
    }

    public function isGCash()
    {
        return $this->payment_method === 'gcash';
    }

    public function isCOD()
    {
        return $this->payment_method === 'cod';
    }

    public function isPaid()
    {
        return $this->payment_method !== 'cod';
    }
}