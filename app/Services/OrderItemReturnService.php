<?php

namespace App\Services;

use App\Models\OrderItemReturn;
use App\Models\Product;
use App\Models\User;
use Illuminate\Support\Facades\DB;

class OrderItemReturnService
{
    public function __construct(
        protected NotificationService $notificationService
    ) {}

    public function approve(OrderItemReturn $return, User $admin, ?string $resolutionNotes): OrderItemReturn
    {
        DB::transaction(function () use ($return, $admin, $resolutionNotes) {
            /** @var \App\Models\OrderItemReturn|null $locked */
            $locked = OrderItemReturn::query()
                ->whereKey($return->id)
                ->lockForUpdate()
                ->first();

            if (! $locked || ! $locked->isPendingAdmin()) {
                throw new \InvalidArgumentException('This return is not awaiting admin review, or it was already processed.');
            }

            $locked->loadMissing('orderItem');
            $productId = $locked->orderItem?->product_id;
            if ($productId) {
                Product::query()->whereKey($productId)->increment('stock', $locked->quantity);
            }

            $locked->update([
                'status' => OrderItemReturn::STATUS_APPROVED,
                'admin_resolution_notes' => $resolutionNotes,
                'reviewed_by' => $admin->id,
                'reviewed_at' => now(),
                'stock_restored_at' => now(),
            ]);
        });

        $fresh = $return->fresh(['order', 'orderItem.product', 'customer', 'artisan']);
        $this->notificationService->notifyOrderItemReturnApproved($fresh);

        return $fresh;
    }

    public function reject(OrderItemReturn $return, User $admin, ?string $resolutionNotes): OrderItemReturn
    {
        DB::transaction(function () use ($return, $admin, $resolutionNotes) {
            /** @var \App\Models\OrderItemReturn|null $locked */
            $locked = OrderItemReturn::query()
                ->whereKey($return->id)
                ->lockForUpdate()
                ->first();

            if (! $locked || ! $locked->isPendingAdmin()) {
                throw new \InvalidArgumentException('This return is not awaiting admin review, or it was already processed.');
            }

            $locked->update([
                'status' => OrderItemReturn::STATUS_REJECTED,
                'admin_resolution_notes' => $resolutionNotes,
                'reviewed_by' => $admin->id,
                'reviewed_at' => now(),
            ]);
        });

        $fresh = $return->fresh(['order', 'orderItem.product', 'customer', 'artisan']);
        $this->notificationService->notifyOrderItemReturnRejected($fresh);

        return $fresh;
    }
}
