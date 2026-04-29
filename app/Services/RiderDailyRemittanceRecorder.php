<?php

namespace App\Services;

use App\Models\Order;
use App\Models\OrderPackage;
use App\Models\RiderRemittanceReport;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;

/**
 * Accumulates per-stop COD splits into rider_remittance_reports by calendar day (office-facing totals).
 * Derived from the same allocation rules as RiderSettlementService when each package is marked delivered.
 */
class RiderDailyRemittanceRecorder
{
    public function __construct(
        protected RiderSettlementService $settlementService
    ) {}

    public function accumulateFromDeliveredPackage(OrderPackage $package): void
    {
        $package->loadMissing(['order.payment']);

        $order = $package->order;
        if (! $order instanceof Order || ! $this->orderUsesCod($order)) {
            return;
        }

        $riderId = $package->rider_id;
        if (! $riderId || ! $package->delivery_completed_at) {
            return;
        }

        $allocation = $this->settlementService->allocatePackage($package);

        $cod = round((float) ($allocation['cod_total'] ?? 0), 2);
        $seller = round((float) ($allocation['seller_share'] ?? 0), 2);
        $company = round((float) ($allocation['company_side_total'] ?? 0), 2);

        if ($cod <= 0 && $seller <= 0 && $company <= 0) {
            return;
        }

        $reportDate = Carbon::parse($package->delivery_completed_at)
            ->timezone(config('app.timezone'))
            ->startOfDay()
            ->toDateString();

        DB::transaction(function () use ($riderId, $reportDate, $cod, $seller, $company) {
            /** @var RiderRemittanceReport|null $row */
            $row = RiderRemittanceReport::query()
                ->where('rider_id', $riderId)
                ->whereDate('report_date', $reportDate)
                ->lockForUpdate()
                ->first();

            if ($row === null) {
                RiderRemittanceReport::create([
                    'rider_id' => $riderId,
                    'report_date' => $reportDate,
                    'cod_declared_total' => $cod,
                    'seller_pool_declared' => $seller,
                    'platform_pool_declared' => $company,
                    'notes' => 'Recorded automatically when COD deliveries complete.',
                    'submitted_at' => now(),
                ]);

                return;
            }

            $row->cod_declared_total = round((float) $row->cod_declared_total + $cod, 2);
            $row->seller_pool_declared = round((float) ($row->seller_pool_declared ?? 0) + $seller, 2);
            $row->platform_pool_declared = round((float) ($row->platform_pool_declared ?? 0) + $company, 2);
            $row->submitted_at = now();
            $row->save();
        });
    }

    protected function orderUsesCod(Order $order): bool
    {
        return $order->payment && strtolower((string) $order->payment->payment_method) === 'cod';
    }
}
