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
        Schema::create('payments', function (Blueprint $table) {
            $table->id();

            // Link to order
            $table->foreignId('order_id')
                ->constrained()
                ->cascadeOnDelete();

            // Basic payment info
            $table->string('payment_method');
            $table->decimal('amount', 10, 2);
            $table->string('proof_image')->nullable();

            // Verification workflow
            $table->string('verification_status')
                ->default('awaiting_proof');
            $table->foreignId('verified_by')
                ->nullable()
                ->constrained('users')
                ->nullOnDelete();
            $table->text('verification_notes')->nullable();
            $table->timestamp('verified_at')->nullable();

            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('payments');
    }
};
