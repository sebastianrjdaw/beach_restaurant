<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('restaurant_settings', function (Blueprint $table) {
            $table->unsignedSmallInteger('max_reservations_per_slot')->nullable()->after('max_days_in_advance');
            $table->unsignedSmallInteger('max_guests_per_slot')->nullable()->after('max_reservations_per_slot');
        });
    }

    public function down(): void
    {
        Schema::table('restaurant_settings', function (Blueprint $table) {
            $table->dropColumn(['max_reservations_per_slot', 'max_guests_per_slot']);
        });
    }
};
