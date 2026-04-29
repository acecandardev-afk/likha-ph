<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class SellerCodHandoff extends Model
{
    protected $fillable = [
        'order_id',
        'artisan_user_id',
        'ledger_journal_id',
        'expected_artisan_payable',
        'acknowledged_at',
        'note',
    ];

    protected function casts(): array
    {
        return [
            'expected_artisan_payable' => 'decimal:2',
            'acknowledged_at' => 'datetime',
        ];
    }

    public function order(): BelongsTo
    {
        return $this->belongsTo(Order::class);
    }

    public function artisan(): BelongsTo
    {
        return $this->belongsTo(User::class, 'artisan_user_id');
    }

    public function ledgerJournal(): BelongsTo
    {
        return $this->belongsTo(LedgerJournal::class);
    }
}
