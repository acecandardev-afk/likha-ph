<?php

namespace App\Http\Controllers\Customer;

use App\Http\Requests\StoreOrderItemReturnRequest;
use App\Models\Order;
use App\Models\OrderItem;
use App\Models\OrderItemReturn;
use App\Services\ImageUploadService;
use App\Services\NotificationService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class OrderItemReturnController extends CustomerController
{
    public function index(Request $request)
    {
        $customer = $this->getCustomer();

        $query = OrderItemReturn::query()
            ->where('customer_id', $customer->id)
            ->with(['order', 'orderItem.product']);

        if ($request->filled('status')) {
            $query->where('status', $request->string('status'));
        }

        $returns = $query->latest()->paginate(20)->withQueryString();

        return view('customer.returns.index', compact('returns'));
    }

    public function show(OrderItemReturn $orderItemReturn)
    {
        $this->authorize('view', $orderItemReturn);

        $orderItemReturn->load(['order', 'orderItem.product', 'reviewer']);

        return view('customer.returns.show', compact('orderItemReturn'));
    }

    public function create(Order $order, OrderItem $orderItem)
    {
        $this->authorize('view', $order);

        abort_unless($orderItem->order_id === $order->id, 404);
        abort_unless($order->isEligibleForItemReturns(), 403, 'Returns are only available after your order is marked received or delivered.');
        abort_unless($order->customer_id === $this->getCustomer()->id, 403);

        $returnable = $orderItem->returnableQuantity();
        abort_unless($returnable > 0, 403, 'There is nothing left to return for this line, or a return is already pending.');

        if (OrderItemReturn::query()
            ->where('order_item_id', $orderItem->id)
            ->where('status', OrderItemReturn::STATUS_PENDING_ADMIN)
            ->exists()) {
            return redirect()
                ->route('customer.orders.show', $order)
                ->with('warning', 'You already have a return pending review for this line.');
        }

        $order->loadMissing('items.product');
        $orderItem->loadMissing('product');

        return view('customer.returns.create', compact('order', 'orderItem', 'returnable'));
    }

    public function store(StoreOrderItemReturnRequest $request, Order $order, OrderItem $orderItem, ImageUploadService $imageUploadService, NotificationService $notificationService)
    {
        $this->authorize('view', $order);

        abort_unless($orderItem->order_id === $order->id, 404);
        abort_unless($order->isEligibleForItemReturns(), 403);
        abort_unless($order->customer_id === $this->getCustomer()->id, 403);

        $returnable = $orderItem->returnableQuantity();
        abort_unless($returnable > 0, 403);

        if (OrderItemReturn::query()
            ->where('order_item_id', $orderItem->id)
            ->where('status', OrderItemReturn::STATUS_PENDING_ADMIN)
            ->exists()) {
            return back()->withErrors(['order' => 'A return for this line is already pending admin review.']);
        }

        $validated = $request->validated();
        $qty = min((int) $validated['quantity'], $returnable);
        if ($qty < 1) {
            return back()->withErrors(['quantity' => 'Invalid return quantity.']);
        }

        $proofFilename = $imageUploadService->uploadOrderReturnProof(
            $request->file('proof_image'),
            $orderItem->id
        );

        $return = DB::transaction(function () use ($order, $orderItem, $validated, $qty, $proofFilename) {
            return OrderItemReturn::create([
                'order_id' => $order->id,
                'order_item_id' => $orderItem->id,
                'customer_id' => $order->customer_id,
                'artisan_id' => $order->artisan_id,
                'quantity' => $qty,
                'reason' => $validated['reason'],
                'notes' => $validated['notes'],
                'proof_image' => $proofFilename,
                'status' => OrderItemReturn::STATUS_PENDING_ADMIN,
            ]);
        });

        $notificationService->notifyOrderItemReturnSubmitted($return->fresh(['order', 'orderItem']));

        return redirect()
            ->route('customer.returns.show', $return)
            ->with('success', 'Return request submitted. An admin will review your photo and details.');
    }
}
