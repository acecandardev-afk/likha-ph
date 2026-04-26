<?php

namespace App\Http\Controllers\Rider;

use App\Models\Order;
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

        $query = $rider->orders()->with(['customer', 'artisan'])->latest();

        if ($request->filled('status')) {
            $query->where('delivery_status', $request->status);
        }

        $orders = $query->paginate(20)->withQueryString();
        $statusOptions = $this->deliveryService->deliveryStatusOptions();

        return view('rider.deliveries.index', compact('orders', 'statusOptions'));
    }

    public function show(Order $order)
    {
        $rider = $this->getRiderUser()->riderProfile;
        abort_unless($order->rider_id === $rider?->id, 403);

        $order->load(['customer', 'artisan', 'deliveryHistory.actor', 'items.product']);
        $statusOptions = $this->deliveryService->deliveryStatusOptions();

        return view('rider.deliveries.show', compact('order', 'statusOptions'));
    }

    public function updateStatus(Request $request, Order $order)
    {
        $rider = $this->getRiderUser()->riderProfile;
        abort_unless($order->rider_id === $rider?->id, 403);

        $validated = $request->validate([
            'delivery_status' => 'required|string',
            'note' => 'nullable|string|max:255',
            'proof_image' => 'nullable|image|mimes:jpeg,png,jpg|max:4096',
        ]);

        if (($validated['delivery_status'] ?? null) === DeliveryService::STATUS_DELIVERED && $request->hasFile('proof_image')) {
            $filename = $this->imageUploadService->uploadDeliveryProof($request->file('proof_image'), $order->id);
            $order->update([
                'delivery_proof_image' => $filename,
            ]);
        }

        try {
            $this->deliveryService->updateDeliveryStatus($order->fresh(), $validated['delivery_status'], $request->user(), $validated['note'] ?? null);
        } catch (\Throwable $e) {
            return back()->withErrors(['delivery_status' => $e->getMessage()]);
        }

        return back()->with('success', 'Delivery progress updated.');
    }
}
