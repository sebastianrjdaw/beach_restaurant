<?php

namespace App\Models;

use App\Enums\ReservationOrigin;
use App\Enums\ReservationStatus;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;

class Reservation extends Model
{
    protected $fillable = [
        'reservation_date',
        'start_time',
        'end_time',
        'party_size',
        'customer_name',
        'customer_email',
        'customer_phone',
        'locale',
        'comments',
        'status',
        'origin',
        'confirmation_code',
        'confirmed_at',
    ];

    protected function casts(): array
    {
        return [
            'reservation_date' => 'date',
            'status' => ReservationStatus::class,
            'origin' => ReservationOrigin::class,
            'confirmed_at' => 'datetime',
        ];
    }

    public function tables(): BelongsToMany
    {
        return $this->belongsToMany(RestaurantTable::class, 'reservation_tables', 'reservation_id', 'table_id')
            ->withTimestamps();
    }
}
