<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('order_packages', function (Blueprint $table) {
            $table->decimal('rider_fee_amount', 10, 2)->nullable()->after('platform_fee_realized_at');
        });

        $defaultFee = round((float) env('RIDER_FEE_PER_PACKAGE', 50), 2);

        DB::table('order_packages')
            ->where('delivery_status', 'delivered')
            ->whereNull('rider_fee_amount')
            ->update(['rider_fee_amount' => $defaultFee]);
    }

    public function down(): void
    {
        Schema::table('order_packages', function (Blueprint $table) {
            $table->dropColumn('rider_fee_amount');
        });
    }
};
