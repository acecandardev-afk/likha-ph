<?php

namespace App\Services;

use App\Models\Order;
use App\Models\OrderItem;
use App\Models\OrderPackage;
use App\Models\OrderPackageItem;
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
     *
     * @param  array<int, array<int, int>>|null  $packageSplit  [artisan_id => [ cart_id => package_number_1_based ]]
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
        ?string $customerNotes = null,
        ?array $packageSplit = null
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

        DB::transaction(function () use ($customer, $cartItems, $paymentMethod, $country, $region, $province, $city, $barangay, $streetAddress, $phone, $customerNotes, $packageSplit, &$orders) {
            // Group cart items by artisan
            $groupedByArtisan = $cartItems->groupBy('product.artisan_id');

            foreach ($groupedByArtisan as $artisanId => $items) {
                $groups = $this->buildCartGroupsForPackages($items, $packageSplit[(int) $artisanId] ?? []);

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
                    $customerNotes,
                    $groups
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
     * @param  Collection<int, \App\Models\Cart>  $items
     * @param  array<int, int>  $splitForArtisan  cart_id => package number (1-based)
     * @return array<int, array<int>>  List of cart-id groups per package
     */
    protected function buildCartGroupsForPackages(Collection $items, array $splitForArtisan): array
    {
        $cartIds = $items->pluck('id')->all();
        if ($splitForArtisan === []) {
            return [$cartIds];
        }

        $maxPkg = max(1, ...array_merge([1], array_values($splitForArtisan)));
        $groups = [];
        for ($i = 0; $i < $maxPkg; $i++) {
            $groups[$i] = [];
        }

        foreach ($items as $item) {
            $p = (int) ($splitForArtisan[$item->id] ?? 1);
            $p = max(1, min(10, $p));
            if (! isset($groups[$p - 1])) {
                $groups[$p - 1] = [];
            }
            $groups[$p - 1][] = $item->id;
        }

        $nonEmpty = array_values(array_filter($groups, fn ($g) => count($g) > 0));

        return count($nonEmpty) > 0 ? $nonEmpty : [$cartIds];
    }

    /**
     * Split platform fee across packages without losing cents.
     *
     * @return array<int, float>
     */
    protected function splitPlatformFeeShares(float $total, int $parts): array
    {
        if ($parts < 1) {
            return [];
        }

        $totalCents = (int) round($total * 100);
        $base = intdiv($totalCents, $parts);
        $rem = $totalCents % $parts;
        $shares = [];
        for ($i = 0; $i < $parts; $i++) {
            $shares[] = ($base + ($i < $rem ? 1 : 0)) / 100;
        }

        return $shares;
    }

    /**
     * Create a single order.
     *
     * @param  Collection<int, \App\Models\Cart>  $items
     * @param  array<int, array<int>>  $packageCartGroups  Each inner array is cart IDs for one package (full cart lines).
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
        ?string $customerNotes,
        array $packageCartGroups
    ): Order {
        if (count($packageCartGroups) < 1) {
            throw new \Exception('At least one delivery package is required.');
        }

        $flat = collect($packageCartGroups)->flatten()->sort()->values()->all();
        $expectedIds = $items->pluck('id')->sort()->values()->all();
        if ($flat !== $expectedIds) {
            throw new \Exception('Each cart line must belong to exactly one delivery package.');
        }

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

        $cartById = $items->keyBy('id');
        $cartIdToOrderItem = [];

        // Create order items and reduce each product's stock by the ordered quantity
        foreach ($items as $item) {
            $orderItem = $this->createOrderItem($order, $item);
            $cartIdToOrderItem[$item->id] = $orderItem;
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

        $pkgCount = max(1, count($packageCartGroups));
        $feeShares = $this->splitPlatformFeeShares((float) $platformFee, $pkgCount);

        foreach ($packageCartGroups as $idx => $cartIdsInPkg) {
            $pkg = OrderPackage::create([
                'order_id' => $order->id,
                'sequence' => $idx + 1,
                'delivery_status' => DeliveryService::STATUS_PENDING_ASSIGNMENT,
                'platform_fee_share' => $feeShares[$idx] ?? 0,
            ]);

            foreach ($cartIdsInPkg as $cartId) {
                $cartItem = $cartById->get($cartId);
                if (! $cartItem || ! isset($cartIdToOrderItem[$cartId])) {
                    throw new \Exception('Invalid package grouping for checkout.');
                }
                $orderItem = $cartIdToOrderItem[$cartId];
                OrderPackageItem::create([
                    'order_package_id' => $pkg->id,
                    'order_item_id' => $orderItem->id,
                    'quantity' => $orderItem->quantity,
                ]);
            }
        }

        // Create payment record (COD only in production flow — verified immediately)
        $verificationStatus = $paymentMethod === 'cod' ? 'verified' : 'awaiting_proof';
        Payment::create([
            'order_id' => $order->id,
            'payment_method' => $paymentMethod,
            'amount' => $order->total,
            'verification_status' => $verificationStatus,
        ]);

        $order->refreshAggregateDeliveryFromPackages();

        return $order->fresh(['items', 'payment', 'artisan', 'packages']);
    }

    /**
     * After the seller approves, assign riders to packages when payment is verified.
     */
    public function assignRidersAfterSellerApproval(Order $order): void
    {
        $order->loadMissing(['packages', 'payment']);

        if (! $order->payment?->isVerified() || ! $order->isSellerApprovedForFulfillment()) {
            return;
        }

        foreach ($order->packages as $pkg) {
            $this->deliveryService->assignRandomAvailableRider($pkg->fresh(['order.payment']));
        }

        $order->fresh()->refreshAggregateDeliveryFromPackages();
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
            'confirmed' => $query->clone()->wherePaymentVerified()->count(),
            'completed' => $query->completed()->count(),
            'cancelled' => $query->where('status', 'cancelled')->count(),
            'total_value' => $query->clone()->wherePaymentVerified()->sum('total'),
            'monthly_value' => $query->clone()
                ->wherePaymentVerified()
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
            ->where('status', 'pending')
            ->wherePaymentVerified()
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