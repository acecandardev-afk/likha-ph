<?php

namespace App\Http\Controllers\Admin;

use App\Models\Payment;
use App\Models\Order;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class PaymentVerificationController extends AdminController
{
    /**
     * Display pending payment verifications.
     */
    public function index()
    {
        $pendingPayments = Payment::pending()
            ->with(['order.customer', 'order.artisan', 'order.items.product'])
            ->latest()
            ->paginate(20);

        return view('admin.payments.pending', compact('pendingPayments'));
    }

    /**
     * Show payment details for verification.
     */
    public function show(Payment $payment)
    {
        $payment->load(['order.customer', 'order.artisan', 'order.items.product']);

        return view('admin.payments.review', compact('payment'));
    }

    /**
     * Verify a payment.
     */
    public function verify(Request $request, Payment $payment)
    {
        $request->validate([
            'notes' => 'nullable|string|max:500',
        ]);

        DB::transaction(function () use ($request, $payment) {
            $payment->update([
                'verification_status' => 'verified',
                'verified_by' => auth()->id(),
                'verification_notes' => $request->notes,
                'verified_at' => now(),
            ]);

            $payment->order->update([
                'status' => 'confirmed',
            ]);
        });

        return redirect()
            ->route('admin.payments.pending')
            ->with('success', "Payment for order {$payment->order->order_number} has been verified.");
    }

    /**
     * Reject a payment.
     */
    public function reject(Request $request, Payment $payment)
    {
        $request->validate([
            'reason' => 'required|string|max:500',
        ]);

        DB::transaction(function () use ($request, $payment) {
            $payment->update([
                'verification_status' => 'rejected',
                'verified_by' => auth()->id(),
                'verification_notes' => $request->reason,
                'verified_at' => now(),
            ]);

            // Optionally restore stock
            foreach ($payment->order->items as $item) {
                $item->product->increment('stock', $item->quantity);
            }
        });

        return redirect()
            ->route('admin.payments.pending')
            ->with('success', "Payment for order {$payment->order->order_number} has been rejected.");
    }

    /**
     * Display verified payments.
     */
    public function verified()
    {
        $verifiedPayments = Payment::verified()
            ->with(['order.customer', 'order.artisan'])
            ->latest('verified_at')
            ->paginate(20);

        return view('admin.payments.verified', compact('verifiedPayments'));
    }
}