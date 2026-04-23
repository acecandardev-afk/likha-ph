<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    /**
     * Run the migrations.
     *
     * Only applies when a native PostgreSQL enum type "orders_status_enum" exists.
     * Default app schema uses orders.status as varchar, so this is often a no-op.
     */
    public function up(): void
    {
        if (DB::getDriverName() !== 'pgsql') {
            return;
        }

        $typeExists = DB::select(
            "SELECT 1 AS x FROM pg_type WHERE typname = 'orders_status_enum' LIMIT 1"
        );
        if ($typeExists === [] || $typeExists === null) {
            return;
        }

        DB::statement("ALTER TYPE orders_status_enum ADD VALUE IF NOT EXISTS 'confirmed'");
        DB::statement("ALTER TYPE orders_status_enum ADD VALUE IF NOT EXISTS 'completed'");
        DB::statement("ALTER TYPE orders_status_enum ADD VALUE IF NOT EXISTS 'cancelled'");
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        // Note: Postgres doesn't support removing enum values easily
    }
};
