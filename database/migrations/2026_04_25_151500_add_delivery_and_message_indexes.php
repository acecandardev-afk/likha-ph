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
            $table->index(['delivery_status', 'rider_id'], 'orders_delivery_status_rider_idx');
            $table->index(['rider_id', 'delivery_status', 'created_at'], 'orders_rider_delivery_created_idx');
            $table->index('delivery_completed_at', 'orders_delivery_completed_at_idx');
        });

        Schema::table('messages', function (Blueprint $table) {
            $table->index(['order_id', 'id'], 'messages_order_id_id_idx');
            $table->index(['order_id', 'is_read', 'sender_id'], 'messages_order_read_sender_idx');
        });

        Schema::table('direct_messages', function (Blueprint $table) {
            $table->index(['sender_id', 'recipient_id', 'id'], 'direct_messages_sender_recipient_id_idx');
            $table->index(['recipient_id', 'sender_id', 'id'], 'direct_messages_recipient_sender_id_idx');
            $table->index(['recipient_id', 'created_at'], 'direct_messages_recipient_created_at_idx');
        });

        Schema::table('order_delivery_histories', function (Blueprint $table) {
            $table->index(['order_id', 'status_at'], 'order_delivery_histories_order_status_at_idx');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('orders', function (Blueprint $table) {
            $table->dropIndex('orders_delivery_status_rider_idx');
            $table->dropIndex('orders_rider_delivery_created_idx');
            $table->dropIndex('orders_delivery_completed_at_idx');
        });

        Schema::table('messages', function (Blueprint $table) {
            $table->dropIndex('messages_order_id_id_idx');
            $table->dropIndex('messages_order_read_sender_idx');
        });

        Schema::table('direct_messages', function (Blueprint $table) {
            $table->dropIndex('direct_messages_sender_recipient_id_idx');
            $table->dropIndex('direct_messages_recipient_sender_id_idx');
            $table->dropIndex('direct_messages_recipient_created_at_idx');
        });

        Schema::table('order_delivery_histories', function (Blueprint $table) {
            $table->dropIndex('order_delivery_histories_order_status_at_idx');
        });
    }
};
