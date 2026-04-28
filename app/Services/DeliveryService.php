<?php

namespace App\Services;

use App\Models\Order;
use App\Models\OrderDeliveryHistory;
use App\Models\OrderPackage;
use App\Models\Rider;
use App\Models\User;
use Illuminate\Support\Facades\DB;

class DeliveryService
{
    public const MAX_ACTIVE_PACKAGES_PER_RIDER = 5;

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

    public function __construct(
        protected NotificationService $notificationService
    ) {}

    /**
     * Progress rank (low = earliest). Used to pick bottleneck package for order-level display.
     */
    public static function statusRank(): array
    {
        return [
            self::STATUS_PENDING_ASSIGNMENT => 0,
            self::STATUS_ORDER_CONFIRMED => 1,
            self::STATUS_PREPARING_PACKAGE => 2,
            self::STATUS_PACKAGE_PICKED_UP => 3,
            self::STATUS_ARRIVED_SORT_CENTER => 4,
            self::STATUS_OUT_FOR_DELIVERY => 5,
            self::STATUS_DELIVERED => 6,
        ];
    }

    public function activePackageCountForRider(Rider $rider): int
    {
        return OrderPackage::query()
            ->where('rider_id', $rider->id)
            ->where('delivery_status', '!=', self::STATUS_DELIVERED)
            ->count();
    }

    /**
     * Assign a rider to this package if payment allows and rider has capacity (< 5 active packages).
     */
    public function assignRandomAvailableRider(OrderPackage $package): ?Rider
    {
        $package->loadMissing('order.payment');

        if ($package->rider_id) {
            return $package->rider;
        }

        if (! $package->order->payment?->isVerified()) {
            $package->update(['delivery_status' => self::STATUS_PENDING_ASSIGNMENT]);

            return null;
        }

        // Prefer lowest active (non-delivered) package count; first rider under capacity.
        // Use a correlated subquery (SQLite-compatible — avoid withCount+having; SQLite rejects HAVING here).
        $prefix = DB::getTablePrefix();
        $ridersTable = $prefix.(new Rider)->getTable();
        $packagesTable = $prefix.(new OrderPackage)->getTable();
        $delivered = self::STATUS_DELIVERED;
        $cap = self::MAX_ACTIVE_PACKAGES_PER_RIDER;

        $activeSql = '(SELECT COUNT(*) FROM '.$packagesTable.' op WHERE op.rider_id = '.$ridersTable.'.id AND op.delivery_status <> ?)';

        $rider = Rider::query()
            ->whereIn('status', [Rider::STATUS_AVAILABLE, Rider::STATUS_BUSY])
            ->whereRaw($activeSql.' < ?', [$delivered, $cap])
            ->orderByRaw($activeSql.' asc', [$delivered])
            ->orderBy($ridersTable.'.id')
            ->first();

        if (! $rider) {
            $package->update(['delivery_status' => self::STATUS_PENDING_ASSIGNMENT]);

            return null;
        }

        DB::transaction(function () use ($package, $rider) {
            $package->update([
                'rider_id' => $rider->id,
                'delivery_status' => self::STATUS_ORDER_CONFIRMED,
                'delivery_assigned_at' => now(),
            ]);

            $this->logStatus(
                $package->order,
                self::STATUS_ORDER_CONFIRMED,
                auth()->user(),
                'Package #'.$package->sequence.' assigned to rider '.$rider->full_name,
                $package->id
            );

            $this->syncRiderBusyState($rider);
        });

        $package->order->refreshAggregateDeliveryFromPackages();

        $this->notificationService->notifyDeliveryAssigned(
            $package->order->fresh(['customer', 'artisan']),
            $rider->fresh('user'),
            $package->fresh()
        );

        return $rider->fresh();
    }

    public function logStatus(Order $order, string $status, ?User $actor = null, ?string $note = null, ?int $orderPackageId = null): OrderDeliveryHistory
    {
        return OrderDeliveryHistory::create([
            'order_id' => $order->id,
            'order_package_id' => $orderPackageId,
            'status' => $status,
            'updated_by' => $actor?->id,
            'updated_by_role' => $actor?->role,
            'note' => $note,
            'status_at' => now(),
        ]);
    }

    public function updateDeliveryStatus(OrderPackage $package, string $status, ?User $actor = null, ?string $note = null): OrderPackage
    {
        if (! in_array($status, self::TRACKING_STATUSES, true)) {
            throw new \InvalidArgumentException('Invalid delivery status selected.');
        }

        DB::transaction(function () use ($package, $status, $actor, $note) {
            $now = now();
            $data = [
                'delivery_status' => $status,
                'delivery_completed_at' => $status === self::STATUS_DELIVERED ? $now : $package->delivery_completed_at,
            ];

            if ($status === self::STATUS_DELIVERED) {
                $data['platform_fee_realized_at'] = $now;
                if (! $package->isDelivered()) {
                    $data['rider_fee_amount'] = round((float) config('fees.rider_fee_per_package', 0), 2);
                }
            }

            $package->update($data);

            $this->logStatus(
                $package->order,
                $status,
                $actor,
                $note,
                $package->id
            );

            if ($status === self::STATUS_DELIVERED && $package->rider) {
                $this->syncRiderBusyState($package->rider);
            }
        });

        $package->order->refreshAggregateDeliveryFromPackages();

        return $package->fresh(['rider', 'order']);
    }

    protected function syncRiderBusyState(Rider $rider): void
    {
        $active = $this->activePackageCountForRider($rider->fresh());
        if ($active >= self::MAX_ACTIVE_PACKAGES_PER_RIDER) {
            $rider->update(['status' => Rider::STATUS_BUSY]);
        } elseif ($active === 0) {
            $rider->update(['status' => Rider::STATUS_AVAILABLE]);
        } else {
            $rider->update(['status' => Rider::STATUS_BUSY]);
        }
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
