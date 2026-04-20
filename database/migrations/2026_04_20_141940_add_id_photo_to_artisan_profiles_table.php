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
            if (! Schema::hasColumn('artisan_profiles', 'id_photo')) {
                $table->string('id_photo')->nullable()->after('profile_image');
            }
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('artisan_profiles', function (Blueprint $table) {
            if (Schema::hasColumn('artisan_profiles', 'id_photo')) {
                $table->dropColumn('id_photo');
            }
        });
    }
};
