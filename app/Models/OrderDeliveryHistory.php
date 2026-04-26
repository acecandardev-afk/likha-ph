<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class OrderDeliveryHistory extends Model
{
    use HasFactory;

    protected $fillable = [
        'order_id',
        'status',
        'updated_by',
        'updated_by_role',
        'note',
        'status_at',
    ];

    protected $casts = [
        'status_at' => 'datetime',
    ];

    public function order()
    {
        return $this->belongsTo(Order::class);
    }

    public function actor()
    {
        return $this->belongsTo(User::class, 'updated_by');
    }
}
