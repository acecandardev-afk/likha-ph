<?php

namespace App\Services;

use App\Models\Order;
use App\Models\OrderDeliveryHistory;
use App\Models\Rider;
use App\Models\User;
use Illuminate\Support\Facades\DB;

class DeliveryService
{
    public function __construct(
        protected NotificationService $notificationService
    ) {}

    public const STATUS_PENDING_ASSIGNMENT = 'pending_assignment';
    public const STATUS_ORDER_CONFIRMED = 'order_confirmed';
    public const STATUS_PREPARING_PACKAGE = 'preparing_package';
    public const STATUS_PACKAGE_PICKED_UP = 'package_picked_up';
    public const STATUS_ARRIVED_SORT_CENTER = 'arrived_sort_center';
    public const STATUS_OUT_FOR_DELIVERY = 'out_for_delivery';
    public const STATUS_DELIVERED = 'delivered';

    public const TRACKING_STATUSES = [
        self::STATUS_ORDER_CONFIRMED,
        self::STATUS_PREPARING_PACKAGE,
        self::STATUS_PACKAGE_PICKED_UP,
        self::STATUS_ARRIVED_SORT_CENTER,
        self::STATUS_OUT_FOR_DELIVERY,
        self::STATUS_DELIVERED,
    ];

    /**
     * Assign a random available rider to order.
     */
    public function assignRandomAvailableRider(Order $order): ?Rider
    {
        if ($order->rider_id) {
            return $order->rider;
        }

        $rider = Rider::available()->inRandomOrder()->first();

        if (! $rider) {
            $order->update(['delivery_status' => self::STATUS_PENDING_ASSIGNMENT]);
            return null;
        }

        DB::transaction(function () use ($order, $rider) {
            $order->update([
                'rider_id' => $rider->id,
                'delivery_status' => self::STATUS_ORDER_CONFIRMED,
                'delivery_assigned_at' => now(),
            ]);

            $rider->update(['status' => Rider::STATUS_BUSY]);

            $this->logStatus($order, self::STATUS_ORDER_CONFIRMED, auth()->user(), 'Order assigned to rider '.$rider->full_name);
        });

        $this->notificationService->notifyDeliveryAssigned($order->fresh(['customer', 'artisan', 'rider.user']), $rider->fresh('user'));

        return $rider->fresh();
    }

    public function logStatus(Order $order, string $status, ?User $actor = null, ?string $note = null): OrderDeliveryHistory
    {
        return OrderDeliveryHistory::create([
            'order_id' => $order->id,
            'status' => $status,
            'updated_by' => $actor?->id,
            'updated_by_role' => $actor?->role,
            'note' => $note,
            'status_at' => now(),
        ]);
    }

    public function updateDeliveryStatus(Order $order, string $status, ?User $actor = null, ?string $note = null): Order
    {
        if (! in_array($status, self::TRACKING_STATUSES, true)) {
            throw new \InvalidArgumentException('Invalid delivery status selected.');
        }

        DB::transaction(function () use ($order, $status, $actor, $note) {
            $order->update([
                'delivery_status' => $status,
                'delivery_completed_at' => $status === self::STATUS_DELIVERED ? now() : $order->delivery_completed_at,
                'status' => $status === self::STATUS_DELIVERED ? 'delivered' : $order->status,
            ]);

            $this->logStatus($order, $status, $actor, $note);

            if ($status === self::STATUS_DELIVERED && $order->rider) {
                $order->rider->update(['status' => Rider::STATUS_AVAILABLE]);
            }
        });

        return $order->fresh(['rider', 'deliveryHistory.actor']);
    }

    public function deliveryStatusOptions(): array
    {
        return [
            self::STATUS_ORDER_CONFIRMED => 'Order Confirmed',
            self::STATUS_PREPARING_PACKAGE => 'Preparing Package',
            self::STATUS_PACKAGE_PICKED_UP => 'Package Picked Up',
            self::STATUS_ARRIVED_SORT_CENTER => 'Package Arrived at Sort Center',
            self::STATUS_OUT_FOR_DELIVERY => 'Out for Delivery',
            self::STATUS_DELIVERED => 'Delivered',
        ];
    }
}
