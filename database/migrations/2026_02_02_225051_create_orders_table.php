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
        Schema::create('orders', function (Blueprint $table) {
            $table->id();

            // Basic order info
            $table->string('order_number')->unique();

            // Relationships
            $table->foreignId('customer_id')
                ->constrained('users')
                ->cascadeOnDelete();
            $table->foreignId('artisan_id')
                ->constrained('users')
                ->cascadeOnDelete();

            // Amounts
            $table->decimal('subtotal', 10, 2);
            $table->decimal('total', 10, 2);

            // Status & notes
            $table->string('status')->default('pending');
            $table->text('customer_notes')->nullable();

            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('orders');
    }
};
