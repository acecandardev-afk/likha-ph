<?php

namespace App\Services;

use App\Models\Order;
use App\Models\OrderPackage;
use App\Models\Payment;
use App\Models\Product;
use App\Models\Rider;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;

class MonthlyReportService
{
    /** @return array{ start: Carbon, end: Carbon, label: string } */
    public function window(int $year, int $month): array
    {
        $start = Carbon::create($year, $month, 1)->startOfDay();
        $end = (clone $start)->endOfMonth();

        return [
            'start' => $start,
            'end' => $end,
            'label' => $start->translatedFormat('F Y'),
        ];
    }

    /**
     * @return array<string, mixed>
     */
    public function buildAdminReport(int $year, int $month): array
    {
        $w = $this->window($year, $month);

        $ordersQuery = Order::query()->whereBetween('created_at', [$w['start'], $w['end']]);

        $byStatus = (clone $ordersQuery)
            ->select('status', DB::raw('count(*) as c'))
            ->groupBy('status')
            ->pluck('c', 'status')
            ->all();

        $orderRows = (clone $ordersQuery)
            ->with(['customer', 'artisan.artisanProfile', 'payment'])
            ->orderByDesc('created_at')
            ->limit(100)
            ->get();

        $deliveredPackages = OrderPackage::query()
            ->whereNotNull('delivery_completed_at')
            ->whereBetween('delivery_completed_at', [$w['start'], $w['end']])
            ->count();

        $verifiedPaymentsTotal = Payment::query()
            ->where('verification_status', 'verified')
            ->whereBetween('created_at', [$w['start'], $w['end']])
            ->sum('amount');

        $newCustomers = User::query()
            ->customers()
            ->whereBetween('created_at', [$w['start'], $w['end']])
            ->count();

        $newArtisans = User::query()
            ->artisans()
            ->whereBetween('created_at', [$w['start'], $w['end']])
            ->count();

        $productsListed = Product::query()
            ->whereBetween('created_at', [$w['start'], $w['end']])
            ->count();

        $gmvMonth = (clone $ordersQuery)->whereIn('status', ['delivered', 'completed'])->sum('total');

        return [
            'window' => $w,
            'orders_count' => (clone $ordersQuery)->count(),
            'by_status' => $byStatus,
            'gmv_delivered_completed' => (float) $gmvMonth,
            'verified_payments_sum' => (float) $verifiedPaymentsTotal,
            'delivered_packages' => $deliveredPackages,
            'new_customers' => $newCustomers,
            'new_artisans' => $newArtisans,
            'products_created' => $productsListed,
            'orders' => $orderRows,
            'generated_at' => now(),
        ];
    }

    /**
     * @return array<string, mixed>
     */
    public function buildArtisanReport(User $artisan, int $year, int $month): array
    {
        $w = $this->window($year, $month);

        $orders = Order::query()
            ->where('artisan_id', $artisan->id)
            ->whereBetween('created_at', [$w['start'], $w['end']])
            ->with(['customer', 'items.product', 'payment', 'packages'])
            ->orderByDesc('created_at')
            ->get();

        $byStatus = $orders->groupBy('status')->map(fn (Collection $g) => $g->count())->all();

        $shareSum = $orders->whereIn('status', ['delivered', 'completed'])->sum(fn (Order $o) => $o->artisanMerchandiseShare());

        return [
            'window' => $w,
            'artisan' => $artisan,
            'orders_count' => $orders->count(),
            'by_status' => $byStatus,
            'estimated_merchandise_share' => (float) $shareSum,
            'orders' => $orders,
            'generated_at' => now(),
        ];
    }

    /**
     * @return array<string, mixed>
     */
    public function buildRiderReport(Rider $rider, int $year, int $month): array
    {
        $w = $this->window($year, $month);

        $packages = OrderPackage::query()
            ->where('rider_id', $rider->id)
            ->whereNotNull('delivery_completed_at')
            ->whereBetween('delivery_completed_at', [$w['start'], $w['end']])
            ->with(['order.customer', 'order.artisan'])
            ->orderByDesc('delivery_completed_at')
            ->get();

        $feeTotal = (float) $packages->sum(fn (OrderPackage $p) => (float) ($p->rider_fee_amount ?? 0));

        return [
            'window' => $w,
            'rider' => $rider,
            'packages_delivered' => $packages->count(),
            'rider_fees_total' => $feeTotal,
            'packages' => $packages,
            'generated_at' => now(),
        ];
    }
}
