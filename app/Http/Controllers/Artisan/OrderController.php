<?php

namespace App\Http\Controllers\Artisan;

use App\Models\Order;
use App\Services\OrderService;
use Illuminate\Http\Request;

class OrderController extends ArtisanController
{
    /**
     * Display artisan's orders.
     */
    public function index(Request $request)
    {
        $artisan = $this->getArtisan();

        $query = $artisan->artisanOrders()
            ->with(['customer', 'items.product', 'payment', 'rider']);

        // Filter by status
        if ($request->has('status') && $request->status !== 'all') {
            $query->where('status', $request->status);
        }

        $orders = $query->latest()->paginate(20);

        return view('artisan.orders.index', compact('orders'));
    }

    /**
     * Show order details.
     */
    public function show(Order $order)
    {
        $this->authorize('view', $order);

        $order->load([
            'customer',
            'items.product.images',
            'payment',
            'messages.sender',
            'rider',
            'packages.rider',
            'packages.items.orderItem',
        ]);

        return view('artisan.orders.show', compact('order'));
    }

    /**
     * Approve a pending order.
     */
    public function approve(Order $order, OrderService $orderService)
    {
        $this->authorize('approve', $order);

        if (! $order->canBeApproved()) {
            return back()->withErrors(['error' => 'Order cannot be approved at this time.']);
        }

        $order->update([
            'status' => 'shipped',
            'approved_at' => now(),
        ]);

        $orderService->assignRidersAfterSellerApproval($order->fresh());

        return back()->with('success', 'Order approved and marked as shipped. A rider will be assigned automatically when available.');
    }

    /**
     * Mark order as completed.
     */
    public function complete(Order $order)
    {
        $this->authorize('complete', $order);

        if (!$order->canBeCompleted()) {
            return back()->withErrors(['error' => 'Order cannot be completed at this time.']);
        }

        $order->update(['status' => 'completed']);

        return back()->with('success', 'Order marked as completed.');
    }
}