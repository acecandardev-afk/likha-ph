<?php

namespace App\Services;

use App\Models\Order;
use App\Models\OrderPackage;
use Carbon\Carbon;
use Illuminate\Database\Eloquent\Builder;

class RiderSettlementService
{
    public const POLICY_PROPORTIONAL = 'proportional_merchandise';

    public const POLICY_SINGLE_FINAL = 'single_collection_final_delivery';

    public function __construct(
        protected LedgerSettlementReader $ledgerReader
    ) {}

    /**
     * Allocate order-level COD and splits to one package (informational UI). Ledger snapshot is authoritative once posted.
     *
     * @return array<string, mixed>
     */
    public function allocatePackage(OrderPackage $package): array
    {
        $package->loadMissing(['order.packages.items.orderItem', 'items.orderItem.product']);
        $order = $package->order;

        $policy = (string) config('cod.allocation_policy', self::POLICY_PROPORTIONAL);

        $sumMerch = $this->orderMerchandiseSum($order);
        $pkgMerch = $package->merchandiseTotal();

        $ratioMerch = $sumMerch > 0
            ? min(1.0, max(0.0, $pkgMerch / $sumMerch))
            : ($order->packages->count() > 0 ? 1.0 / $order->packages->count() : 1.0);

        $ratio = match ($policy) {
            self::POLICY_SINGLE_FINAL => $this->finalDeliveryRatioForPackage($package),
            default => $ratioMerch,
        };

        $total = round((float) $order->total, 2);

        $sellerShare = round((float) $order->artisanMerchandiseShare() * $ratio, 2);
        $platform = round((float) $order->platform_fee * $ratio, 2);
        $shipping = round((float) ($order->shipping_amount ?? 0) * $ratio, 2);
        $tax = round((float) ($order->tax_amount ?? 0) * $ratio, 2);
        $cod = round($total * $ratio, 2);

        $ledgerSnapshot = $this->ledgerReader->snapshotForOrder($order);

        return [
            'cod_total' => $cod,
            'seller_share' => $sellerShare,
            'platform_fee' => $platform,
            'shipping' => $shipping,
            'tax' => $tax,
            'company_side_total' => round($platform + $shipping + $tax, 2),
            'ratio' => $ratio,
            'ratio_merchandise_basis' => round($ratioMerch, 6),
            'merchandise_in_package' => round($pkgMerch, 2),
            'merchandise_in_order' => round($sumMerch, 2),
            'allocation_policy' => $policy,
            'policy_label' => $this->policyLabel($policy),
            'physical_cod_hint' => $this->physicalCodHint($policy, $package, $order),
            'ledger_snapshot' => $ledgerSnapshot,
            'ledger_journal_id' => $ledgerSnapshot['journal_id'] ?? null,
            'ledger_posted_at' => $ledgerSnapshot['posted_at'] ?? null,
            'official_artisan_payable' => $ledgerSnapshot['artisan_payable'] ?? null,
            'official_cod_collectible' => $ledgerSnapshot['cod_collectible'] ?? null,
            'is_final_stop_for_cod' => $policy !== self::POLICY_SINGLE_FINAL || $ratio >= 0.999,
        ];
    }

    protected function policyLabel(string $policy): string
    {
        return match ($policy) {
            self::POLICY_SINGLE_FINAL => 'Full order COD attributed to last delivered package',
            default => 'Split by merchandise in each package',
        };
    }

    protected function physicalCodHint(string $policy, OrderPackage $package, Order $order): string
    {
        $order->loadMissing('packages');

        if ($policy === self::POLICY_SINGLE_FINAL) {
            return 'Cash-on-delivery for the full receipt is treated as collected when the last package for this order is delivered. Earlier stops show ₱0 until completion.';
        }

        if ($order->packages->count() > 1) {
            return 'Buyer payment is split across packages by merchandise value for planning remittances. The settlement ledger for sellers uses the full order once everything is delivered.';
        }

        return 'Single-package order: attributed amounts align with the receipt total.';
    }

