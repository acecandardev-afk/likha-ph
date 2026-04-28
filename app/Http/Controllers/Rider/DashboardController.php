<?php

namespace App\Http\Controllers\Rider;

use App\Models\OrderPackage;
use App\Services\DeliveryService;

class DashboardController extends RiderController
{
    public function index()
    {
        $user = $this->getRiderUser();
        $rider = $user->riderProfile;

        $activeStatuses = [
            DeliveryService::STATUS_ORDER_CONFIRMED,
            DeliveryService::STATUS_PREPARING_PACKAGE,
            DeliveryService::STATUS_PACKAGE_PICKED_UP,
            DeliveryService::STATUS_ARRIVED_SORT_CENTER,
            DeliveryService::STATUS_OUT_FOR_DELIVERY,
        ];

        $stats = [
            'assigned' => $rider
                ? OrderPackage::query()
                    ->where('rider_id', $rider->id)
                    ->whereIn('delivery_status', $activeStatuses)
                    ->count()
                : 0,
            'delivered' => $rider
                ? OrderPackage::query()
                    ->where('rider_id', $rider->id)
                    ->where('delivery_status', DeliveryService::STATUS_DELIVERED)
                    ->count()
                : 0,
            'pending_assignment' => OrderPackage::where('delivery_status', DeliveryService::STATUS_PENDING_ASSIGNMENT)->count(),
        ];

        return view('rider.dashboard', compact('rider', 'stats'));
    }
}
