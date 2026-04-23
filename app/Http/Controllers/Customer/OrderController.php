<?php

namespace App\Http\Controllers\Customer;

use App\Http\Requests\CancelOrderRequest;
use App\Models\Order;
use App\Services\ImageUploadService;
use App\Services\OrderService;
use Illuminate\Http\Request;

class OrderController extends CustomerController
{
    public function __construct(
        protected ImageUploadService $imageUploadService
    ) {
        parent::__construct();
    }

    /**
     * Display customer's orders.
     */
    public function index(Request $request)
    {
        $customer = $this->getCustomer();

        $query = $customer->orders()
            ->with(['artisan.artisanProfile', 'items.product', 'payment']);

        // Filter by status
        if ($request->has('status') && $request->status !== 'all') {
            $query->where('status', $request->status);
        }

        $orders = $query->latest()->paginate(20)->withQueryString();
        $statusFilter = $request->input('status', 'all');

        return view('customer.orders.index', compact('orders', 'statusFilter'));
    }

    /**
     * Show order details.
     */
    public function show(Order $order)
    {
        $this->authorize('view', $order);

        $order->load([
            'artisan.artisanProfile',
            'items.product.images',
            'payment',
            'messages.sender'
        ]);

        return view('customer.orders.show', compact('order'));
    }

    /**
     * Upload payment proof.
     */
    public function uploadPaymentProof(Request $request, Order $order)
    {
        $this->authorize('view', $order);

        $validated = $request->validate([
            'proof_image' => 'required|image|mimes:jpeg,png,jpg|max:2048',
        ]);

        $payment = $order->payment;

        if (!$payment || !$payment->isAwaitingProof()) {
            return back()->withErrors(['error' => 'Payment proof cannot be uploaded at this time.']);
        }

        $filename = $this->imageUploadService->uploadPaymentProof(
            $request->file('proof_image'),
            $order->id
        );

        $payment->update([
            'proof_image' => $filename,
            'verification_status' => 'pending',
        ]);

        return back()->with('success', 'Payment proof uploaded successfully. Awaiting admin verification.');
    }

    /**
     * Mark order as received.
     */
    public function markReceived(Order $order)
    {
        $this->authorize('view', $order);

        if (!$order->canBeReceived()) {
            return back()->withErrors(['error' => 'Order cannot be marked as received at this time.']);
        }

        $order->update(['status' => 'delivered']);

        return back()->with('success', 'Order marked as received. Thank you for your feedback!');
    }

    /**
     * Cancel order (customer: pending only; admin: any state — see policy and OrderService).
     */
    public function cancel(CancelOrderRequest $request, Order $order, OrderService $orderService)
    {
        try {
            $orderService->cancelOrder($order->fresh());
        } catch (\Throwable $e) {
            return back()->withErrors(['order' => $e->getMessage()]);
        }

        return redirect()
            ->route('customer.orders.index')
            ->with('success', 'Order has been cancelled.');
    }
}