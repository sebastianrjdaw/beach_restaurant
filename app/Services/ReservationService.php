<?php

namespace App\Services;

use App\Enums\ReservationOrigin;
use App\Enums\ReservationStatus;
use App\Models\Reservation;
use Carbon\CarbonImmutable;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use RuntimeException;

class ReservationService
{
    public function __construct(private readonly AvailabilityService $availability)
    {
    }

    public function create(array $data, ReservationOrigin $origin = ReservationOrigin::Web): Reservation
    {
        return DB::transaction(function () use ($data, $origin) {
            $settings = $this->availability->settings();
            $status = $data['status'] ?? ReservationStatus::Pending;
            $statusValue = $status instanceof ReservationStatus ? $status->value : $status;
            $endTime = CarbonImmutable::parse($data['reservation_date'].' '.$data['start_time'], $settings->timezone)
                ->addMinutes((int) $settings->default_reservation_duration)
                ->format('H:i');

            $tables = $this->availability->firstAvailableTables(
                $data['reservation_date'],
                $data['start_time'],
                $endTime,
                (int) $data['party_size'],
                $data['preferred_area_id'] ?? null,
                (bool) $settings->strict_area_preference,
            );

            if ($tables->sum('capacity') < (int) $data['party_size']) {
                throw new RuntimeException('No availability for the selected slot.');
            }

            $reservation = Reservation::query()->create([
                'reservation_date' => $data['reservation_date'],
                'start_time' => $data['start_time'],
                'end_time' => $endTime,
                'party_size' => $data['party_size'],
                'preferred_area_id' => $data['preferred_area_id'] ?? null,
                'customer_name' => $data['customer_name'],
                'customer_email' => $data['customer_email'] ?? null,
                'customer_phone' => $data['customer_phone'] ?? null,
                'locale' => $data['locale'] ?? $settings->default_locale,
                'comments' => $data['comments'] ?? null,
                'customer_notes' => $data['customer_notes'] ?? $data['comments'] ?? null,
                'internal_notes' => $data['internal_notes'] ?? null,
                'status' => $status,
                'origin' => $origin,
                'confirmation_code' => Str::upper(Str::random(8)),
                'public_token' => Str::random(48),
                'confirmed_at' => $statusValue === ReservationStatus::Confirmed->value ? now() : null,
            ]);

            $reservation->tables()->sync($tables->pluck('id')->all());

            return $reservation->load('tables.area');
        });
    }
}
