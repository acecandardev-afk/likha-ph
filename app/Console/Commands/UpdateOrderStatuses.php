<?php

namespace App\Console\Commands;

use App\Models\Order;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Log;

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
    protected $description = 'Automatically update order statuses based on time elapsed';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $dryRun = $this->option('dry-run');
        $now = now();

        $this->info($dryRun ? 'DRY RUN: Simulating order status updates' : 'Updating order statuses...');

        // Update pending orders to shipped after 1 hour
        $pendingOrders = Order::where('status', 'pending')
            ->where('created_at', '<=', $now->copy()->subHour())
            ->get();

        $this->info("Found {$pendingOrders->count()} pending orders older than 1 hour");

        foreach ($pendingOrders as $order) {
            if ($dryRun) {
                $this->line("Would update order {$order->order_number} from pending to shipped");
            } else {
                $order->update(['status' => 'shipped']);
                Log::info("Order {$order->order_number} status updated to shipped");
            }
        }

        // Update shipped orders to on_delivery after 1-5 hours total (4 more hours)
        $shippedOrders = Order::where('status', 'shipped')
            ->where('created_at', '<=', $now->copy()->subHours(5))
            ->get();

        $this->info("Found {$shippedOrders->count()} shipped orders older than 5 hours");

        foreach ($shippedOrders as $order) {
            if ($dryRun) {
                $this->line("Would update order {$order->order_number} from shipped to on_delivery");
            } else {
                $order->update(['status' => 'on_delivery']);
                Log::info("Order {$order->order_number} status updated to on_delivery");
            }
        }

        if (!$dryRun) {
            $this->info('Order status updates completed successfully.');
        } else {
            $this->info('Dry run completed. No changes were made.');
        }

        return Command::SUCCESS;
    }
}
