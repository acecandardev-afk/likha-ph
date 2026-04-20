<?php

namespace App\Http\Controllers\Customer;

use Illuminate\Http\Request;

class DashboardController extends CustomerController
{
    /**
     * Display customer dashboard.
     */
    public function index()
    {
        $customer = $this->getCustomer();

        $stats = [
            'total_orders' => $customer->orders()->count(),
            'pending_orders' => $customer->orders()->pending()->count(),
            'confirmed_orders' => $customer->orders()->confirmed()->count(),
            'completed_orders' => $customer->orders()->completed()->count(),
            'total_spent' => $customer->orders()->confirmed()->sum('total'),
        ];

        // Recent orders
        $recentOrders = $customer->orders()
            ->with(['artisan.artisanProfile', 'items.product.images', 'payment'])
            ->latest()
            ->take(5)
            ->get();

        return view('customer.dashboard', compact('stats', 'recentOrders'));
    }
}