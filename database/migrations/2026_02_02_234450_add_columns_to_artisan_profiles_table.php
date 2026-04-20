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
        Schema::table('artisan_profiles', function (Blueprint $table) {
            $table->foreignId('user_id')->after('id')->constrained()->cascadeOnDelete();
            $table->string('workshop_name')->after('user_id');
            $table->text('story')->nullable()->after('workshop_name');
            $table->string('city')->nullable()->after('story');
            $table->string('barangay')->nullable()->after('city');
            $table->string('profile_image')->nullable()->after('barangay');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('artisan_profiles', function (Blueprint $table) {
            $table->dropForeign(['user_id']);
            $table->dropColumn(['workshop_name', 'story', 'city', 'barangay', 'profile_image']);
        });
    }
};
