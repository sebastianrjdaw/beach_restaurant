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
        ];
    }
}
