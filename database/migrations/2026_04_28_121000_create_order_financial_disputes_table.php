<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Must run after order_packages exist (2026_04_28_120000).
     * order_package_id has no DB FK — nullable FKs to order_packages caused errno 150 on MariaDB.
     */
    public function up(): void
    {
        Schema::create('order_financial_disputes', function (Blueprint $table) {
            $table->id();
            $table->foreignId('order_id')->constrained('orders')->cascadeOnDelete();
            $table->unsignedBigInteger('order_package_id')->nullable();
            $table->index('order_package_id');
            $table->foreignId('user_id')->constrained('users')->cascadeOnDelete();
            $table->string('actor_role', 32);
            $table->string('category', 48);
            $table->text('description');
            $table->string('status', 24)->default('open');
            $table->text('resolution_notes')->nullable();
            $table->foreignId('resolved_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamp('resolved_at')->nullable();
            $table->timestamps();

            $table->index(['status', 'created_at']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('order_financial_disputes');
    }
};
