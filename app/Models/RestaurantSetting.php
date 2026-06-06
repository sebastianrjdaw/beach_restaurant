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
        ];
    }
}
