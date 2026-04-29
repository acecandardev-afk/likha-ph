<?php

namespace App\Http\Controllers\Rider;

use App\Models\OrderPackage;
use App\Services\DeliveryService;
use App\Services\RiderSettlementService;
use Carbon\Carbon;
use Illuminate\Http\Request;

class SettlementController extends RiderController
{
    public function index(Request $request, RiderSettlementService $settlementService)
    {
        $user = $this->getRiderUser();
        $rider = $user->riderProfile;
        abort_unless($rider, 403);

        $validated = $request->validate([
            'date_from' => ['nullable', 'date'],
            'date_to' => ['nullable', 'date'],
        ]);

        $fromInput = $validated['date_from'] ?? null;
        $toInput = $validated['date_to'] ?? null;

        if ($fromInput === null && $toInput === null) {
            $from = now()->startOfMonth()->startOfDay();
            $to = now()->endOfDay();
        } elseif ($fromInput !== null && $toInput !== null) {
            $from = Carbon::parse($fromInput)->startOfDay();
            $to = Carbon::parse($toInput)->endOfDay();
            if ($from->greaterThan($to)) {
                return back()->withErrors(['date_from' => 'Start date must be on or before end date.']);
            }
            $days = $from->diffInDays($to);
            if ($days > 366) {
                return back()->withErrors(['date_to' => 'Choose a range of one year or less.']);
            }
        } else {
            return back()->withErrors(['date_from' => 'Please choose both start and end dates, or leave both blank for this month.']);
        }

        $packages = OrderPackage::query()
            ->where('rider_id', $rider->id)
            ->where('delivery_status', DeliveryService::STATUS_DELIVERED)
            ->whereNotNull('delivery_completed_at')
            ->whereBetween('delivery_completed_at', [$from, $to])
            ->with([
                'order.artisan.artisanProfile',
                'items.orderItem.product',
                'order.packages.items.orderItem',
            ])
            ->orderByDesc('delivery_completed_at')
            ->paginate(25)
            ->withQueryString();

        $periodTotals = $settlementService->totalsForRider((int) $rider->id, $from, $to);

        $rows = [];
        foreach ($packages as $pkg) {
            $allocation = $settlementService->allocatePackage($pkg);
            $order = $pkg->order;
            $lineItems = [];
            foreach ($pkg->items as $opi) {
                $oi = $opi->orderItem;
                if (! $oi) {
                    continue;
                }
                $lineItems[] = [
                    'order_item_id' => $oi->id,
                    'product_id' => $oi->product_id,
                    'name' => $oi->product_name ?? $oi->product?->name ?? 'Item',
                    'quantity' => (int) $opi->quantity,
                    'line_total' => round((float) $oi->price * (int) $opi->quantity, 2),
                ];
            }

            $rows[] = [
                'package' => $pkg,
                'allocation' => $allocation,
                'workshop' => $settlementService->workshopLabel($order),
                'order_number' => $order->order_number ?? '#'.$order->id,
                'line_items' => $lineItems,
            ];
        }

        $lifetimeTotals = $settlementService->totalsForRider((int) $rider->id);

        return view('rider.cod-settlement', [
            'rider' => $rider,
            'packages' => $packages,
            'rows' => $rows,
            'periodTotals' => $periodTotals,
            'lifetimeTotals' => $lifetimeTotals,
            'dateFrom' => $from,
            'dateTo' => $to,
        ]);
    }
}
