<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        // Add missing enum values for Postgres compatibility
        DB::statement("ALTER TYPE orders_status_enum ADD VALUE 'confirmed'");
        DB::statement("ALTER TYPE orders_status_enum ADD VALUE 'completed'");
        DB::statement("ALTER TYPE orders_status_enum ADD VALUE 'cancelled'");
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        // Note: Postgres doesn't support removing enum values easily
        // This is a one-way migration
    }
};
