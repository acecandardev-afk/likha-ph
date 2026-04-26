<?php

namespace App\Services;

use App\Models\Order;
use App\Models\OrderItem;
use App\Models\Payment;
use App\Models\User;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;

class OrderService
{
    public const PLATFORM_FEE_RATE = 0.05;

    public function __construct(
        protected StockService $stockService,
        protected CartService $cartService,
        protected NotificationService $notificationService,
        protected DeliveryService $deliveryService
    ) {}

    /**
     * Create orders from cart (multi-artisan checkout).
     */
    public function createOrdersFromCart(
        User $customer,
        string $paymentMethod,
        string $country,
        string $region,
        string $province,
        string $city,
        string $barangay,
        ?string $streetAddress,
        string $phone,
        ?string $customerNotes = null
    ): Collection {
        // Validate cart
        $validationErrors = $this->cartService->validateCart($customer);
        if (!empty($validationErrors)) {
            throw new \Exception(implode(', ', $validationErrors));
        }

        $cartItems = $this->cartService->getCartItems($customer);
        
        if ($cartItems->isEmpty()) {
            throw new \Exception("Cart is empty.");
        }

        $orders = collect();

        DB::transaction(function () use ($customer, $cartItems, $paymentMethod, $country, $region, $province, $city, $barangay, $streetAddress, $phone, $customerNotes, &$orders) {
            // Group cart items by artisan
            $groupedByArtisan = $cartItems->groupBy('product.artisan_id');

            foreach ($groupedByArtisan as $artisanId => $items) {
                $order = $this->createSingleOrder(
                    $customer,
                    $artisanId,
                    $items,
                    $paymentMethod,
                    $country,
                    $region,
                    $province,
                    $city,
                    $barangay,
                    $streetAddress,
                    $phone,
                    $customerNotes
                );

                $orders->push($order);
            }

            // Clear cart after successful order creation
            $this->cartService->clearCart($customer);
        });

        // Send notifications
        foreach ($orders as $order) {
            $this->notificationService->notifyOrderCreated($order);
        }

        return $orders;
    }

    /**
     * Create a single order.
     */
    private function createSingleOrder(
        User $customer,
        int $artisanId,
        Collection $items,
        string $paymentMethod,
        string $country,
        string $region,
        string $province,
        string $city,
        string $barangay,
        ?string $streetAddress,
        string $phone,
        ?string $customerNotes
    ): Order {
        $subtotal = 0;

        // Validate stock for all items
        foreach ($items as $item) {
            if (!$this->stockService->hasStock($item->product, $item->quantity)) {
                throw new \Exception("Insufficient stock for '{$item->product->name}'");
            }
            $subtotal += $item->product->price * $item->quantity;
        }

        $platformFeeRate = (float) config('fees.platform_fee_rate', self::PLATFORM_FEE_RATE);
        $platformFee = round($subtotal * $platformFeeRate, 2);
        $total = $subtotal + $platformFee;

        // Create order
        $order = Order::create([
            'customer_id' => $customer->id,
            'artisan_id' => $artisanId,
            'subtotal' => $subtotal,
            'platform_fee' => $platformFee,
            'total' => $total,
            'status' => 'pending',
            'customer_notes' => $customerNotes,
            'country' => $country,
            'region' => $region,
            'province' => $province,
            'city' => $city,
            'barangay' => $barangay,
            'street_address' => $streetAddress,
            'shipping_phone' => $phone,
        ]);

        // Create order items and reduce each product's stock by the ordered quantity
        foreach ($items as $item) {
            $this->createOrderItem($order, $item);
            $product = $item->product;
            $product->decrement('stock', $item->quantity);
            $product->refresh();

            if ($product->approval_status === 'approved') {
                if ($product->stock === 0) {
                    $this->notificationService->notifyOutOfStock($product);
                } elseif ($product->stock <= StockService::LOW_STOCK_THRESHOLD) {
                    $this->notificationService->notifyLowStock($product);
                }
            }
        }

        // Create payment record (COD does not require proof)
        $verificationStatus = $paymentMethod === 'cod' ? 'verified' : 'awaiting_proof';
        Payment::create([
            'order_id' => $order->id,
            'payment_method' => $paymentMethod,
            'amount' => $order->total,
            'verification_status' => $verificationStatus,
        ]);

        // Initial delivery state waits for assignment. For COD (already verified),
        // assignment is attempted immediately after order creation.
        $order->update([
            'delivery_status' => DeliveryService::STATUS_PENDING_ASSIGNMENT,
        ]);

        if ($verificationStatus === 'verified') {
            $this->deliveryService->assignRandomAvailableRider($order->fresh(['payment', 'rider']));
        }

        return $order->fresh(['items', 'payment', 'artisan']);
    }

