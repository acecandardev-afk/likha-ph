<?php

namespace App\Http\Controllers\Rider;

use App\Models\OrderPackage;
use App\Services\DeliveryService;
use App\Services\ImageUploadService;
use Illuminate\Http\Request;

class DeliveryController extends RiderController
{
    public function __construct(
        protected DeliveryService $deliveryService,
        protected ImageUploadService $imageUploadService
    ) {
        parent::__construct();
    }

    public function index(Request $request)
    {
        $rider = $this->getRiderUser()->riderProfile;

        $query = OrderPackage::query()
            ->where('rider_id', $rider?->id)
            ->with(['order.customer', 'order.artisan'])
            ->latest();

        if ($request->filled('status')) {
            $query->where('delivery_status', $request->status);
        }

        $packages = $query->paginate(20)->withQueryString();
        $statusOptions = $this->deliveryService->deliveryStatusOptions();

        return view('rider.deliveries.index', compact('packages', 'statusOptions'));
    }

    public function show(OrderPackage $orderPackage)
    {
        $rider = $this->getRiderUser()->riderProfile;
        abort_unless($orderPackage->rider_id === $rider?->id, 403);

        $orderPackage->load([
            'order.customer',
            'order.artisan',
            'order.deliveryHistory.actor',
            'items.orderItem.product',
        ]);
        $order = $orderPackage->order;
        $statusOptions = $this->deliveryService->deliveryStatusOptions();

        return view('rider.deliveries.show', compact('orderPackage', 'order', 'statusOptions'));
    }

    public function updateStatus(Request $request, OrderPackage $orderPackage)
    {
        $rider = $this->getRiderUser()->riderProfile;
        abort_unless($orderPackage->rider_id === $rider?->id, 403);

        $validated = $request->validate([
            'delivery_status' => 'required|string',
            'note' => 'nullable|string|max:255',
            'proof_image' => 'nullable|image|mimes:jpeg,png,jpg|max:4096',
        ]);

        if (($validated['delivery_status'] ?? null) === DeliveryService::STATUS_DELIVERED && $request->hasFile('proof_image')) {
            $filename = $this->imageUploadService->uploadDeliveryProof($request->file('proof_image'), $orderPackage->order_id);
            $orderPackage->update([
                'delivery_proof_image' => $filename,
            ]);
        }

        try {
            $this->deliveryService->updateDeliveryStatus(
                $orderPackage->fresh(),
                $validated['delivery_status'],
                $request->user(),
                $validated['note'] ?? null
            );
        } catch (\Throwable $e) {
            return back()->withErrors(['delivery_status' => $e->getMessage()]);
        }

        return back()->with('success', 'Delivery progress updated.');
    }
}
