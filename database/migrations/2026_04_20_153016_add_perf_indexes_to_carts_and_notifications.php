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
        Schema::table('carts', function (Blueprint $table) {
            $table->index('user_id', 'carts_user_id_idx');
            $table->index(['user_id', 'product_id'], 'carts_user_product_idx');
        });

        Schema::table('user_notifications', function (Blueprint $table) {
            $table->index(['user_id', 'is_read'], 'user_notifications_user_read_idx');
            $table->index(['user_id', 'type', 'is_read'], 'user_notifications_user_type_read_idx');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('carts', function (Blueprint $table) {
            $table->dropIndex('carts_user_id_idx');
            $table->dropIndex('carts_user_product_idx');
        });

        Schema::table('user_notifications', function (Blueprint $table) {
            $table->dropIndex('user_notifications_user_read_idx');
            $table->dropIndex('user_notifications_user_type_read_idx');
        });
    }
};
