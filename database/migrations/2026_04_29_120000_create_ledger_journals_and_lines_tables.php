<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('ledger_journals', function (Blueprint $table) {
            $table->id();
            $table->foreignId('order_id')->constrained()->cascadeOnDelete();
            $table->string('kind', 80)->default('delivery_settlement');
            $table->timestamp('posted_at');
            $table->timestamps();

            $table->unique(['order_id', 'kind']);
        });

        Schema::create('ledger_lines', function (Blueprint $table) {
            $table->id();
            $table->foreignId('ledger_journal_id')->constrained('ledger_journals')->cascadeOnDelete();
            $table->unsignedSmallInteger('sequence');
            $table->string('side', 12);
            $table->string('bucket', 80);
            $table->decimal('amount', 14, 2);
            $table->string('memo', 500)->nullable();
            $table->timestamps();

            $table->index(['ledger_journal_id', 'sequence']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('ledger_lines');
        Schema::dropIfExists('ledger_journals');
    }
};
