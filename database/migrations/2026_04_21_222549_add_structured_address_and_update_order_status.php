<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     *
     * Note: We do not use $table->enum()->change() here — Doctrine/dbal generates SQL that
     * PostgreSQL rejects. orders.status is already a string; allowed values are enforced in app code.
     */
    public function up(): void
    {
        Schema::table('users', function (Blueprint $table) {
            if (! Schema::hasColumn('users', 'country')) {
                $table->string('country')->default('Philippines')->after('address');
            }
            if (! Schema::hasColumn('users', 'region')) {
                $table->string('region')->nullable()->after('country');
            }
            if (! Schema::hasColumn('users', 'province')) {
                $table->string('province')->nullable()->after('region');
            }
            if (! Schema::hasColumn('users', 'city')) {
                $table->string('city')->nullable()->after('province');
            }
            if (! Schema::hasColumn('users', 'barangay')) {
                $table->string('barangay')->nullable()->after('city');
            }
            if (! Schema::hasColumn('users', 'street_address')) {
                $table->text('street_address')->nullable()->after('barangay');
            }
        });

        Schema::table('orders', function (Blueprint $table) {
            if (! Schema::hasColumn('orders', 'country')) {
                $table->string('country')->default('Philippines')->after('shipping_phone');
            }
            if (! Schema::hasColumn('orders', 'region')) {
                $table->string('region')->nullable()->after('country');
            }
            if (! Schema::hasColumn('orders', 'province')) {
                $table->string('province')->nullable()->after('region');
            }
            if (! Schema::hasColumn('orders', 'city')) {
                $table->string('city')->nullable()->after('province');
            }
            if (! Schema::hasColumn('orders', 'barangay')) {
                $table->string('barangay')->nullable()->after('city');
            }
            if (! Schema::hasColumn('orders', 'street_address')) {
                $table->text('street_address')->nullable()->after('barangay');
            }
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $uDrop = array_filter(
                ['country', 'region', 'province', 'city', 'barangay', 'street_address'],
                fn ($c) => Schema::hasColumn('users', $c)
            );
            if ($uDrop !== []) {
                $table->dropColumn($uDrop);
            }
        });

        Schema::table('orders', function (Blueprint $table) {
            $toDrop = array_filter(
                ['country', 'region', 'province', 'city', 'barangay', 'street_address'],
                fn ($c) => Schema::hasColumn('orders', $c)
            );
            if ($toDrop !== []) {
                $table->dropColumn($toDrop);
            }
        });
    }
};
