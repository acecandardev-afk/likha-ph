<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Admin\AdminController;
use App\Models\Product;
use App\Models\Order;
use App\Models\Payment;
use App\Models\User;
use App\Models\Review;
use Illuminate\Http\Request;

class DashboardController extends AdminController
{
    /**
     * Display admin dashboard.
     */
    public function index()
    {
        $stats = [
            'pending_products' => Product::pending()->count(),
            'pending_payments' => Payment::pending()->count(),
            'total_artisans' => User::artisans()->count(),
            'total_customers' => User::customers()->count(),
            'total_orders' => Order::count(),
            'pending_orders' => Order::pending()->count(),
            'total_revenue' => Order::confirmed()->sum('total'),
            'unapproved_reviews' => Review::where('is_approved', false)->count(),
        ];

        // Recent activity
        $recentProducts = Product::pending()
            ->with('artisan', 'category')
            ->latest()
            ->take(5)
            ->get();

        $recentPayments = Payment::pending()
            ->with('order.customer')
            ->latest()
            ->take(5)
            ->get();

        $recentOrders = Order::with('customer', 'artisan')
            ->latest()
            ->take(10)
            ->get();

        return view('admin.dashboard', compact(
            'stats',
            'recentProducts',
            'recentPayments',
            'recentOrders'
        ));
    }
}