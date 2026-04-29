<?php

namespace App\Services;

use App\Models\DirectMessage;
use App\Models\Message;
use App\Models\Order;
use App\Models\OrderFinancialDispute;
use App\Models\OrderPackage;
use App\Models\Payment;
use App\Models\Product;
use App\Models\Rider;
use App\Models\User;
use App\Models\UserNotification;
use App\Models\Voucher;
use App\Support\ProductNotificationUrl;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Str;

class NotificationService
{
    public function notifyOrderCreated(Order $order): void
    {
        $order->loadMissing(['customer', 'artisan']);

        $num = $order->order_number;
        $total = number_format((float) $order->total, 2);
        $discount = (float) ($order->discount_amount ?? 0);
        $code = $order->voucher_code ? (string) $order->voucher_code : null;
        $promoCustomer = '';
        $promoArtisan = '';
        if ($discount > 0.009 && $code) {
            $da = number_format($discount, 2);
            $promoCustomer = " Promo {$code} saved ₱{$da} on merchandise.";
            $merchShare = number_format(max(0, (float) $order->subtotal - $discount - (float) $order->platform_fee), 2);
            $promoArtisan = " Promo {$code} applied (₱{$da} off merchandise). Estimated merchandise share after promo & service fee: ₱{$merchShare}.";
        }

        $this->notifyUser(
            $order->customer_id,
            'order_created_customer',
            'Order placed',
            "Order {$num} was placed successfully. Total: ₱{$total}.{$promoCustomer}",
            route('customer.orders.show', $order)
        );

        $this->notifyUser(
            $order->artisan_id,
            'order_created_artisan',
            'New order received',
            "You have a new order {$num} (₱{$total}).{$promoArtisan}",
            route('artisan.orders.show', $order)
        );

        Log::info('Order created (in-app notifications sent)', ['order_id' => $order->id]);
    }

    /**
     * Inform admins once per checkout when a promo voucher was applied (possibly multi-order cart).
     *
     * @param  Collection<int, Order>  $orders
     */
    public function notifyPromoAppliedForAdmins(Voucher $voucher, Collection $orders, User $customer): void
    {
        $nums = $orders->pluck('order_number')->filter()->implode(', ');
        $discountSum = round((float) $orders->sum(fn ($o) => (float) ($o->discount_amount ?? 0)), 2);
        $discStr = number_format($discountSum, 2);

        $this->notifyAdmins(
            'checkout_promo_applied',
            'Promo code used at checkout',
            "Customer {$customer->name} applied promo {$voucher->code}. Order(s): {$nums}. Total merchandise discount allocated: ₱{$discStr}.",
            route('admin.vouchers.index')
        );
    }

    /**
     * Notify admins when a voucher definition is created or materially updated (audit visibility).
     */
    public function notifyVoucherManagedByAdmin(User $admin, string $action, string $code): void
    {
        $this->notifyAdmins(
            'voucher_managed',
            'Promo voucher '.$action,
            "Admin {$admin->name} {$action} voucher {$code}.",
            route('admin.vouchers.index')
        );
    }

    public function notifyOrderCancelled(Order $order): void
    {
        $order->loadMissing(['customer', 'artisan']);
        $num = $order->order_number;

        $this->notifyUser(
            $order->customer_id,
            'order_cancelled_customer',
            'Order cancelled',
            "Order {$num} has been cancelled.",
            route('customer.orders.show', $order)
        );

        $this->notifyUser(
            $order->artisan_id,
            'order_cancelled_artisan',
            'Order cancelled',
            "Order {$num} was cancelled by the customer.",
            route('artisan.orders.show', $order)
        );
    }

    public function notifyOrderCompleted(Order $order): void
    {
        $order->loadMissing(['customer', 'artisan']);
        $num = $order->order_number;

        $this->notifyUser(
            $order->customer_id,
            'order_completed_customer',
            'Order completed',
            "Order {$num} is complete. You can leave a review for your items.",
            route('customer.orders.show', $order)
        );

        $this->notifyUser(
            $order->artisan_id,
            'order_completed_artisan',
            'Order completed',
            "Order {$num} has been marked as completed.",
            route('artisan.orders.show', $order)
        );
    }

    public function notifyPaymentProofUploaded(Payment $payment): void
    {
        $payment->loadMissing('order');
        $order = $payment->order;
        $num = $order->order_number;

        $this->notifyAdmins(
            'payment_proof_uploaded',
            'Payment proof uploaded',
            "Customer uploaded proof for order {$num}.",
            route('admin.payments.review', $payment)
        );
    }

