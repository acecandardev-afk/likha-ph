<?php

namespace App\Http\Controllers\Artisan;

use App\Models\Order;
use App\Models\OrderItemReturn;
use Illuminate\Support\Facades\Cache;

class DashboardController extends ArtisanController
{
    /**
     * Display artisan dashboard.
     */
    public function index()
    {
        $artisan = $this->getArtisan();

        $stats = Cache::remember("dashboard:artisan:{$artisan->id}:stats:v7", 60, function () use ($artisan) {
            $scopedOrders = $artisan->artisanOrders()->notStaleCancelled();

            return [
                'total_products' => $artisan->products()->count(),
                'approved_products' => $artisan->products()->approved()->count(),
                'pending_products' => $artisan->products()->pending()->count(),
                'rejected_products' => $artisan->products()->where('approval_status', 'rejected')->count(),
                'total_orders' => $scopedOrders->clone()->count(),
                'pending_orders' => $scopedOrders->clone()->pending()->count(),
                'shipped_orders' => $scopedOrders->clone()->shipped()->count(),
                'on_delivery_orders' => $scopedOrders->clone()->onDelivery()->count(),
                'delivered_orders' => $scopedOrders->clone()->delivered()->count(),
                'confirmed_orders' => $scopedOrders->clone()->wherePaymentVerified()->count(),
                'completed_orders' => $scopedOrders->clone()->completed()->count(),
                'returns_pending_admin' => OrderItemReturn::query()
                    ->where('artisan_id', $artisan->id)
                    ->where('status', OrderItemReturn::STATUS_PENDING_ADMIN)
                    ->count(),
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
            ->notStaleCancelled()
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
