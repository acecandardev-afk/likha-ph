<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('order_packages', function (Blueprint $table) {
            $table->id();
            $table->foreignId('order_id')->constrained()->cascadeOnDelete();
            $table->unsignedTinyInteger('sequence')->default(1);
            $table->foreignId('rider_id')->nullable()->constrained('riders')->nullOnDelete();
            $table->string('delivery_status')->default('pending_assignment');
            $table->timestamp('delivery_assigned_at')->nullable();
            $table->timestamp('delivery_completed_at')->nullable();
            $table->string('delivery_proof_image')->nullable();
            $table->decimal('platform_fee_share', 10, 2)->default(0);
            $table->timestamp('platform_fee_realized_at')->nullable();
            $table->timestamps();

            $table->index(['delivery_status', 'rider_id']);
            $table->index(['order_id', 'sequence']);
        });

        Schema::create('order_package_items', function (Blueprint $table) {
            $table->id();
            $table->foreignId('order_package_id')->constrained('order_packages')->cascadeOnDelete();
            $table->foreignId('order_item_id')->constrained('order_items')->cascadeOnDelete();
            $table->unsignedInteger('quantity');
            $table->timestamps();

            $table->unique(['order_package_id', 'order_item_id']);
        });

        Schema::table('order_delivery_histories', function (Blueprint $table) {
            $table->foreignId('order_package_id')->nullable()->after('order_id')->constrained('order_packages')->nullOnDelete();
        });

        Schema::create('delivery_reports', function (Blueprint $table) {
            $table->id();
            $table->foreignId('order_package_id')->constrained('order_packages')->cascadeOnDelete();
            $table->foreignId('user_id')->constrained()->cascadeOnDelete();
            $table->string('concern', 120);
            $table->text('details')->nullable();
            $table->string('proof_image')->nullable();
            $table->string('status')->default('open');
            $table->foreignId('reviewed_by')->nullable()->constrained('users')->nullOnDelete();
            $table->text('admin_notes')->nullable();
            $table->timestamps();

            $table->index(['status', 'created_at']);
        });

        Schema::table('riders', function (Blueprint $table) {
            $table->date('birth_date')->nullable()->after('vehicle_type');
            $table->string('emergency_contact_name')->nullable()->after('birth_date');
            $table->string('emergency_contact_phone')->nullable()->after('emergency_contact_name');
            $table->string('license_number')->nullable()->after('emergency_contact_phone');
            $table->string('license_expiry')->nullable()->after('license_number');
            $table->string('vehicle_plate')->nullable()->after('license_expiry');
            $table->text('bio')->nullable()->after('vehicle_plate');
            $table->string('license_image')->nullable()->after('bio');
            $table->string('id_document_image')->nullable()->after('license_image');
            $table->string('clearance_document_image')->nullable()->after('id_document_image');
        });

        $this->backfillPackagesFromOrders();
    }

    protected function backfillPackagesFromOrders(): void
    {
        $orders = DB::table('orders')->orderBy('id')->get();

        foreach ($orders as $order) {
            $items = DB::table('order_items')->where('order_id', $order->id)->get();
            if ($items->isEmpty()) {
                continue;
            }

            $platformFee = (float) ($order->platform_fee ?? 0);

            $packageId = DB::table('order_packages')->insertGetId([
                'order_id' => $order->id,
                'sequence' => 1,
                'rider_id' => $order->rider_id,
                'delivery_status' => $order->delivery_status ?? 'pending_assignment',
                'delivery_assigned_at' => $order->delivery_assigned_at,
                'delivery_completed_at' => $order->delivery_completed_at,
                'delivery_proof_image' => $order->delivery_proof_image,
                'platform_fee_share' => round($platformFee, 2),
                'platform_fee_realized_at' => (($order->delivery_status ?? '') === 'delivered')
                    ? ($order->delivery_completed_at ?? now())
                    : null,
                'created_at' => now(),
                'updated_at' => now(),
            ]);

            foreach ($items as $item) {
                DB::table('order_package_items')->insert([
                    'order_package_id' => $packageId,
                    'order_item_id' => $item->id,
                    'quantity' => $item->quantity,
                    'created_at' => now(),
                    'updated_at' => now(),
                ]);
            }
        }
    }

    public function down(): void
    {
        Schema::table('riders', function (Blueprint $table) {
            foreach ([
                'birth_date',
                'emergency_contact_name',
                'emergency_contact_phone',
                'license_number',
                'license_expiry',
                'vehicle_plate',
                'bio',
                'license_image',
                'id_document_image',
                'clearance_document_image',
            ] as $col) {
                if (Schema::hasColumn('riders', $col)) {
                    $table->dropColumn($col);
                }
            }
        });

        Schema::dropIfExists('delivery_reports');

        Schema::table('order_delivery_histories', function (Blueprint $table) {
            if (Schema::hasColumn('order_delivery_histories', 'order_package_id')) {
                $table->dropForeign(['order_package_id']);
                $table->dropColumn('order_package_id');
            }
        });

        Schema::dropIfExists('order_package_items');
        Schema::dropIfExists('order_packages');
    }
};
