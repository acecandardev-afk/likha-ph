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
        Schema::table('products', function (Blueprint $table) {
            $table->foreignId('artisan_id')->after('id')->constrained('users')->cascadeOnDelete();
            $table->foreignId('category_id')->after('artisan_id')->constrained()->cascadeOnDelete();
            $table->string('name')->after('category_id');
            $table->text('description')->nullable()->after('name');
            $table->decimal('price', 10, 2)->after('description');
            $table->unsignedInteger('stock')->default(0)->after('price');
            $table->string('approval_status')->default('pending')->after('stock');
            $table->boolean('is_active')->default(true)->after('approval_status');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('products', function (Blueprint $table) {
            $table->dropForeign(['artisan_id']);
            $table->dropForeign(['category_id']);
            $table->dropColumn(['artisan_id', 'category_id', 'name', 'description', 'price', 'stock', 'approval_status', 'is_active']);
        });
    }
};
