<?php

namespace App\Services;

use App\Models\LedgerJournal;
use App\Models\LedgerLine;
use App\Models\Order;
use App\Models\OrderPackage;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class LedgerPostingService
{
    /**
     * Post immutable delivery settlement when handoff is complete — idempotent per order.
     * Double-entry balances: cash in (COD) + courier expense = credits to maker, fees, shipping, tax, rider dues.
     */
    public function postDeliverySettlementIfNeeded(Order $order): void
    {
        $order->loadMissing(['packages', 'payment']);

        if ($order->status !== 'delivered' || $order->isCancelled()) {
            return;
        }

        if (! $order->payment?->isVerified()) {
            return;
        }

        $exists = LedgerJournal::query()
            ->where('order_id', $order->id)
            ->where('kind', LedgerJournal::KIND_DELIVERY_SETTLEMENT)
            ->exists();

        if ($exists) {
            return;
        }

        $total = round((float) $order->total, 2);
        $artisan = round((float) $order->artisanMerchandiseShare(), 2);
        $platformFee = round((float) $order->platform_fee, 2);
        $shipping = round((float) ($order->shipping_amount ?? 0), 2);
        $tax = round((float) ($order->tax_amount ?? 0), 2);
        $riderFees = $order->packages->reduce(
            function (float $carry, OrderPackage $package): float {
                return $carry + (float) ($package->rider_fee_amount ?? 0);
            },
            0.0,
        );
        $riderTotal = round($riderFees, 2);

        $creditCore = round($artisan + $platformFee + $shipping + $tax, 2);
        if (abs($creditCore - $total) > 0.02) {
            Log::warning('ledger_amount_mismatch', [
                'order_id' => $order->id,
                'order_number' => $order->order_number,
                'credit_core' => $creditCore,
                'total' => $total,
            ]);
        }

        DB::transaction(function () use ($order, $total, $artisan, $platformFee, $shipping, $tax, $riderTotal) {
            $journal = LedgerJournal::create([
                'order_id' => $order->id,
                'kind' => LedgerJournal::KIND_DELIVERY_SETTLEMENT,
                'posted_at' => now(),
            ]);

            $seq = 1;
            $lines = [
                [
                    'side' => 'debit',
                    'bucket' => LedgerLine::BUCKET_COD_COLLECTIBLE,
                    'amount' => $total,
                    'memo' => 'Amount the rider collects from the buyer on arrival',
                ],
                [
                    'side' => 'credit',
                    'bucket' => LedgerLine::BUCKET_ARTISAN_PAYABLE,
                    'amount' => $artisan,
                    'memo' => 'Goods share after promos and marketplace fee',
                ],
                [
                    'side' => 'credit',
                    'bucket' => LedgerLine::BUCKET_PLATFORM_SERVICE_FEE,
                    'amount' => $platformFee,
                    'memo' => 'Marketplace fee on this order',
                ],
                [
                    'side' => 'credit',
                    'bucket' => LedgerLine::BUCKET_SHIPPING_TRUST,
                    'amount' => $shipping,
                    'memo' => 'Delivery charge from the buyer receipt',
                ],
                [
                    'side' => 'credit',
                    'bucket' => LedgerLine::BUCKET_TAX_PAYABLE,
                    'amount' => $tax,
                    'memo' => 'Tax collected on the receipt',
                ],
            ];

            if ($riderTotal > 0) {
                $lines[] = [
                    'side' => 'debit',
                    'bucket' => LedgerLine::BUCKET_COURIER_EXPENSE,
                    'amount' => $riderTotal,
                    'memo' => 'Delivery partner payout recorded on packages',
                ];
                $lines[] = [
                    'side' => 'credit',
                    'bucket' => LedgerLine::BUCKET_RIDER_PAYABLE,
                    'amount' => $riderTotal,
                    'memo' => 'Accrued payout to riders for this order',
                ];
            }

            foreach ($lines as $row) {
                LedgerLine::create([
                    'ledger_journal_id' => $journal->id,
                    'sequence' => $seq++,
                    'side' => $row['side'],
                    'bucket' => $row['bucket'],
                    'amount' => $row['amount'],
                    'memo' => $row['memo'] ?? null,
                ]);
            }

            $t = $journal->fresh()->totalsBySide();
            if (abs($t['debit'] - $t['credit']) > 0.02) {
                throw new \RuntimeException('Ledger out of balance for order '.$order->order_number);
            }
        });
    }
}
