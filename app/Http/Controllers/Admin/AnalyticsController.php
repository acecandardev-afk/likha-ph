<?php

namespace App\Http\Controllers\Admin;

use App\Models\Order;
use App\Models\OrderItem;
use App\Models\User;

class AnalyticsController extends AdminController
{
    /**
     * Order trends and bestselling items (friendly labels — no jargon).
     */
    public function index()
    {
        $days = 14;
        $ordersByDay = [];
        for ($i = $days - 1; $i >= 0; $i--) {
            $day = now()->subDays($i)->toDateString();
            $ordersByDay[$day] = Order::whereDate('created_at', $day)->count();
        }

        $buyerAccounts = User::customers()->count();
        $buyersWhoOrdered = (int) Order::query()->select('customer_id')->distinct()->count('customer_id');
        $activityRate = $buyerAccounts > 0
            ? round(100 * $buyersWhoOrdered / $buyerAccounts, 1)
            : 0.0;

        $topItems = OrderItem::query()
            ->join('orders', 'order_items.order_id', '=', 'orders.id')
            ->join('products', 'order_items.product_id', '=', 'products.id')
            ->where('orders.created_at', '>=', now()->subDays(30))
            ->groupBy('products.id', 'products.name')
            ->selectRaw('products.name as name, SUM(order_items.quantity) as qty')
            ->orderByDesc('qty')
            ->limit(15)
            ->get();

        return view('admin.analytics.index', compact(
            'ordersByDay',
            'activityRate',
            'buyerAccounts',
            'buyersWhoOrdered',
            'topItems',
            'days'
        ));
    }
}