    /**
     * Create order item with product snapshot.
     */
    private function createOrderItem(Order $order, $cartItem): OrderItem
    {
        $product = $cartItem->product;
        $subtotal = $product->price * $cartItem->quantity;

        return OrderItem::create([
            'order_id' => $order->id,
            'product_id' => $product->id,
            'product_name' => $product->name,
            'product_description' => $product->description,
            'price' => $product->price,
            'quantity' => $cartItem->quantity,
            'subtotal' => $subtotal,
        ]);
    }

    /**
     * Cancel an order.
     */
    public function cancelOrder(Order $order): Order
    {
        $isAdmin = auth()->user()?->isAdmin() ?? false;
        if (! $isAdmin && ! $order->canBeCancelled()) {
            throw new \Exception('Order cannot be cancelled at this time.');
        }

        DB::transaction(function () use ($order) {
            // Restore stock
            $this->stockService->restoreStockFromOrder($order->items);

            // Update order status
            $order->update(['status' => 'cancelled']);
        });

        // Send notification
        $this->notificationService->notifyOrderCancelled($order);

        return $order->fresh();
    }

    /**
     * Mark order as completed.
     */
    public function completeOrder(Order $order): Order
    {
        if (!$order->canBeCompleted()) {
            throw new \Exception("Order cannot be completed at this time.");
        }

        $order->update(['status' => 'completed']);

        // Send notification
        $this->notificationService->notifyOrderCompleted($order);

        return $order->fresh();
    }

    /**
     * Get order statistics for a user.
     */
    public function getOrderStats(User $user, string $role = 'customer'): array
    {
        $query = $role === 'customer' 
            ? $user->orders() 
            : $user->artisanOrders();

        return [
            'total' => $query->count(),
            'pending' => $query->pending()->count(),
            'confirmed' => $query->confirmed()->count(),
            'completed' => $query->completed()->count(),
            'cancelled' => $query->where('status', 'cancelled')->count(),
            'total_value' => $query->confirmed()->sum('total'),
            'monthly_value' => $query->confirmed()
                ->whereMonth('created_at', now()->month)
                ->sum('total'),
        ];
    }

    /**
     * Get recent orders.
     */
    public function getRecentOrders(User $user, string $role = 'customer', int $limit = 10): Collection
    {
        $query = $role === 'customer' 
            ? $user->orders() 
            : $user->artisanOrders();

        return $query->with(['artisan.artisanProfile', 'customer', 'items.product', 'payment'])
            ->latest()
            ->take($limit)
            ->get();
    }

    /**
     * Get pending orders requiring action.
     */
    public function getPendingOrders(int $artisanId): Collection
    {
        return Order::where('artisan_id', $artisanId)
            ->whereIn('status', ['pending', 'confirmed'])
            ->with(['customer', 'items.product', 'payment'])
            ->latest()
            ->get();
    }

    /**
     * Calculate order totals.
     */
    public function calculateOrderTotals(Collection $items): array
    {
        $subtotal = $items->sum(function ($item) {
            return $item->product->price * $item->quantity;
        });

        $platformFeeRate = (float) config('fees.platform_fee_rate', self::PLATFORM_FEE_RATE);
        $platformFee = round($subtotal * $platformFeeRate, 2);

        // Future: Add shipping, taxes, discounts
        $shipping = 0;
        $tax = 0;
        $discount = 0;

        $total = $subtotal + $platformFee + $shipping + $tax - $discount;

        return [
            'subtotal' => $subtotal,
            'platform_fee' => $platformFee,
            'shipping' => $shipping,
            'tax' => $tax,
            'discount' => $discount,
            'total' => $total,
        ];
    }
}