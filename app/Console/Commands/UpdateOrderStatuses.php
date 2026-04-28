<?php

namespace App\Console\Commands;

use App\Models\OrderPackage;
use App\Services\DeliveryService;
use Illuminate\Console\Command;

class UpdateOrderStatuses extends Command
{
    protected $signature = 'orders:update-statuses {--dry-run : Show what would be updated without making changes}';

    protected $description = 'Assign pending delivery packages to available riders';

    public function __construct(
        protected DeliveryService $deliveryService
    ) {
        parent::__construct();
    }

    public function handle()
    {
        $dryRun = $this->option('dry-run');
        $this->info($dryRun ? 'DRY RUN: Simulating delivery assignment checks' : 'Checking pending delivery assignments...');

        $pendingCount = OrderPackage::query()
            ->where('delivery_status', DeliveryService::STATUS_PENDING_ASSIGNMENT)
            ->whereHas('order.payment', fn ($q) => $q->where('verification_status', 'verified'))
            ->whereHas('order', fn ($q) => $q->whereIn('status', ['approved', 'shipped']))
            ->count();

        $this->info("Found {$pendingCount} packages pending rider assignment");

        OrderPackage::query()
            ->where('delivery_status', DeliveryService::STATUS_PENDING_ASSIGNMENT)
            ->whereHas('order.payment', fn ($q) => $q->where('verification_status', 'verified'))
            ->whereHas('order', fn ($q) => $q->whereIn('status', ['approved', 'shipped']))
            ->orderBy('id')
            ->chunkById(100, function ($packages) use ($dryRun) {
                foreach ($packages as $package) {
                    /** @var \App\Models\OrderPackage $package */
                    if ($dryRun) {
                        $num = $package->order?->order_number ?? $package->order_id;
                        $this->line("Would attempt rider assignment for order {$num} package #{$package->sequence}");
                    } else {
                        $this->deliveryService->assignRandomAvailableRider($package->fresh(['order.payment']));
                    }
                }
            });

        if (! $dryRun) {
            $this->info('Delivery assignment checks completed successfully.');
        } else {
            $this->info('Dry run completed. No changes were made.');
        }

        return self::SUCCESS;
    }
}
