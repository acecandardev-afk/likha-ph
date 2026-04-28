<?php

namespace App\Http\Controllers\Artisan;

use App\Models\Order;
use Illuminate\Support\Facades\Cache;

class DashboardController extends ArtisanController
{
    /**
     * Display artisan dashboard.
     */
    public function index()
    {
        $artisan = $this->getArtisan();

        $stats = Cache::remember("dashboard:artisan:{$artisan->id}:stats:v4", 60, function () use ($artisan) {
            return [
                'total_products' => $artisan->products()->count(),
                'approved_products' => $artisan->products()->approved()->count(),
                'pending_products' => $artisan->products()->pending()->count(),
                'rejected_products' => $artisan->products()->where('approval_status', 'rejected')->count(),
                'total_orders' => $artisan->artisanOrders()->count(),
                'pending_orders' => $artisan->artisanOrders()->pending()->count(),
                'shipped_orders' => $artisan->artisanOrders()->shipped()->count(),
                'on_delivery_orders' => $artisan->artisanOrders()->onDelivery()->count(),
                'delivered_orders' => $artisan->artisanOrders()->delivered()->count(),
                'confirmed_orders' => $artisan->artisanOrders()->wherePaymentVerified()->count(),
                'completed_orders' => $artisan->artisanOrders()->completed()->count(),
                'estimated_share_total' => (float) Order::query()
                    ->where('artisan_id', $artisan->id)
                    ->whereIn('status', ['delivered', 'completed'])
                    ->get()
                    ->sum(fn (Order $order) => $order->artisanMerchandiseShare()),
                'estimated_share_month' => (float) Order::query()
                    ->where('artisan_id', $artisan->id)
                    ->whereIn('status', ['delivered', 'completed'])
                    ->whereMonth('created_at', now()->month)
                    ->whereYear('created_at', now()->year)
                    ->get()
                    ->sum(fn (Order $order) => $order->artisanMerchandiseShare()),
            ];
        });

        // Recent products
        $recentProducts = $artisan->products()
            ->with('category', 'images')
            ->latest()
            ->take(5)
            ->get();

        // Recent orders
        $recentOrders = $artisan->artisanOrders()
            ->with('customer', 'items.product', 'payment')
            ->latest()
            ->take(10)
            ->get();

        // Low stock products
        $lowStockProducts = $artisan->products()
            ->approved()
            ->where('stock', '<=', 5)
            ->where('stock', '>', 0)
            ->get();

        return view('artisan.dashboard', compact(
            'stats',
            'recentProducts',
            'recentOrders',
            'lowStockProducts'
        ));
    }
}
