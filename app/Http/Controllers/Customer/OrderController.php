<?php

namespace App\Http\Controllers\Customer;

use App\Models\Order;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Storage;
use Intervention\Image\Laravel\Facades\Image;

class OrderController extends CustomerController
{
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

        $orders = $query->latest()->paginate(20);

        return view('customer.orders.index', compact('orders'));
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

        $paymentsDir = storage_path('app/public/payments');
        if (!File::isDirectory($paymentsDir)) {
            File::makeDirectory($paymentsDir, 0755, true);
        }

        $filename = uniqid('payment_' . $order->id . '_') . '.jpg';

        $image = Image::read($request->file('proof_image'));
        $image->scale(width: 800);
        $image->toJpeg(quality: 85)->save(
            $paymentsDir . '/' . $filename
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
}