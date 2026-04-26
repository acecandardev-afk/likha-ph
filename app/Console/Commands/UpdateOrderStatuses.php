<?php

namespace App\Console\Commands;

use App\Models\Order;
use App\Services\DeliveryService;
use Illuminate\Console\Command;

class UpdateOrderStatuses extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'orders:update-statuses {--dry-run : Show what would be updated without making changes}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Assign pending deliveries to available riders';

    public function __construct(
        protected DeliveryService $deliveryService
    ) {
        parent::__construct();
    }

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $dryRun = $this->option('dry-run');
        $this->info($dryRun ? 'DRY RUN: Simulating delivery assignment checks' : 'Checking pending delivery assignments...');

        // Keep only assignment automation. Delivery progress itself is rider/admin-driven.
        $pendingCount = Order::where('delivery_status', DeliveryService::STATUS_PENDING_ASSIGNMENT)
            ->whereHas('payment', fn ($q) => $q->where('verification_status', 'verified'))
            ->count();

        $this->info("Found {$pendingCount} orders pending rider assignment");

        Order::where('delivery_status', DeliveryService::STATUS_PENDING_ASSIGNMENT)
            ->whereHas('payment', fn ($q) => $q->where('verification_status', 'verified'))
            ->chunkById(100, function ($orders) use ($dryRun) {
                foreach ($orders as $order) {
                    /** @var \App\Models\Order $order */
                    if ($dryRun) {
                        $this->line("Would attempt rider assignment for order {$order->order_number}");
                    } else {
                        $this->deliveryService->assignRandomAvailableRider($order);
                    }
                }
            });

        if (!$dryRun) {
            $this->info('Delivery assignment checks completed successfully.');
        } else {
            $this->info('Dry run completed. No changes were made.');
        }

        return \Symfony\Component\Console\Command\Command::SUCCESS;
    }
}
