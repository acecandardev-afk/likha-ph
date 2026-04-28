<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class LedgerJournal extends Model
{
    public const KIND_DELIVERY_SETTLEMENT = 'delivery_settlement';

    protected $fillable = [
        'order_id',
        'kind',
        'posted_at',
    ];

    protected $casts = [
        'posted_at' => 'datetime',
    ];

    public function order(): BelongsTo
    {
        return $this->belongsTo(Order::class);
    }

    public function lines(): HasMany
    {
        return $this->hasMany(LedgerLine::class, 'ledger_journal_id')->orderBy('sequence');
    }

    /** @return array{ debit: float, credit: float } */
    public function totalsBySide(): array
    {
        if ($this->relationLoaded('lines')) {
            $debit = round((float) $this->lines->where('side', 'debit')->sum('amount'), 2);
            $credit = round((float) $this->lines->where('side', 'credit')->sum('amount'), 2);

            return ['debit' => $debit, 'credit' => $credit];
        }

        $debit = round((float) $this->lines()->where('side', 'debit')->sum('amount'), 2);
        $credit = round((float) $this->lines()->where('side', 'credit')->sum('amount'), 2);

        return ['debit' => $debit, 'credit' => $credit];
    }
}
