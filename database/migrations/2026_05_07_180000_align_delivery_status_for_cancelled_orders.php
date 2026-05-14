<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        $cancelledOrderIds = DB::table('orders')
            ->where('status', 'cancelled')
            ->pluck('id');

        if ($cancelledOrderIds->isEmpty()) {
            return;
        }

        DB::table('order_packages')
            ->whereIn('order_id', $cancelledOrderIds)
            ->where('delivery_status', '!=', 'delivered')
            ->update([
                'delivery_status' => 'cancelled',
                'rider_id' => null,
                'delivery_assigned_at' => null,
            ]);

        DB::table('orders')
            ->whereIn('id', $cancelledOrderIds)
            ->whereNotIn('delivery_status', ['delivered'])
            ->update([
                'delivery_status' => 'cancelled',
                'rider_id' => null,
                'delivery_assigned_at' => null,
                'delivery_completed_at' => null,
            ]);
    }

    public function down(): void
    {
        // Intentionally empty: prior delivery states cannot be restored safely.
    }
};
