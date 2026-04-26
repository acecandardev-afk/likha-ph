<?php

namespace App\Http\Controllers\Customer;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;

class DashboardController extends CustomerController
{
    /**
     * Display customer dashboard.
     */
    public function index()
    {
        $customer = $this->getCustomer();

        $stats = Cache::remember("dashboard:customer:{$customer->id}:stats", 60, function () use ($customer) {
            return [
                'total_orders' => $customer->orders()->count(),
                'pending_orders' => $customer->orders()->pending()->count(),
                'shipped_orders' => $customer->orders()->shipped()->count(),
                'on_delivery_orders' => $customer->orders()->onDelivery()->count(),
                'delivered_orders' => $customer->orders()->delivered()->count(),
                'confirmed_orders' => $customer->orders()->confirmed()->count(),
                'completed_orders' => $customer->orders()->completed()->count(),
                'total_spent' => $customer->orders()->whereIn('status', ['delivered', 'completed'])->sum('total'),
            ];
        });

        // Recent orders
        $recentOrders = $customer->orders()
            ->with(['artisan.artisanProfile', 'items.product.images', 'payment'])
            ->latest()
            ->take(5)
            ->get();

        return view('customer.dashboard', compact('stats', 'recentOrders'));
    }
}