    public function notifyPaymentVerified(Payment $payment): void
    {
        $payment->loadMissing('order.customer', 'order.artisan');
        $order = $payment->order;
        $num = $order->order_number;

        $this->notifyUser(
            $order->customer_id,
            'payment_verified_customer',
            'Payment verified',
            "Your payment for order {$num} was verified. The artisan will prepare your order.",
            route('customer.orders.show', $order)
        );

        $this->notifyUser(
            $order->artisan_id,
            'payment_verified_artisan',
            'Payment verified',
            "Payment for order {$num} was verified.",
            route('artisan.orders.show', $order)
        );
    }

    public function notifyPaymentRejected(Payment $payment): void
    {
        $payment->loadMissing('order.customer', 'order.artisan');
        $order = $payment->order;
        $num = $order->order_number;
        $reason = $payment->verification_notes ? ' Reason: '.$payment->verification_notes : '';

        $this->notifyUser(
            $order->customer_id,
            'payment_rejected_customer',
            'Payment not accepted',
            "Your payment for order {$num} could not be verified.{$reason}",
            route('customer.orders.show', $order)
        );

        $this->notifyUser(
            $order->artisan_id,
            'payment_rejected_artisan',
            'Order cancelled (payment)',
            "Order {$num} was cancelled after payment verification failed.",
            route('artisan.orders.show', $order)
        );
    }

    public function notifyProductApproved(Product $product): void
    {
        $product->loadMissing('artisan');

        $this->notifyUser(
            $product->artisan_id,
            'product_approved',
            'Product approved',
            "'{$product->name}' is approved and visible in the shop.",
            route('artisan.products.show', $product)
        );
    }

    public function notifyProductRejected(Product $product): void
    {
        $product->loadMissing('artisan');
        $reason = $product->rejection_reason ? ' '.$product->rejection_reason : '';

        $this->notifyUser(
            $product->artisan_id,
            'product_rejected',
            'Product needs changes',
            "'{$product->name}' was not approved.{$reason}",
            route('artisan.products.show', $product)
        );
    }

    public function notifyLowStock(Product $product): void
    {
        $product->loadMissing('artisan');

        $this->notifyUser(
            $product->artisan_id,
            'product_low_stock',
            'Low stock alert',
            "'{$product->name}' is running low ({$product->stock} left).",
            route('artisan.products.edit', $product)
        );
    }

    public function notifyOutOfStock(Product $product): void
    {
        $product->loadMissing('artisan');

        $this->notifyUser(
            $product->artisan_id,
            'product_out_of_stock',
            'Out of stock',
            "'{$product->name}' is now out of stock.",
            route('artisan.products.edit', $product)
        );
    }

    public function notifyOrderThreadMessage(Order $order, Message $message): void
    {
        $order->loadMissing('customer', 'artisan');
        $message->loadMissing('sender');

        $recipientId = (int) $message->sender_id === (int) $order->customer_id
            ? $order->artisan_id
            : $order->customer_id;

        $senderName = $message->sender->name ?? 'Someone';
        $num = $order->order_number;

        $this->notifyUser(
            $recipientId,
            'order_message',
            'New message on order '.$num,
            "{$senderName}: ".Str::limit((string) $message->message, 120),
            route('messages.index', $order)
        );
    }

    public function notifyDirectMessageReceived(User $recipient, User $sender, DirectMessage $message): void
    {
        $senderName = $sender->name;

        $this->notifyUser(
            $recipient->id,
            'direct_message',
            'New message from '.$senderName,
            Str::limit((string) $message->message, 120),
            route('chat.index', $sender)
        );
    }

    public function notifyDeliveryAssigned(Order $order, Rider $rider, OrderPackage $package): void
    {
        $order->loadMissing(['customer', 'artisan']);
        $num = $order->order_number;
        $seq = $package->sequence;
        $riderName = $rider->full_name ?: 'Assigned rider';

        $this->notifyAdmins(
            'delivery_assigned_admin',
            'Rider assigned automatically',
            "Order {$num} (package #{$seq}) was assigned to {$riderName}.",
            route('admin.deliveries.index')
        );

        $this->notifyUser(
            $order->customer_id,
            'delivery_assigned_customer',
            'Rider assigned to your order',
            "Order {$num}, package #{$seq}, is assigned to {$riderName}.",
            route('customer.orders.tracking', $order)
        );

        $this->notifyUser(
            $order->artisan_id,
            'delivery_assigned_artisan',
            'Rider assigned',
            "Order {$num}, package #{$seq}, is assigned to {$riderName}.",
            route('artisan.orders.show', $order)
        );

        if ($rider->user_id) {
            $this->notifyUser(
                (int) $rider->user_id,
                'delivery_assigned_rider',
                'New delivery assignment',
                "New assignment: order {$num}, package #{$seq}.",
                route('rider.deliveries.show', $package)
            );
        }
    }

