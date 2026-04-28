<?php

namespace App\Http\Controllers\Customer;

use App\Models\Cart;
use App\Models\Product;
use App\Services\CartService;
use Illuminate\Http\Request;

class CartController extends CustomerController
{
    public function __construct(protected CartService $cartService)
    {
        parent::__construct();
    }

    /**
     * Display shopping cart.
     */
    public function index()
    {
        $customer = $this->getCustomer();

        $cartItems = $customer->cart()
            ->with(['product.primaryImage', 'product.images', 'product.category', 'product.artisan.artisanProfile'])
            ->get();

        // Group by artisan for checkout preview
        $groupedByArtisan = $cartItems->groupBy('product.artisan_id');

        $total = $cartItems->sum(function ($item) {
            return $item->product->price * $item->quantity;
        });

        return view('customer.cart.index', compact('cartItems', 'groupedByArtisan', 'total'));
    }

    /**
     * Add product to cart.
     */
    public function add(Request $request, Product $product)
    {
        if ($request->user()->isArtisan() && $product->isOwnedBy($request->user())) {
            return redirect()
                ->route('home')
                ->with('error', 'You cannot purchase your own products.');
        }

        if (! $product->isAvailable()) {
            // Even on error, send the customer to the cart so they
            // clearly see the problem instead of just reloading the page.
            return redirect()
                ->route('customer.cart.index')
                ->with('error', 'Product is not available.')
                ->withErrors(['error' => 'Product is not available.']);
        }

        $validated = $request->validate([
            'quantity' => 'required|integer|min:1|max:'.$product->stock,
        ]);

        $customer = $this->getCustomer();

        $cartItem = $customer->cart()->where('product_id', $product->id)->first();

        if ($cartItem) {
            $newQuantity = $cartItem->quantity + $validated['quantity'];

            if ($newQuantity > $product->stock) {
                return redirect()
                    ->route('customer.cart.index')
                    ->with('error', 'Not enough stock available.')
                    ->withErrors(['error' => 'Not enough stock available.']);
            }

            $cartItem->update(['quantity' => $newQuantity]);
        } else {
            Cart::create([
                'user_id' => $customer->id,
                'product_id' => $product->id,
                'quantity' => $validated['quantity'],
            ]);
        }

        if ($request->get('redirect') === 'checkout') {
            return redirect()->route('customer.checkout.index')->with('success', 'Item added to your cart. Complete delivery details below to place your order.');
        }

        return redirect()->route('customer.cart.index')->with('success', 'Item successfully added to cart.');
    }

    /**
     * Update cart item quantity.
     */
    public function update(Request $request, Cart $cart)
    {
        if ($cart->user_id !== auth()->id()) {
            abort(403);
        }

        $validated = $request->validate([
            'quantity' => 'required|integer|min:1|max:'.$cart->product->stock,
        ]);

        try {
            $this->cartService->updateQuantity($cart->fresh(['product']), (int) $validated['quantity']);
        } catch (\Exception $e) {
            return back()->withErrors([
                'quantity' => 'We couldn’t update that quantity. Please choose an amount within available stock.',
            ]);
        }

        return back()->with('success', 'Cart updated.');
    }

    /**
     * Remove item from cart.
     */
    public function remove(Cart $cart)
    {
        if ($cart->user_id !== auth()->id()) {
            abort(403);
        }

        $cart->delete();

        return back()->with('success', 'Item removed from cart.');
    }

    /**
     * Clear entire cart.
     */
    public function clear()
    {
        $this->getCustomer()->cart()->delete();

        return back()->with('success', 'Cart cleared.');
    }
}
