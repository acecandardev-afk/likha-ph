<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Admin\AdminController;
use App\Models\Product;
use App\Models\Order;
use App\Models\Payment;
use App\Models\User;
use App\Models\Review;
use App\Models\Rider;
use App\Services\DeliveryService;
use Illuminate\Support\Facades\Cache;
use Illuminate\Http\Request;

class DashboardController extends AdminController
{
    /**
     * Display admin dashboard.
     */
    public function index()
    {
        $stats = Cache::remember('dashboard:admin:stats', 60, function () {
            return [
                'pending_products' => Product::pending()->count(),
                'pending_payments' => Payment::pending()->count(),
                'total_artisans' => User::artisans()->count(),
                'total_customers' => User::customers()->count(),
                'total_orders' => Order::count(),
                'pending_orders' => Order::pending()->count(),
                'total_revenue' => Order::confirmed()->sum('total'),
                'unapproved_reviews' => Review::where('is_approved', false)->count(),
                'total_riders' => Rider::count(),
                'available_riders' => Rider::where('status', Rider::STATUS_AVAILABLE)->count(),
                'pending_delivery_assignment' => Order::where('delivery_status', DeliveryService::STATUS_PENDING_ASSIGNMENT)->count(),
                'completed_deliveries' => Order::where('delivery_status', DeliveryService::STATUS_DELIVERED)->count(),
            ];
        });

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