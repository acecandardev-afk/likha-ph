<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('users', function (Blueprint $table) {
            if (! Schema::hasColumn('users', 'shipping_barangay')) {
                $table->string('shipping_barangay', 120)->nullable()->after('shipping_address');
            }
        });

        Schema::table('orders', function (Blueprint $table) {
            if (! Schema::hasColumn('orders', 'shipping_barangay')) {
                $table->string('shipping_barangay', 120)->nullable()->after('shipping_address');
            }
        });
    }

    public function down(): void
    {
        Schema::table('users', function (Blueprint $table) {
            if (Schema::hasColumn('users', 'shipping_barangay')) {
                $table->dropColumn('shipping_barangay');
            }
        });

        Schema::table('orders', function (Blueprint $table) {
            if (Schema::hasColumn('orders', 'shipping_barangay')) {
                $table->dropColumn('shipping_barangay');
            }
        });
    }
};
