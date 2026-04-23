<?php

namespace App\Services;

use App\Models\Cart;
use App\Models\Product;
use App\Models\User;
use Illuminate\Support\Collection;

class CartService
{
    public function __construct(
        protected StockService $stockService
    ) {}

    /**
     * Get cart items for a user.
     */
    public function getCartItems(User $user): Collection
    {
        return $user->cart()
            ->with(['product.images', 'product.artisan.artisanProfile'])
            ->get();
    }

    /**
     * Add item to cart.
     */
    public function addToCart(User $user, Product $product, int $quantity): Cart
    {
        // Prevent sellers from purchasing their own products
        if ($user->isArtisan() && $product->artisan_id === $user->id) {
            throw new \Exception("You cannot purchase your own products.");
        }

        // Validate product availability
        if (!$product->isAvailable()) {
            throw new \Exception("Product '{$product->name}' is not available.");
        }

        // Validate stock
        if (!$this->stockService->hasStock($product, $quantity)) {
            throw new \Exception("Insufficient stock for '{$product->name}'. Available: {$product->stock}");
        }

        // Check if item already exists in cart
        $cartItem = $user->cart()->where('product_id', $product->id)->first();

        if ($cartItem) {
            $newQuantity = $cartItem->quantity + $quantity;

            // Validate total quantity
            if (!$this->stockService->hasStock($product, $newQuantity)) {
                throw new \Exception("Cannot add more. Maximum available: {$product->stock}");
            }

            $cartItem->update(['quantity' => $newQuantity]);
            return $cartItem;
        }

        // Create new cart item
        return Cart::create([
            'user_id' => $user->id,
            'product_id' => $product->id,
            'quantity' => $quantity,
        ]);
    }

    /**
     * Update cart item quantity.
     */
    public function updateQuantity(Cart $cartItem, int $quantity): Cart
    {
        if ($quantity < 1) {
            throw new \Exception("Quantity must be at least 1.");
        }

        // Validate stock
        if (!$this->stockService->hasStock($cartItem->product, $quantity)) {
            throw new \Exception("Insufficient stock. Available: {$cartItem->product->stock}");
        }

        $cartItem->update(['quantity' => $quantity]);
        return $cartItem->fresh();
    }

    /**
     * Remove item from cart.
     */
    public function removeFromCart(Cart $cartItem): bool
    {
        return $cartItem->delete();
    }

    /**
     * Clear entire cart for a user.
     */
    public function clearCart(User $user): bool
    {
        return $user->cart()->delete();
    }

    /**
     * Calculate cart total.
     */
    public function getCartTotal(User $user): float
    {
        $cartItems = $this->getCartItems($user);

        return $cartItems->sum(function ($item) {
            return $item->product->price * $item->quantity;
        });
    }

    /**
     * Get cart count.
     */
    public function getCartCount(User $user): int
    {
        return $user->cart()->sum('quantity');
    }

    /**
     * Group cart items by artisan.
     */
    public function groupByArtisan(User $user): Collection
    {
        $cartItems = $this->getCartItems($user);

        return $cartItems->groupBy('product.artisan_id');
    }

    /**
     * Validate cart items (check availability and stock).
     */
    public function validateCart(User $user): array
    {
        $cartItems = $this->getCartItems($user);
        $errors = [];

        foreach ($cartItems as $item) {
            if ($user->isArtisan() && $item->product->artisan_id === $user->id) {
                $errors[] = "Remove your own product '{$item->product->name}' from the cart. You cannot purchase it.";
            }

            // Check if product is still available
            if (!$item->product->isAvailable()) {
                $errors[] = "Product '{$item->product->name}' is no longer available.";
            }

            // Check stock
            if (!$this->stockService->hasStock($item->product, $item->quantity)) {
                $errors[] = "Insufficient stock for '{$item->product->name}'. Available: {$item->product->stock}";
            }
        }

        return $errors;
    }

    /**
     * Sync cart quantities with available stock.
     */
    public function syncWithStock(User $user): array
    {
        $cartItems = $this->getCartItems($user);
        $adjusted = [];

        foreach ($cartItems as $item) {
            if ($item->quantity > $item->product->stock) {
                if ($item->product->stock > 0) {
                    $item->update(['quantity' => $item->product->stock]);
                    $adjusted[] = [
                        'product' => $item->product->name,
                        'old_quantity' => $item->quantity,
                        'new_quantity' => $item->product->stock,
                    ];
                } else {
                    $item->delete();
                    $adjusted[] = [
                        'product' => $item->product->name,
                        'removed' => true,
                    ];
                }
            }
        }

        return $adjusted;
    }

    /**
     * Get cart summary for display.
     */
    public function getCartSummary(User $user): array
    {
        $cartItems = $this->getCartItems($user);
        $groupedByArtisan = $this->groupByArtisan($user);

        $summary = [
            'items' => $cartItems,
            'grouped_by_artisan' => $groupedByArtisan,
            'total_items' => $cartItems->count(),
            'total_quantity' => $cartItems->sum('quantity'),
            'subtotal' => $this->getCartTotal($user),
            'artisan_count' => $groupedByArtisan->count(),
        ];

        return $summary;
    }
}