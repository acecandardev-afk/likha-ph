<?php

namespace App\Services;

use App\Models\Order;
use App\Models\OrderItem;
use App\Models\OrderPackage;
use App\Models\OrderPackageItem;
use App\Models\Payment;
use App\Models\User;
use App\Models\Voucher;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;

class OrderService
{
    public const PLATFORM_FEE_RATE = 0.05;

    public function __construct(
        protected StockService $stockService,
        protected CartService $cartService,
        protected NotificationService $notificationService,
        protected DeliveryService $deliveryService,
        protected VoucherService $voucherService
    ) {}

    /**
     * Create orders from cart (multi-artisan checkout). Online storefront only supports pay on delivery.
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
        ?array $packageSplit = null,
        ?string $voucherCode = null
    ): Collection {
        $normalizedPayment = strtolower(trim($paymentMethod));
        if ($normalizedPayment !== 'cod') {
            throw new \InvalidArgumentException('online_checkout_cod_only');
        }

        $validationErrors = $this->cartService->validateCart($customer);
        if (! empty($validationErrors)) {
            throw new \Exception(implode(', ', $validationErrors));
        }

        $cartItems = $this->cartService->getCartItems($customer);

        if ($cartItems->isEmpty()) {
            throw new \Exception('Cart is empty.');
        }

        $cartSubtotal = round((float) $cartItems->sum(fn ($item) => $item->product->price * $item->quantity), 2);
        $voucherResolution = $this->voucherService->resolve($voucherCode, $cartSubtotal);
        if ($voucherResolution['error'] !== null) {
            throw new \InvalidArgumentException('voucher_invalid');
        }

        /** @var Voucher|null $appliedVoucher */
        $appliedVoucher = $voucherResolution['voucher'];
        $cartDiscount = (float) $voucherResolution['discount'];

        $groupedByArtisan = $cartItems->groupBy('product.artisan_id');
        $artisanIds = $groupedByArtisan->keys()->values()->all();
        $allocatedDiscounts = $this->allocateDiscountAcrossArtisans($groupedByArtisan, $cartSubtotal, $cartDiscount);

        $orders = collect();

        DB::transaction(function () use (
            $customer,
            $appliedVoucher,
            $groupedByArtisan,
            $allocatedDiscounts,
            $packageSplit,
            $country,
            $region,
            $province,
            $city,
            $barangay,
            $streetAddress,
            $phone,
            $customerNotes,
            $normalizedPayment,
            &$orders,
            $artisanIds,
        ) {
            foreach ($artisanIds as $artisanId) {
                /** @var int|string $artisanId */
                $items = $groupedByArtisan->get($artisanId);
                $discountForSlice = $allocatedDiscounts[(int) $artisanId] ?? 0.0;
                $groups = $this->buildCartGroupsForPackages($items, $packageSplit[(int) $artisanId] ?? []);

                $order = $this->createSingleOrder(
                    $customer,
                    (int) $artisanId,
                    $items,
                    $normalizedPayment,
                    $country,
                    $region,
                    $province,
                    $city,
                    $barangay,
                    $streetAddress,
                    $phone,
                    $customerNotes,
                    $groups,
                    $discountForSlice,
                    $appliedVoucher
                );

                $orders->push($order);
            }

            if ($appliedVoucher) {
                $this->voucherService->incrementRedemption($appliedVoucher->fresh());
            }

            $this->cartService->clearCart($customer);
        });

        foreach ($orders as $order) {
            $this->notificationService->notifyOrderCreated($order);
        }

        if ($appliedVoucher !== null && $orders->isNotEmpty()) {
            $this->notificationService->notifyPromoAppliedForAdmins($appliedVoucher->fresh(), $orders, $customer);
        }

