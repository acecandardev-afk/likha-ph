<?php

namespace App\Http\Controllers\Admin;

use App\Models\OrderPackage;
use App\Models\Rider;
use App\Services\DeliveryService;
use App\Support\SafeUserMessage;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;

class DeliveryController extends AdminController
{
    public function __construct(
        protected DeliveryService $deliveryService
    ) {
        parent::__construct();
    }

    public function index(Request $request)
    {
        $query = OrderPackage::with(['order.customer', 'order.artisan', 'rider'])->latest();

        if ($request->filled('delivery_status')) {
            $query->where('delivery_status', $request->delivery_status);
        }

        if ($request->filled('rider_id')) {
            $query->where('rider_id', $request->integer('rider_id'));
        }

        if ($request->filled('delivery_date')) {
            $query->whereDate('delivery_completed_at', $request->delivery_date);
        }

        $packages = $query->paginate(20)->withQueryString();
        $riders = Rider::orderBy('full_name')->get(['id', 'full_name']);
        $statusOptions = [DeliveryService::STATUS_PENDING_ASSIGNMENT => 'Pending Delivery Assignment'] + $this->deliveryService->deliveryStatusOptions();

        return view('admin.deliveries.index', compact('packages', 'riders', 'statusOptions'));
    }

    public function assign(OrderPackage $orderPackage)
    {
        if (! $orderPackage->order->payment?->isVerified()) {
            return back()->withErrors(['delivery' => 'Order payment must be verified before assignment.']);
        }

        if (! $orderPackage->order->isSellerApprovedForFulfillment()) {
            return back()->withErrors(['delivery' => 'The seller must approve the order before a rider can be assigned.']);
        }

        $rider = $this->deliveryService->assignRandomAvailableRider($orderPackage->fresh(['order.payment']));

        if (! $rider) {
            return back()->with('warning', 'No available rider with capacity. Package remains pending assignment.');
        }

        return back()->with('success', 'Package assigned to '.$rider->full_name.'.');
    }

    public function updateStatus(Request $request, OrderPackage $orderPackage)
    {
        $orderPackage->refresh();
        if ($orderPackage->isDelivered()) {
            return back()->withErrors([
                'delivery' => 'Delivered packages can no longer be updated.',
            ]);
        }

        $validated = $request->validate([
            'delivery_status' => 'required|string',
            'note' => 'nullable|string|max:255',
        ]);

        try {
            $this->deliveryService->updateDeliveryStatus(
                $orderPackage,
                $validated['delivery_status'],
                $request->user(),
                $validated['note'] ?? null
            );
        } catch (\InvalidArgumentException $e) {
            return back()->withErrors(['delivery' => SafeUserMessage::forDeliveryInvalidArgument($e)]);
        } catch (\Throwable $e) {
            Log::warning('admin_delivery_status_update_failed', ['message' => $e->getMessage()]);

            return back()->with('error', 'Unable to update delivery status. Please try again.');
        }

        return back()->with('success', 'Delivery status updated.');
    }
}
