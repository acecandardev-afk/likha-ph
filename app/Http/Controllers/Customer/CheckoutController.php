<?php

namespace App\Http\Controllers\Customer;

use App\Http\Requests\CheckoutRequest;
use App\Services\CartService;
use App\Services\OrderService;

class CheckoutController extends CustomerController
{
    public function __construct(
        protected CartService $cartService,
        protected OrderService $orderService
    ) {
        parent::__construct();
    }

    public function index()
    {
        $customer = $this->getCustomer();
        $summary = $this->cartService->getCartSummary($customer);

        if ($summary['total_items'] === 0) {
            return redirect()->route('customer.cart.index')
                ->withErrors(['error' => 'Your cart is empty.']);
        }

        // Validate cart
        $errors = $this->cartService->validateCart($customer);
        if (!empty($errors)) {
            return redirect()->route('customer.cart.index')
                ->withErrors($errors);
        }

        return view('customer.checkout.index', compact('summary'));
    }

    public function store(CheckoutRequest $request)
    {
        $validated = $request->validated();

        try {
            $orders = $this->orderService->createOrdersFromCart(
                $this->getCustomer(),
                $validated['payment_method'],
                $validated['shipping_barangay'],
                $validated['shipping_address'] ?? null,
                $validated['shipping_phone'],
                $validated['customer_notes'] ?? null
            );

            return redirect()
                ->route('customer.orders.index')
                ->with('success', count($orders) . ' order(s) placed successfully!');
        } catch (\Exception $e) {
            return back()
                ->withInput()
                ->withErrors(['error' => $e->getMessage()]);
        }
    }
}