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
        Schema::create('riders', function (Blueprint $table) {
            $table->id();
            $table->string('rider_id')->unique();
            $table->foreignId('user_id')->unique()->constrained('users')->cascadeOnDelete();
            $table->string('full_name');
            $table->string('contact_number');
            $table->string('email')->unique();
            $table->text('address')->nullable();
            $table->string('vehicle_type')->nullable();
            $table->string('status')->default('available'); // available, busy, offline
            $table->timestamp('date_created')->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('riders');
    }
};
