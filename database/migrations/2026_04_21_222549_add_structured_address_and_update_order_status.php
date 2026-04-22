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
        Schema::table('users', function (Blueprint $table) {
            $table->string('country')->default('Philippines')->after('address');
            $table->string('region')->nullable()->after('country');
            $table->string('province')->nullable()->after('region');
            $table->string('city')->nullable()->after('province');
            $table->string('barangay')->nullable()->after('city');
            $table->text('street_address')->nullable()->after('barangay');
        });

        Schema::table('orders', function (Blueprint $table) {
            $table->string('country')->default('Philippines')->after('shipping_phone');
            $table->string('region')->nullable()->after('country');
            $table->string('province')->nullable()->after('region');
            $table->string('city')->nullable()->after('province');
            $table->string('barangay')->nullable()->after('city');
            $table->text('street_address')->nullable()->after('barangay');
            $table->enum('status', ['pending', 'confirmed', 'shipped', 'on_delivery', 'received', 'delivered', 'completed', 'cancelled'])->default('pending')->change();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->dropColumn(['country', 'region', 'province', 'city', 'barangay', 'street_address']);
        });

        Schema::table('orders', function (Blueprint $table) {
            $table->dropColumn(['country', 'region', 'province', 'city', 'barangay', 'street_address']);
            $table->string('status')->default('pending')->change();
        });
    }
};
