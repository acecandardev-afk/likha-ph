<?php

namespace App\Http\Controllers\Rider;

use App\Models\OrderPackage;
use App\Services\DeliveryService;
use App\Services\ImageUploadService;
use App\Support\SafeUserMessage;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Illuminate\Validation\Rule;

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
        $progressStatusOptions = $this->deliveryService->riderProgressStatusOptions();

        return view('rider.deliveries.show', compact('orderPackage', 'order', 'statusOptions', 'progressStatusOptions'));
    }

    public function updateStatus(Request $request, OrderPackage $orderPackage)
    {
        $rider = $this->getRiderUser()->riderProfile;
        abort_unless($orderPackage->rider_id === $rider?->id, 403);

        $orderPackage->refresh();
        if ($orderPackage->isDelivered()) {
            return back()->withErrors([
                'delivery' => 'This package is delivered. Progress can no longer be changed.',
            ]);
        }

        if ($request->boolean('mark_delivered')) {
            $validated = $request->validate([
                'confirm_handoff' => 'accepted',
                'proof_image' => 'required|image|mimes:jpeg,png,jpg,webp|max:4096',
                'note' => 'nullable|string|max:255',
            ]);

            $filename = $this->imageUploadService->uploadDeliveryProof($request->file('proof_image'), $orderPackage->order_id);
            $orderPackage->update([
                'delivery_proof_image' => $filename,
            ]);

            try {
                $this->deliveryService->updateDeliveryStatus(
                    $orderPackage->fresh(),
                    DeliveryService::STATUS_DELIVERED,
                    $request->user(),
                    $validated['note'] ?? null
                );
            } catch (\InvalidArgumentException $e) {
                return back()->withErrors(['delivery' => SafeUserMessage::forDeliveryInvalidArgument($e)]);
            } catch (\Throwable $e) {
                Log::warning('rider_delivery_status_update_failed', ['message' => $e->getMessage()]);

                return back()->with('error', 'Unable to complete delivery. Please try again.');
            }

            return back()->with('success', 'Package marked as delivered. Thank you.');
        }

        $progressKeys = array_keys($this->deliveryService->riderProgressStatusOptions());

        $validated = $request->validate([
            'delivery_status' => ['required', 'string', Rule::in($progressKeys)],
            'note' => 'nullable|string|max:255',
        ]);

        try {
            $this->deliveryService->updateDeliveryStatus(
                $orderPackage->fresh(),
                $validated['delivery_status'],
                $request->user(),
                $validated['note'] ?? null
            );
        } catch (\InvalidArgumentException $e) {
            return back()->withErrors(['delivery' => SafeUserMessage::forDeliveryInvalidArgument($e)]);
        } catch (\Throwable $e) {
            Log::warning('rider_delivery_status_update_failed', ['message' => $e->getMessage()]);

            return back()->with('error', 'Unable to update delivery status. Please try again.');
        }

        return back()->with('success', 'Delivery progress updated.');
    }
}
