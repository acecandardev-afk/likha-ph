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
        // Orders table indexes
        Schema::table('orders', function (Blueprint $table) {
            $table->index('status', 'orders_status_idx');
            $table->index(['customer_id', 'status'], 'orders_customer_status_idx');
            $table->index(['artisan_id', 'status'], 'orders_artisan_status_idx');
            $table->index('created_at', 'orders_created_at_idx');
        });

        // Users table indexes for address fields
        Schema::table('users', function (Blueprint $table) {
            $table->index(['country', 'region', 'province'], 'users_address_idx');
        });

        // Products table indexes
        Schema::table('products', function (Blueprint $table) {
            $table->index(['artisan_id', 'is_active', 'approval_status'], 'products_artisan_active_approval_idx');
            $table->index(['category_id', 'is_active'], 'products_category_active_idx');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('orders', function (Blueprint $table) {
            $table->dropIndex('orders_status_idx');
            $table->dropIndex('orders_customer_status_idx');
            $table->dropIndex('orders_artisan_status_idx');
            $table->dropIndex('orders_created_at_idx');
        });

        Schema::table('users', function (Blueprint $table) {
            $table->dropIndex('users_address_idx');
        });

        Schema::table('products', function (Blueprint $table) {
            $table->dropIndex('products_artisan_active_approval_idx');
            $table->dropIndex('products_category_active_idx');
        });
    }
};
