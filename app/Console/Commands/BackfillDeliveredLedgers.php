<?php

namespace App\Console\Commands;

use App\Models\LedgerJournal;
use App\Models\Order;
use App\Services\LedgerPostingService;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Log;

class BackfillDeliveredLedgers extends Command
{
    protected $signature = 'ledger:backfill {--chunk=100}';

    protected $description = 'Create settlement ledger journals for delivered orders missing a journal (e.g. historical data).';

    public function handle(LedgerPostingService $ledgerPostingService): int
    {
        $query = Order::query()
            ->where('status', 'delivered')
            ->whereDoesntHave('ledgerJournals', fn ($q) => $q->where('kind', LedgerJournal::KIND_DELIVERY_SETTLEMENT))
            ->whereHas('payment', fn ($q) => $q->where('verification_status', 'verified'));

        $total = (clone $query)->count();

        if ($total === 0) {
            $this->info('No delivered orders need a ledger journal.');

            return self::SUCCESS;
        }

        $this->info("Processing {$total} order(s)…");
        $bar = $this->output->createProgressBar($total);
        $bar->start();

        $failed = 0;

        $query->orderBy('id')->chunk((int) $this->option('chunk'), function (\Illuminate\Support\Collection $orders) use ($ledgerPostingService, $bar, &$failed) {
            foreach ($orders as $order) {
                if (! $order instanceof Order) {
                    continue;
                }
                try {
                    $order->load(['packages', 'payment']);
                    $ledgerPostingService->postDeliverySettlementIfNeeded($order);
                } catch (\Throwable $e) {
                    $failed++;
                    Log::warning('ledger_backfill_failed', [
                        'order_id' => $order->id,
                        'message' => $e->getMessage(),
                    ]);
                }
                $bar->advance();
            }
        });

        $bar->finish();
        $this->newLine();

        if ($failed > 0) {
            $this->warn("Completed with {$failed} skipped (logged).");

            return self::SUCCESS;
        }

        $this->info('Done.');

        return self::SUCCESS;
    }
}
