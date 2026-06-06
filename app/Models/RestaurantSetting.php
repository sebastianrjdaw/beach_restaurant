<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class RestaurantSetting extends Model
{
    protected $fillable = [
        'name',
        'description',
        'email',
        'phone',
        'address',
        'city',
        'country',
        'latitude',
        'longitude',
        'default_reservation_duration',
        'reservation_interval',
        'max_days_in_advance',
        'max_reservations_per_slot',
        'max_guests_per_slot',
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
        'timezone',
        'default_locale',
        'locales',
    ];

    protected function casts(): array
    {
        return [
            'description' => 'array',
            'locales' => 'array',
            'latitude' => 'decimal:7',
            'longitude' => 'decimal:7',
            'max_reservations_per_slot' => 'integer',
            'max_guests_per_slot' => 'integer',
            'allow_public_cancellations' => 'boolean',
            'strict_area_preference' => 'boolean',
            'large_party_requires_manual_confirmation' => 'boolean',
            'email_verification_expiration_minutes' => 'integer',
            'min_hours_before_public_cancellation' => 'integer',
            'min_guests_online' => 'integer',
            'max_guests_online' => 'integer',
            'large_party_threshold' => 'integer',
            'min_minutes_before_reservation' => 'integer',
        ];
    }
}
