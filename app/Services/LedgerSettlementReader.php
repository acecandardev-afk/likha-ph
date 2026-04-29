<?php

namespace App\Services;

use App\Models\LedgerJournal;
use App\Models\LedgerLine;
use App\Models\Order;

class LedgerSettlementReader
{
    /**
     * Amounts from the delivery settlement journal (authoritative once posted).
     *
     * @return array{
     *   journal_id: int,
     *   posted_at: \Carbon\Carbon|null,
     *   cod_collectible: float,
     *   artisan_payable: float,
     *   platform_service_fee: float,
     *   shipping_trust: float,
     *   tax_payable: float,
     *   rider_payable: float,
     * }|null
     */
    public function snapshotForOrder(Order $order): ?array
    {
        $order->loadMissing(['ledgerJournals.lines']);

        $journal = $order->ledgerJournals->firstWhere('kind', LedgerJournal::KIND_DELIVERY_SETTLEMENT);

        return $journal ? $this->snapshotFromJournal($journal) : null;
    }

    /**
     * @return array{
     *   journal_id: int,
     *   posted_at: \Carbon\Carbon|null,
     *   cod_collectible: float,
     *   artisan_payable: float,
     *   platform_service_fee: float,
     *   shipping_trust: float,
     *   tax_payable: float,
     *   rider_payable: float,
     * }
     */
    public function snapshotFromJournal(LedgerJournal $journal): array
    {
        $journal->loadMissing('lines');

        $sumBucket = fn (string $bucket): float => round((float) $journal->lines->where('bucket', $bucket)->sum('amount'), 2);

        return [
            'journal_id' => (int) $journal->id,
            'posted_at' => $journal->posted_at,
            'cod_collectible' => $sumBucket(LedgerLine::BUCKET_COD_COLLECTIBLE),
            'artisan_payable' => $sumBucket(LedgerLine::BUCKET_ARTISAN_PAYABLE),
            'platform_service_fee' => $sumBucket(LedgerLine::BUCKET_PLATFORM_SERVICE_FEE),
            'shipping_trust' => $sumBucket(LedgerLine::BUCKET_SHIPPING_TRUST),
            'tax_payable' => $sumBucket(LedgerLine::BUCKET_TAX_PAYABLE),
            'rider_payable' => $sumBucket(LedgerLine::BUCKET_RIDER_PAYABLE),
        ];
    }
}
