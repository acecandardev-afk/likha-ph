<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class RiderRemittanceReport extends Model
{
    protected $fillable = [
        'rider_id',
        'report_date',
        'cod_declared_total',
        'seller_pool_declared',
        'platform_pool_declared',
        'notes',
        'submitted_at',
    ];

    protected function casts(): array
    {
        return [
            'report_date' => 'date',
            'cod_declared_total' => 'decimal:2',
            'seller_pool_declared' => 'decimal:2',
            'platform_pool_declared' => 'decimal:2',
            'submitted_at' => 'datetime',
        ];
    }

    public function rider(): BelongsTo
    {
        return $this->belongsTo(Rider::class);
    }
}
