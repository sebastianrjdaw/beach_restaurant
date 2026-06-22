<?php

namespace App\Models;

use App\Enums\ReservationOrigin;
use App\Enums\ReservationStatus;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;

class Reservation extends Model
{
    protected $fillable = [
        'reservation_date',
        'start_time',
        'end_time',
        'party_size',
        'preferred_area_id',
        'customer_name',
        'customer_email',
        'customer_phone',
        'locale',
        'comments',
        'customer_notes',
        'internal_notes',
        'status',
        'origin',
        'confirmation_code',
        'public_token',
        'email_verified_at',
        'confirmed_at',
        'cancelled_at',
        'cancel_reason',
    ];

    protected function casts(): array
    {
        return [
            'reservation_date' => 'date',
            'status' => ReservationStatus::class,
            'origin' => ReservationOrigin::class,
            'email_verified_at' => 'datetime',
            'confirmed_at' => 'datetime',
            'cancelled_at' => 'datetime',
        ];
    }

    public function preferredArea(): BelongsTo
    {
        return $this->belongsTo(Area::class, 'preferred_area_id');
    }

    public function tables(): BelongsToMany
    {
        return $this->belongsToMany(RestaurantTable::class, 'reservation_tables', 'reservation_id', 'table_id')
            ->withTimestamps();
    }
}