    /**
     * Buyer/seller clarity when a package is marked delivered (COD wording when multiple packages exist).
     */
    public function notifyOrderPackageDelivered(OrderPackage $package): void
    {
        $package->loadMissing(['order.customer', 'order.artisan', 'order.payment', 'order.packages']);
        $order = $package->order;
        $num = $order->order_number;
        $seq = $package->sequence;
        $pkgCount = $order->packages->count();
        $method = strtolower((string) ($order->payment?->payment_method ?? ''));

        $codSentence = '';
        if ($method === 'cod') {
            $codSentence = $pkgCount > 1
                ? ' Pay cash on delivery using your full order total when your rider completes each drop — amounts match your receipt across packages.'
                : ' Pay cash on delivery when your rider arrives (same total as your order summary).';
        }

        $this->notifyUser(
            (int) $order->customer_id,
            'delivery_package_delivered_customer',
            "Package #{$seq} delivered",
            "Package #{$seq} for order {$num} was marked delivered.{$codSentence}",
            route('customer.orders.tracking', $order)
        );

        if ($order->artisan_id) {
            $ledgerHint = '';
            $order->loadMissing('deliverySettlementJournal');
            if ($order->deliverySettlementJournal) {
                $ledgerHint = ' Settlement journal posted for this order — check After delivery / Settlement ledger.';
            } elseif ($pkgCount > 1) {
                $ledgerHint = ' If this order has more packages still moving, ledger settlement posts after every package is delivered.';
            }

            $this->notifyUser(
                (int) $order->artisan_id,
                'delivery_package_delivered_artisan',
                "Package #{$seq} delivered",
                "Package #{$seq} for order {$num} was marked delivered.{$ledgerHint}",
                route('artisan.orders.show', $order)
            );
        }
    }

    public function notifyFinancialDisputeOpened(OrderFinancialDispute $dispute): void
    {
        $dispute->loadMissing('order');
        $num = $dispute->order?->order_number ?? 'Order';

        $this->notifyAdmins(
            'financial_dispute_opened',
            'Financial / COD concern opened',
            "Dispute on {$num}: {$dispute->category}. Review in Admin.",
            route('admin.financial-disputes.index')
        );
    }

    protected function notifyUser(int $userId, string $type, string $title, ?string $body, ?string $actionUrl = null): void
    {
        UserNotification::create([
            'user_id' => $userId,
            'type' => $type,
            'title' => $title,
            'body' => $body,
            'action_url' => $actionUrl,
            'is_read' => false,
        ]);
    }

    protected function notifyAdmins(string $type, string $title, ?string $body, ?string $actionUrl = null): void
    {
        $adminIds = User::query()
            ->where('role', 'admin')
            ->where('status', 'active')
            ->pluck('id');

        foreach ($adminIds as $id) {
            $this->notifyUser((int) $id, $type, $title, $body, $actionUrl);
        }
    }

    /**
     * Remove in-app notifications whose action URL points at a deleted product (avoids 404s from stale links).
     */
    public function removeNotificationsForDeletedProduct(int $productId): void
    {
        if ($productId < 1) {
            return;
        }

        $likeProducts = '%/products/'.$productId.'%';
        $likeArtisan = '%/artisan/products/'.$productId.'%';

        $affectedUserIds = [];

        $candidates = UserNotification::query()
            ->whereNotNull('action_url')
            ->where(function ($q) use ($likeProducts, $likeArtisan) {
                $q->where('action_url', 'like', $likeProducts)
                    ->orWhere('action_url', 'like', $likeArtisan);
            })
            ->get();

        foreach ($candidates as $notification) {
            if (! ProductNotificationUrl::referencesProductId($notification->action_url, $productId)) {
                continue;
            }
            $affectedUserIds[] = (int) $notification->user_id;
            $notification->delete();
        }

        foreach (array_unique($affectedUserIds) as $userId) {
            $this->forgetNotificationUiCacheFor((int) $userId);
        }
    }

    protected function forgetNotificationUiCacheFor(int $userId): void
    {
        Cache::forget("ui:unreadNotificationsCount:{$userId}");
        Cache::forget("ui:applicationBanner:{$userId}");
    }
}
