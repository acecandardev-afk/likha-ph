<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('rider_remittance_reports', function (Blueprint $table) {
            $table->id();
            $table->foreignId('rider_id')->constrained('riders')->cascadeOnDelete();
            $table->date('report_date');
            $table->decimal('cod_declared_total', 14, 2);
            $table->decimal('seller_pool_declared', 14, 2)->nullable();
            $table->decimal('platform_pool_declared', 14, 2)->nullable();
            $table->text('notes')->nullable();
            $table->timestamp('submitted_at')->nullable();
            $table->timestamps();

            $table->unique(['rider_id', 'report_date']);
        });

        Schema::create('seller_cod_handoffs', function (Blueprint $table) {
            $table->id();
            $table->foreignId('order_id')->constrained('orders')->cascadeOnDelete();
            $table->foreignId('artisan_user_id')->constrained('users')->cascadeOnDelete();
            // FK added after create — MariaDB/MySQL often returns errno 150 for nullable
            // ledger_journal_id when defined inline with constrained() on CREATE TABLE.
            $table->unsignedBigInteger('ledger_journal_id')->nullable();
            $table->decimal('expected_artisan_payable', 14, 2);
            $table->timestamp('acknowledged_at')->nullable();
            $table->string('note', 500)->nullable();
            $table->timestamps();

            $table->unique(['order_id']);
        });

        Schema::create('order_financial_disputes', function (Blueprint $table) {
            $table->id();
            $table->foreignId('order_id')->constrained('orders')->cascadeOnDelete();
            $table->foreignId('order_package_id')->nullable()->constrained('order_packages')->nullOnDelete();
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

        Schema::table('seller_cod_handoffs', function (Blueprint $table) {
            $table->foreign('ledger_journal_id')
                ->references('id')
                ->on('ledger_journals')
                ->nullOnDelete();
        });
    }

    public function down(): void
    {
        Schema::table('seller_cod_handoffs', function (Blueprint $table) {
            $table->dropForeign(['ledger_journal_id']);
        });

        Schema::dropIfExists('order_financial_disputes');
        Schema::dropIfExists('seller_cod_handoffs');
        Schema::dropIfExists('rider_remittance_reports');
    }
};
