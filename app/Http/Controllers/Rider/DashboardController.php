<?php

namespace App\Http\Controllers\Rider;

use App\Services\DeliveryService;

class DashboardController extends RiderController
{
    public function index()
    {
        $user = $this->getRiderUser();
        $rider = $user->riderProfile;

        $stats = [
            'assigned' => $rider?->orders()->whereIn('delivery_status', [
                DeliveryService::STATUS_ORDER_CONFIRMED,
                DeliveryService::STATUS_PREPARING_PACKAGE,
                DeliveryService::STATUS_PACKAGE_PICKED_UP,
                DeliveryService::STATUS_ARRIVED_SORT_CENTER,
                DeliveryService::STATUS_OUT_FOR_DELIVERY,
            ])->count() ?? 0,
            'delivered' => $rider?->orders()->where('delivery_status', DeliveryService::STATUS_DELIVERED)->count() ?? 0,
            'pending_assignment' => \App\Models\Order::where('delivery_status', DeliveryService::STATUS_PENDING_ASSIGNMENT)->count(),
        ];

        return view('rider.dashboard', compact('rider', 'stats'));
    }
}