        return $orders;
    }

    /**
     * Live preview for checkout page (same math as checkout, no side effects).
     *
     * @return array{
     *   subtotal: float,
     *   discount: float,
     *   merchandise_after_discount: float,
     *   service_fee_total: float,
     *   delivery_total: float,
     *   taxes_total: float,
     *   grand_total: float,
     *   seller_count: int,
     *   voucher_error: string|null,
     *   voucher_label: string|null
     * }
     */
    public function previewCheckoutTotals(User $customer, ?string $voucherCode): array
    {
        $cartItems = $this->cartService->getCartItems($customer);
        if ($cartItems->isEmpty()) {
            return [
                'subtotal' => 0.0,
                'discount' => 0.0,
                'merchandise_after_discount' => 0.0,
                'service_fee_total' => 0.0,
                'delivery_total' => 0.0,
                'taxes_total' => 0.0,
                'grand_total' => 0.0,
                'seller_count' => 0,
                'voucher_error' => null,
                'voucher_label' => null,
            ];
        }

        $cartSubtotal = round((float) $cartItems->sum(fn ($item) => $item->product->price * $item->quantity), 2);
        $resolution = $this->voucherService->resolve($voucherCode, $cartSubtotal);

        $discount = 0.0;
        $voucherLabel = null;
        if ($resolution['voucher']) {
            $discount = $resolution['discount'];
            $voucherLabel = $resolution['voucher']->label ?? $resolution['voucher']->code;
        }

        $grouped = $cartItems->groupBy('product.artisan_id');
        $allocated = $this->allocateDiscountAcrossArtisans($grouped, $cartSubtotal, $discount);

        $serviceFeeTotal = 0.0;
        $deliveryTotal = 0.0;
        $taxTotal = 0.0;
        $grandTotal = 0.0;

        foreach ($grouped as $artisanId => $items) {
            $sliceSubtotal = round((float) $items->sum(fn ($item) => $item->product->price * $item->quantity), 2);
            $sliceDiscount = $allocated[(int) $artisanId] ?? 0.0;
            $slice = $this->computeFinancialsForSlice($sliceSubtotal, $sliceDiscount);
            $serviceFeeTotal += $slice['platform_fee'];
            $deliveryTotal += $slice['shipping_amount'];
            $taxTotal += $slice['tax_amount'];
            $grandTotal += $slice['total'];
        }

        return [
            'subtotal' => $cartSubtotal,
            'discount' => round($discount, 2),
            'merchandise_after_discount' => round($cartSubtotal - $discount, 2),
            'service_fee_total' => round($serviceFeeTotal, 2),
            'delivery_total' => round($deliveryTotal, 2),
            'taxes_total' => round($taxTotal, 2),
            'grand_total' => round($grandTotal, 2),
            'seller_count' => $grouped->count(),
            'voucher_error' => $resolution['error'],
            'voucher_label' => $voucherLabel,
        ];
    }

    /**
     * @param  \Illuminate\Support\Collection<int|string, Collection<int,\App\Models\Cart>>  $groupedByArtisan
     * @return array<int,float>
     */
    protected function allocateDiscountAcrossArtisans(Collection $groupedByArtisan, float $cartSubtotal, float $cartDiscount): array
    {
        $cartDiscount = round($cartDiscount, 2);
        if ($cartDiscount <= 0 || $cartSubtotal <= 0) {
            return [];
        }

        $ids = $groupedByArtisan->keys()->values()->all();
        $remaining = $cartDiscount;
        $out = [];

        foreach ($ids as $idx => $aid) {
            /** @var int|string $aid */
            $items = $groupedByArtisan->get($aid);
            $slice = round((float) $items->sum(fn ($item) => $item->product->price * $item->quantity), 2);

            if ($idx === count($ids) - 1) {
                $portion = round($remaining, 2);
            } else {
                $portion = round($cartDiscount * ($slice / $cartSubtotal), 2);
                $remaining -= $portion;
            }

            $out[(int) $aid] = $portion;
        }

        return $out;
    }

    /**
     * @param  Collection<int, \App\Models\Cart>  $items
     * @param  array<int, int>  $splitForArtisan  cart_id => package number (1-based)
     * @return array<int, array<int>>
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
     * @param  Collection<int, \App\Models\Cart>  $items
     * @param  array<int, array<int>>  $packageCartGroups
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
        array $packageCartGroups,
        float $allocatedDiscount,
        ?Voucher $voucher
    ): Order {
        if (count($packageCartGroups) < 1) {
            throw new \Exception('At least one delivery package is required.');
        }

        $flat = collect($packageCartGroups)->flatten()->sort()->values()->all();
        $expectedIds = $items->pluck('id')->sort()->values()->all();
        if ($flat !== $expectedIds) {
            throw new \Exception('Each cart line must belong to exactly one delivery package.');
        }

        $subtotal = round((float) $items->sum(fn ($item) => $item->product->price * $item->quantity), 2);
        $allocatedDiscount = round($allocatedDiscount, 2);
        if ($allocatedDiscount > $subtotal) {
            $allocatedDiscount = $subtotal;
        }

        $pkgCount = max(1, count($packageCartGroups));
        $financials = $this->computeFinancialsForSlice($subtotal, $allocatedDiscount);

        foreach ($items as $item) {
            if (! $this->stockService->hasStock($item->product, $item->quantity)) {
                throw new \Exception("Insufficient stock for '{$item->product->name}'");
            }
        }

        $order = Order::create([
            'customer_id' => $customer->id,
            'artisan_id' => $artisanId,
            'subtotal' => $subtotal,
            'platform_fee' => $financials['platform_fee'],
            'shipping_amount' => $financials['shipping_amount'],
            'tax_amount' => $financials['tax_amount'],
            'discount_amount' => $financials['discount_amount'],
            'voucher_code' => $voucher?->code,
            'total' => $financials['total'],
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

        $feeShares = $this->splitPlatformFeeShares((float) $order->platform_fee, $pkgCount);

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
     * One delivery fee and tax calculation per seller order (multiple packages share one fee).
     *
     * @return array{
     *   discount_amount: float,
     *   merchandise_net: float,
     *   platform_fee: float,
     *   shipping_amount: float,
     *   tax_amount: float,
     *   total: float
     * }
     */
    protected function computeFinancialsForSlice(float $sliceSubtotal, float $discountAmount): array
    {
        $discountAmount = round(min($discountAmount, $sliceSubtotal), 2);
        $merchandiseNet = round($sliceSubtotal - $discountAmount, 2);

        $platformFeeRate = (float) config('fees.platform_fee_rate', self::PLATFORM_FEE_RATE);
        $platformFee = round($merchandiseNet * $platformFeeRate, 2);

        $shippingEach = max(0.0, (float) config('commerce.shipping_flat_per_order', 0));
        $shippingTotal = round($shippingEach, 2);

        $taxRate = max(0.0, (float) config('commerce.tax_rate', 0));
        $taxBase = $merchandiseNet + $shippingTotal;
        $taxAmount = $taxRate > 0 ? round($taxBase * $taxRate, 2) : 0.0;

        $total = round($merchandiseNet + $platformFee + $shippingTotal + $taxAmount, 2);

        return [
            'discount_amount' => $discountAmount,
            'merchandise_net' => $merchandiseNet,
            'platform_fee' => $platformFee,
            'shipping_amount' => $shippingTotal,
            'tax_amount' => $taxAmount,
            'total' => $total,
        ];
    }

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

    private function createOrderItem(Order $order, $cartItem): OrderItem
    {
        $product = $cartItem->product;
        $line = $product->price * $cartItem->quantity;

        return OrderItem::create([
            'order_id' => $order->id,
            'product_id' => $product->id,
            'product_name' => $product->name,
            'product_description' => $product->description,
            'price' => $product->price,
            'quantity' => $cartItem->quantity,
            'subtotal' => $line,
        ]);
    }

    public function cancelOrder(Order $order): Order
    {
        $isAdmin = auth()->user()?->isAdmin() ?? false;
        if (! $isAdmin && ! $order->canBeCancelled()) {
            throw new \Exception('Order cannot be cancelled at this time.');
        }

        DB::transaction(function () use ($order) {
            $this->stockService->restoreStockFromOrder($order->items);

            $order->update(['status' => 'cancelled']);
        });

        $this->notificationService->notifyOrderCancelled($order);

        return $order->fresh();
    }

    public function completeOrder(Order $order): Order
    {
        if (! $order->canBeCompleted()) {
            throw new \Exception('Order cannot be completed at this time.');
        }

        $order->update(['status' => 'completed']);

        $this->notificationService->notifyOrderCompleted($order);

        return $order->fresh();
    }

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
     * @param  Collection<int, \App\Models\Cart>|Collection<int,\App\Models\OrderItem>  $items
     */
    public function calculateOrderTotals(Collection $items, ?string $voucherCode = null): array
    {
        $subtotal = $items->sum(function ($item) {
            return $item->product->price * $item->quantity;
        });

        $subtotal = round((float) $subtotal, 2);
        $resolution = app(VoucherService::class)->resolve($voucherCode, $subtotal);
        $discount = $resolution['voucher'] ? $resolution['discount'] : 0.0;

        $financials = $this->computeFinancialsForSlice($subtotal, $discount);

        return [
            'subtotal' => $subtotal,
            'discount' => $financials['discount_amount'],
            'shipping' => $financials['shipping_amount'],
            'tax' => $financials['tax_amount'],
            'platform_fee' => $financials['platform_fee'],
            'total' => $financials['total'],
        ];
    }
}
