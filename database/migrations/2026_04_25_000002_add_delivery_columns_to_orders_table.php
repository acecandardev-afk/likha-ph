<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::table('orders', function (Blueprint $table) {
            if (! Schema::hasColumn('orders', 'rider_id')) {
                $table->foreignId('rider_id')->nullable()->after('artisan_id')->constrained('riders')->nullOnDelete();
            }

            if (! Schema::hasColumn('orders', 'delivery_status')) {
                $table->string('delivery_status')->default('pending_assignment')->after('status');
            }

            if (! Schema::hasColumn('orders', 'delivery_assigned_at')) {
                $table->timestamp('delivery_assigned_at')->nullable()->after('approved_at');
            }

            if (! Schema::hasColumn('orders', 'delivery_completed_at')) {
                $table->timestamp('delivery_completed_at')->nullable()->after('delivery_assigned_at');
            }

            if (! Schema::hasColumn('orders', 'delivery_proof_image')) {
                $table->string('delivery_proof_image')->nullable()->after('delivery_completed_at');
            }
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('orders', function (Blueprint $table) {
            foreach (['rider_id', 'delivery_status', 'delivery_assigned_at', 'delivery_completed_at', 'delivery_proof_image'] as $column) {
                if (Schema::hasColumn('orders', $column)) {
                    if ($column === 'rider_id') {
                        $table->dropConstrainedForeignId($column);
                    } else {
                        $table->dropColumn($column);
                    }
                }
            }
        });
    }
};
