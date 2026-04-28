<?php

namespace App\Http\Controllers\Customer;

use App\Models\Order;
use App\Models\Product;
use App\Models\Review;
use Illuminate\Http\Request;

class ReviewController extends CustomerController
{
    /**
     * Show review form.
     */
    public function create(Order $order, Product $product)
    {
        $this->authorize('create', [Review::class, $order, $product]);

        return view('customer.reviews.create', compact('order', 'product'));
    }

    /**
     * Store review.
     */
    public function store(Request $request, Order $order, Product $product)
    {
        $this->authorize('create', [Review::class, $order, $product]);

        $validated = $request->validate([
            'rating' => 'required|integer|min:1|max:5',
            'comment' => 'nullable|string|max:1000',
        ]);

        Review::create([
            'product_id' => $product->id,
            'customer_id' => auth()->id(),
            'order_id' => $order->id,
            'rating' => $validated['rating'],
            'comment' => $validated['comment'],
            'is_approved' => true, // Auto-approve or set to false for moderation
        ]);

        return redirect()
            ->route('customer.orders.show', $order)
            ->with('success', 'Review submitted successfully.');
    }
}
