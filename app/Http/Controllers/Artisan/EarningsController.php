<?php

namespace App\Http\Controllers\Artisan;

use App\Models\Order;
use App\Models\OrderPackage;

class EarningsController extends ArtisanController
{
    public function index()
    {
        $artisan = $this->getArtisan();

        $pagination = Order::query()
            ->where('artisan_id', $artisan->id)
            ->whereIn('status', ['delivered', 'completed'])
            ->with(['customer'])
            ->latest()
            ->paginate(20);

        $orderIds = Order::query()
            ->where('artisan_id', $artisan->id)
            ->whereIn('status', ['delivered', 'completed'])
            ->pluck('id');

        $estimatedShare = (float) Order::query()
            ->where('artisan_id', $artisan->id)
            ->whereIn('status', ['delivered', 'completed'])
            ->get()
            ->sum(fn (Order $o) => $o->artisanMerchandiseShare());

        $totals = [
            'orders_count' => $orderIds->count(),
            'estimated_share' => $estimatedShare,
            'courier_fees_on_record' => (float) OrderPackage::query()
                ->whereIn('order_id', $orderIds)
                ->sum('rider_fee_amount'),
        ];

        return view('artisan.earnings.index', [
            'orders' => $pagination,
            'totals' => $totals,
        ]);
    }
}
