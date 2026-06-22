<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('restaurant_settings', function (Blueprint $table) {
            $table->string('web_reservation_confirmation_mode')->default('manual')->after('max_guests_per_slot');
            $table->unsignedSmallInteger('email_verification_expiration_minutes')->default(30)->after('web_reservation_confirmation_mode');
            $table->boolean('allow_public_cancellations')->default(true)->after('email_verification_expiration_minutes');
            $table->unsignedSmallInteger('min_hours_before_public_cancellation')->default(3)->after('allow_public_cancellations');
            $table->boolean('strict_area_preference')->default(false)->after('min_hours_before_public_cancellation');
            $table->unsignedTinyInteger('min_guests_online')->default(1)->after('strict_area_preference');
            $table->unsignedTinyInteger('max_guests_online')->default(10)->after('min_guests_online');
            $table->boolean('large_party_requires_manual_confirmation')->default(true)->after('max_guests_online');
            $table->unsignedTinyInteger('large_party_threshold')->default(8)->after('large_party_requires_manual_confirmation');
            $table->unsignedSmallInteger('min_minutes_before_reservation')->default(60)->after('large_party_threshold');
        });

        Schema::table('reservations', function (Blueprint $table) {
            $table->foreignId('preferred_area_id')->nullable()->after('party_size')->constrained('areas')->nullOnDelete();
            $table->string('public_token')->nullable()->unique()->after('confirmation_code');
            $table->timestamp('email_verified_at')->nullable()->after('public_token');
            $table->timestamp('cancelled_at')->nullable()->after('confirmed_at');
            $table->string('cancel_reason')->nullable()->after('cancelled_at');
            $table->text('customer_notes')->nullable()->after('comments');
            $table->text('internal_notes')->nullable()->after('customer_notes');
        });
    }

    public function down(): void
    {
        Schema::table('reservations', function (Blueprint $table) {
            $table->dropConstrainedForeignId('preferred_area_id');
            $table->dropColumn([
                'public_token',
                'email_verified_at',
                'cancelled_at',
                'cancel_reason',
                'customer_notes',
                'internal_notes',
            ]);
        });

        Schema::table('restaurant_settings', function (Blueprint $table) {
            $table->dropColumn([
                'web_reservation_confirmation_mode',
                'email_verification_expiration_minutes',
                'allow_public_cancellations',
                'min_hours_before_public_cancellation',
                'strict_area_preference',
                'min_guests_online',
                'max_guests_online',
                'large_party_requires_manual_confirmation',
                'large_party_threshold',
                'min_minutes_before_reservation',
            ]);
        });
    }
};