    protected function finalDeliveryRatioForPackage(OrderPackage $package): float
    {
        $order = $package->order;
        $order->loadMissing('packages');

        $delivered = $order->packages->filter(function (OrderPackage $p) {
            return $p->delivery_status === DeliveryService::STATUS_DELIVERED
                && $p->delivery_completed_at !== null;
        });

        if ($delivered->isEmpty()) {
            return 0.0;
        }

        $final = $delivered->sort(function (OrderPackage $a, OrderPackage $b) {
            $ta = $a->delivery_completed_at?->timestamp ?? 0;
            $tb = $b->delivery_completed_at?->timestamp ?? 0;
            if ($ta !== $tb) {
                return $tb <=> $ta;
            }

            return $b->id <=> $a->id;
        })->first();

        return (int) $final->id === (int) $package->id ? 1.0 : 0.0;
    }

    /**
     * @return array{
     *   packages_count: int,
     *   cod_total: float,
     *   seller_share: float,
     *   platform_fee: float,
     *   shipping: float,
     *   tax: float,
     *   company_side_total: float
     * }
     */
    public function totalsForRider(int $riderId, ?Carbon $from = null, ?Carbon $to = null): array
    {
        $packages = $this->deliveredPackagesBaseQuery($riderId)
            ->when($from && $to, fn (Builder $q) => $q->whereBetween('delivery_completed_at', [$from, $to]))
            ->with(['order.packages.items.orderItem', 'items.orderItem.product'])
            ->get();

        return $this->sumAllocationsForPackages($packages);
    }

    /**
     * @return array<int|string, array{label: string, seller_share: float}>
     */
    public function sellerTotalsForRider(int $riderId, ?Carbon $from = null, ?Carbon $to = null): array
    {
        $packages = $this->deliveredPackagesBaseQuery($riderId)
            ->when($from && $to, fn (Builder $q) => $q->whereBetween('delivery_completed_at', [$from, $to]))
            ->with(['order.artisan.artisanProfile', 'order.packages.items.orderItem', 'items.orderItem.product'])
            ->get();

        $bySeller = [];

        foreach ($packages as $pkg) {
            $order = $pkg->order;
            $alloc = $this->allocatePackage($pkg);
            $key = (string) ($order->artisan_id ?? 'unknown');

            if (! isset($bySeller[$key])) {
                $bySeller[$key] = [
                    'label' => $this->workshopLabel($order),
                    'seller_share' => 0.0,
                ];
            }

            $bySeller[$key]['seller_share'] = round($bySeller[$key]['seller_share'] + $alloc['seller_share'], 2);
        }

        uasort($bySeller, fn ($a, $b) => strcmp($a['label'], $b['label']));

        return $bySeller;
    }

    public function workshopLabel(Order $order): string
    {
        $order->loadMissing('artisan.artisanProfile');

        $workshop = $order->artisan?->artisanProfile?->workshop_name;

        if ($workshop) {
            return $workshop;
        }

        return $order->artisan?->name ?? 'Seller';
    }

    protected function deliveredPackagesBaseQuery(int $riderId): Builder
    {
        return OrderPackage::query()
            ->where('rider_id', $riderId)
            ->where('delivery_status', DeliveryService::STATUS_DELIVERED)
            ->whereNotNull('delivery_completed_at');
    }

    protected function orderMerchandiseSum(Order $order): float
    {
        $order->loadMissing('packages.items.orderItem');

        return (float) $order->packages->sum(fn (OrderPackage $p) => $p->merchandiseTotal());
    }

    /**
     * @param  iterable<OrderPackage>  $packages
     * @return array{
     *   packages_count: int,
     *   cod_total: float,
     *   seller_share: float,
     *   platform_fee: float,
     *   shipping: float,
     *   tax: float,
     *   company_side_total: float
     * }
     */
    public function sumAllocationsForPackages(iterable $packages): array
    {
        $totals = [
            'packages_count' => 0,
            'cod_total' => 0.0,
            'seller_share' => 0.0,
            'platform_fee' => 0.0,
            'shipping' => 0.0,
            'tax' => 0.0,
            'company_side_total' => 0.0,
        ];

        foreach ($packages as $pkg) {
            $a = $this->allocatePackage($pkg);
            $totals['packages_count']++;
            $totals['cod_total'] = round($totals['cod_total'] + $a['cod_total'], 2);
            $totals['seller_share'] = round($totals['seller_share'] + $a['seller_share'], 2);
            $totals['platform_fee'] = round($totals['platform_fee'] + $a['platform_fee'], 2);
            $totals['shipping'] = round($totals['shipping'] + $a['shipping'], 2);
            $totals['tax'] = round($totals['tax'] + $a['tax'], 2);
            $totals['company_side_total'] = round($totals['company_side_total'] + $a['company_side_total'], 2);
        }

        return $totals;
    }
}
