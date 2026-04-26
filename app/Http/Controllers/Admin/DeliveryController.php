<?php

namespace App\Http\Controllers\Admin;

use App\Models\Order;
use App\Models\Rider;
use App\Services\DeliveryService;
use Illuminate\Http\Request;

class DeliveryController extends AdminController
{
    public function __construct(
        protected DeliveryService $deliveryService
    ) {
        parent::__construct();
    }

    public function index(Request $request)
    {
        $query = Order::with(['customer', 'artisan', 'rider'])->latest();

        if ($request->filled('delivery_status')) {
            $query->where('delivery_status', $request->delivery_status);
        }

        if ($request->filled('rider_id')) {
            $query->where('rider_id', $request->integer('rider_id'));
        }

        if ($request->filled('delivery_date')) {
            $query->whereDate('delivery_completed_at', $request->delivery_date);
        }

        $deliveries = $query->paginate(20)->withQueryString();
        $riders = Rider::orderBy('full_name')->get(['id', 'full_name']);
        $statusOptions = [DeliveryService::STATUS_PENDING_ASSIGNMENT => 'Pending Delivery Assignment'] + $this->deliveryService->deliveryStatusOptions();

        return view('admin.deliveries.index', compact('deliveries', 'riders', 'statusOptions'));
    }

    public function assign(Order $order)
    {
        if (! $order->payment?->isVerified()) {
            return back()->withErrors(['delivery' => 'Order payment must be verified before assignment.']);
        }

        $rider = $this->deliveryService->assignRandomAvailableRider($order->fresh(['payment', 'rider']));

        if (! $rider) {
            return back()->with('warning', 'No available rider. Order remains pending delivery assignment.');
        }

        return back()->with('success', 'Order assigned to '.$rider->full_name.'.');
    }

    public function updateStatus(Request $request, Order $order)
    {
        $validated = $request->validate([
            'delivery_status' => 'required|string',
            'note' => 'nullable|string|max:255',
        ]);

        try {
            $this->deliveryService->updateDeliveryStatus($order, $validated['delivery_status'], $request->user(), $validated['note'] ?? null);
        } catch (\Throwable $e) {
            return back()->withErrors(['delivery_status' => $e->getMessage()]);
        }

        return back()->with('success', 'Delivery status updated.');
    }
}
