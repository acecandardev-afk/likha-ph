<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class LedgerLine extends Model
{
    /** Money the rider collects from the buyer at the door */
    public const BUCKET_COD_COLLECTIBLE = 'cod_collectible';

    /** Marketplace cost attributed to courier partners */
    public const BUCKET_COURIER_EXPENSE = 'courier_expense';

    /** Amount owed / credited to maker for items sold after fees & promos */
    public const BUCKET_ARTISAN_PAYABLE = 'artisan_payable';

    /** Marketplace service fee retained */
    public const BUCKET_PLATFORM_SERVICE_FEE = 'platform_service_fee';

    /** Delivery charge portion from the buyer receipt */
    public const BUCKET_SHIPPING_TRUST = 'shipping_trust';

    /** Taxes shown on receipt (until remittance rules attach) */
    public const BUCKET_TAX_PAYABLE = 'tax_payable';

    /** Payable to delivery partners recorded on packages */
    public const BUCKET_RIDER_PAYABLE = 'rider_payable';

    /** Reconcile stored order.total with merchandise + fees + freight + tax (legacy or rounding drift) */
    public const BUCKET_BOOKING_ADJUSTMENT = 'booking_adjustment';

    public static function labelForBucket(string $bucket): string
    {
        return match ($bucket) {
            self::BUCKET_COD_COLLECTIBLE => 'Cash from buyer (COD total)',
            self::BUCKET_COURIER_EXPENSE => 'Rider delivery cost',
            self::BUCKET_ARTISAN_PAYABLE => 'Seller share from items',
            self::BUCKET_PLATFORM_SERVICE_FEE => 'Likha service fee',
            self::BUCKET_SHIPPING_TRUST => 'Shipping fee (from buyer)',
            self::BUCKET_TAX_PAYABLE => 'Tax (from buyer)',
            self::BUCKET_RIDER_PAYABLE => 'Rider delivery pay',
            self::BUCKET_BOOKING_ADJUSTMENT => 'Small rounding adjustment',
            default => ucfirst(str_replace('_', ' ', $bucket)),
        };
    }

    protected $fillable = [
        'ledger_journal_id',
        'sequence',
        'side',
        'bucket',
        'amount',
        'memo',
    ];

    protected $casts = [
        'amount' => 'decimal:2',
    ];

    public function ledgerJournal(): BelongsTo
    {
        return $this->belongsTo(LedgerJournal::class, 'ledger_journal_id');
    }
}
