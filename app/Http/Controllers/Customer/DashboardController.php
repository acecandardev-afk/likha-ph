<?php

namespace App\Http\Controllers\Customer;

use App\Models\OrderItemReturn;
use Illuminate\Support\Facades\Cache;

class DashboardController extends CustomerController
{
    /**
     * Display customer dashboard.
     */
    public function index()
    {
        $customer = $this->getCustomer();

        $stats = Cache::remember("dashboard:customer:{$customer->id}:stats:v5", 60, function () use ($customer) {
            $scoped = $customer->orders()->notStaleCancelled();

            return [
                'total_orders' => $scoped->clone()->count(),
                'pending_orders' => $scoped->clone()->pending()->count(),
                'shipped_orders' => $scoped->clone()->shipped()->count(),
                'on_delivery_orders' => $scoped->clone()->onDelivery()->count(),
                'delivered_orders' => $scoped->clone()->delivered()->count(),
                'confirmed_orders' => $scoped->clone()->wherePaymentVerified()->count(),
                'completed_orders' => $scoped->clone()->completed()->count(),
                'total_spent' => $scoped->clone()->whereIn('status', ['delivered', 'completed'])->sum('total'),
                'returns_pending_review' => OrderItemReturn::query()
                    ->where('customer_id', $customer->id)
                    ->where('status', OrderItemReturn::STATUS_PENDING_ADMIN)
                    ->count(),
            ];
        });

        // Recent orders
        $recentOrders = $customer->orders()
            ->notStaleCancelled()
            ->with(['artisan.artisanProfile', 'items.product.images', 'payment'])
            ->latest()
            ->take(5)
            ->get();

        return view('customer.dashboard', compact('stats', 'recentOrders'));
    }
}
