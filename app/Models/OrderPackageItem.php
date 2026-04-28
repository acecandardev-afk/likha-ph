<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class OrderPackageItem extends Model
{
    use HasFactory;

    protected $fillable = [
        'order_package_id',
        'order_item_id',
        'quantity',
    ];

    public function orderPackage()
    {
        return $this->belongsTo(OrderPackage::class);
    }

    public function orderItem()
    {
        return $this->belongsTo(OrderItem::class);
    }
}
