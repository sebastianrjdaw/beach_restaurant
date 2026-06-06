<?php

namespace App\Services;

use App\Enums\ReservationStatus;
use App\Models\BlockedSlot;
use App\Models\OpeningHour;
use App\Models\Reservation;
use App\Models\RestaurantSetting;
use App\Models\RestaurantTable;
use App\Models\SpecialDay;
use Carbon\CarbonImmutable;
use Illuminate\Support\Collection;

class AvailabilityService
{
    public function availableSlots(string $date, ?int $partySize = null, ?int $preferredAreaId = null): array
    {
        $settings = $this->settings();
        $day = CarbonImmutable::parse($date, $settings->timezone);
        $windows = $this->openingWindows($day);

        $duration = (int) $settings->default_reservation_duration;
        $interval = (int) $settings->reservation_interval;
        $slots = [];

        foreach ($windows as [$opensAt, $closesAt]) {
            for ($slot = $opensAt; $slot->addMinutes($duration)->lessThanOrEqualTo($closesAt); $slot = $slot->addMinutes($interval)) {
                $endsAt = $slot->addMinutes($duration);

                if ($this->hasCapacity($day, $slot, $endsAt, $partySize ?? 1, $preferredAreaId)) {
                    $slots[] = [
                        'time' => $slot->format('H:i'),
                        'ends_at' => $endsAt->format('H:i'),
                        'label' => $slot->format('H:i'),
                    ];
                }
            }
        }

        return $slots;
    }

    public function publicSlots(string $date, ?int $partySize = null, ?int $preferredAreaId = null): array
    {
        $settings = $this->settings();
        $day = CarbonImmutable::parse($date, $settings->timezone);
        $now = CarbonImmutable::now($settings->timezone);
        $windows = $this->openingWindows($day);

        $duration = (int) $settings->default_reservation_duration;
        $interval = (int) $settings->reservation_interval;
        $slots = [];

        foreach ($windows as [$opensAt, $closesAt, $label]) {
            for ($slot = $opensAt; $slot->addMinutes($duration)->lessThanOrEqualTo($closesAt); $slot = $slot->addMinutes($interval)) {
                $endsAt = $slot->addMinutes($duration);
                $isTooSoon = $slot->lessThanOrEqualTo($now->addMinutes((int) $settings->min_minutes_before_reservation));
                $hasCapacity = ! $isTooSoon && $this->hasCapacity($day, $slot, $endsAt, $partySize ?? 1, $preferredAreaId);

                $slots[] = [
                    'time' => $slot->format('H:i'),
                    'ends_at' => $endsAt->format('H:i'),
                    'label' => $slot->format('H:i'),
                    'shift' => $label ?: $this->shiftLabel($slot),
                    'is_available' => $hasCapacity,
                    'disabled_reason' => match (true) {
                        $isTooSoon => 'No cumple el tiempo minimo de antelacion',
                        ! $hasCapacity => 'Sin disponibilidad',
                        default => null,
                    },
                ];
            }
        }

        return $slots;
    }

    public function bookableSlots(string $date): array
    {
        $settings = $this->settings();
        $day = CarbonImmutable::parse($date, $settings->timezone);
        $duration = (int) $settings->default_reservation_duration;
        $interval = (int) $settings->reservation_interval;
        $slots = [];

        foreach ($this->openingWindows($day) as $window) {
            [$opensAt, $closesAt, $label] = $window;

            for ($slot = $opensAt; $slot->addMinutes($duration)->lessThanOrEqualTo($closesAt); $slot = $slot->addMinutes($interval)) {
                $endsAt = $slot->addMinutes($duration);

                $slots[] = [
                    'time' => $slot->format('H:i'),
                    'ends_at' => $endsAt->format('H:i'),
                    'label' => $slot->format('H:i'),
                    'shift' => $label ?: $this->shiftLabel($slot),
                ];
            }
        }

        return $slots;
    }

    public function operationalLimitWarnings(string $date, string $startTime, int $partySize): array
    {
        $settings = $this->settings();
        $summary = $this->slotReservationSummary($date, $startTime);
        $warnings = [];

        if ($settings->max_reservations_per_slot && $summary['reservations'] >= $settings->max_reservations_per_slot) {
            $warnings[] = 'Se supera el maximo de reservas previsto para este turno.';
        }

        if ($settings->max_guests_per_slot && ($summary['guests'] + $partySize) > $settings->max_guests_per_slot) {
            $warnings[] = 'Se supera el maximo de comensales previsto para este turno.';
        }

        return $warnings;
    }

    public function dailyPlan(string $date): array
    {
        $settings = $this->settings();
        $reservations = Reservation::query()
            ->whereDate('reservation_date', $date)
            ->with('tables.area')
            ->orderBy('start_time')
            ->get()
            ->groupBy(fn (Reservation $reservation): string => substr((string) $reservation->start_time, 0, 5));

        $slots = collect($this->bookableSlots($date))
            ->map(function (array $slot) use ($reservations, $settings): array {
                $slotReservations = $reservations->get($slot['time'], collect());
                $activeReservations = $slotReservations->whereIn('status', [
                    ReservationStatus::Pending,
                    ReservationStatus::PendingEmailVerification,
                    ReservationStatus::Confirmed,
                ]);
                $guests = (int) $activeReservations->sum('party_size');
                $tables = $activeReservations
                    ->flatMap(fn (Reservation $reservation) => $reservation->tables)
                    ->unique('id');

                return [
                    ...$slot,
                    'reservations_count' => $activeReservations->count(),
                    'guests_count' => $guests,
                    'tables_count' => $tables->count(),
                    'tables_capacity' => (int) $tables->sum('capacity'),
                    'max_reservations' => $settings->max_reservations_per_slot,
                    'max_guests' => $settings->max_guests_per_slot,
                    'is_over_reservations_limit' => $settings->max_reservations_per_slot
                        && $activeReservations->count() > $settings->max_reservations_per_slot,
                    'is_over_guests_limit' => $settings->max_guests_per_slot
                        && $guests > $settings->max_guests_per_slot,
                    'reservations' => $slotReservations->values(),
                ];
            })
            ->groupBy('shift')
            ->all();

        return $slots;
    }

    public function firstAvailableTables(
        string $date,
        string $startTime,
        string $endTime,
        int $partySize,
        ?int $preferredAreaId = null,
        bool $strictAreaPreference = false,
        ?int $ignoreReservationId = null,
    ): Collection
    {
        $day = CarbonImmutable::parse($date);
        $start = CarbonImmutable::parse($date.' '.$startTime);
        $end = CarbonImmutable::parse($date.' '.$endTime);
        $busyTableIds = $this->busyTableIds($day, $start, $end, $ignoreReservationId);

        $tables = RestaurantTable::query()
            ->where('is_active', true)
            ->whereNotIn('id', $busyTableIds)
            ->when($strictAreaPreference && $preferredAreaId, fn ($query) => $query->where('area_id', $preferredAreaId))
            ->when($preferredAreaId, fn ($query) => $query->orderByRaw('area_id = ? desc', [$preferredAreaId]))
            ->orderBy('capacity')
            ->get();

        $selected = collect();
        $capacity = 0;

        foreach ($tables as $table) {
            $selected->push($table);
            $capacity += $table->capacity;

            if ($capacity >= $partySize) {
                return $selected;
            }
        }

        return collect();
    }

    public function isAvailable(string $date, string $startTime, int $partySize): bool
    {
        $settings = $this->settings();
        $start = CarbonImmutable::parse($date.' '.$startTime, $settings->timezone);
        $end = $start->addMinutes((int) $settings->default_reservation_duration);

        return $this->hasCapacity(CarbonImmutable::parse($date, $settings->timezone), $start, $end, $partySize);
    }

    public function settings(): RestaurantSetting
    {
        return RestaurantSetting::query()->first()
            ?? new RestaurantSetting([
                'name' => 'Restaurante A Saina',
                'default_reservation_duration' => 90,
                'reservation_interval' => 30,
                'max_days_in_advance' => 30,
                'timezone' => 'Europe/Madrid',
                'default_locale' => 'es',
                'locales' => ['es', 'en'],
            ]);
    }

    public function openingWindows(CarbonImmutable $day): array
    {
        $specialDay = SpecialDay::query()->whereDate('date', $day->toDateString())->first();

        if ($specialDay?->is_closed) {
            return [];
        }

        if ($specialDay && $specialDay->opens_at && $specialDay->closes_at) {
            return [[
                CarbonImmutable::parse($day->toDateString().' '.$specialDay->opens_at),
                CarbonImmutable::parse($day->toDateString().' '.$specialDay->closes_at),
                'Especial',
            ]];
        }

        $openingHours = OpeningHour::query()
            ->where('weekday', $day->dayOfWeekIso)
            ->where('is_closed', false)
            ->orderBy('opens_at')
            ->get();

        if ($openingHours->isEmpty()) {
            return [];
        }

        return $openingHours
            ->map(fn (OpeningHour $openingHour) => [
                CarbonImmutable::parse($day->toDateString().' '.$openingHour->opens_at),
                CarbonImmutable::parse($day->toDateString().' '.$openingHour->closes_at),
                $openingHour->label,
            ])
            ->all();
    }

    private function hasCapacity(CarbonImmutable $day, CarbonImmutable $start, CarbonImmutable $end, int $partySize, ?int $preferredAreaId = null): bool
    {
        if ($this->isManuallyBlocked($day, $start, $end)) {
            return false;
        }

        if ($this->exceedsOperationalLimits($day->toDateString(), $start->format('H:i'), $partySize)) {
            return false;
        }

        $settings = $this->settings();

        return $this->firstAvailableTables(
            $day->toDateString(),
            $start->format('H:i'),
            $end->format('H:i'),
            $partySize,
            $preferredAreaId,
            (bool) $settings->strict_area_preference,
        )
            ->sum('capacity') >= $partySize;
    }

    private function exceedsOperationalLimits(string $date, string $startTime, int $partySize): bool
    {
        return $this->operationalLimitWarnings($date, $startTime, $partySize) !== [];
    }

    private function slotReservationSummary(string $date, string $startTime): array
    {
        $reservations = Reservation::query()
            ->whereDate('reservation_date', $date)
            ->where('start_time', $startTime)
            ->whereIn('status', [
                ReservationStatus::Pending->value,
                ReservationStatus::PendingEmailVerification->value,
                ReservationStatus::Confirmed->value,
            ])
            ->get();

        return [
            'reservations' => $reservations->count(),
            'guests' => (int) $reservations->sum('party_size'),
        ];
    }

    private function shiftLabel(CarbonImmutable $slot): string
    {
        return $slot->hour < 18 ? 'Comida' : 'Cena';
    }

    private function busyTableIds(CarbonImmutable $day, CarbonImmutable $start, CarbonImmutable $end, ?int $ignoreReservationId = null): array
    {
        $reservationTableIds = Reservation::query()
            ->whereDate('reservation_date', $day->toDateString())
            ->when($ignoreReservationId, fn ($query) => $query->whereKeyNot($ignoreReservationId))
            ->whereIn('status', [
                ReservationStatus::Pending->value,
                ReservationStatus::PendingEmailVerification->value,
                ReservationStatus::Confirmed->value,
            ])
            ->where('start_time', '<', $end->format('H:i:s'))
            ->where('end_time', '>', $start->format('H:i:s'))
            ->with('tables:id')
            ->get()
            ->flatMap(fn (Reservation $reservation) => $reservation->tables->pluck('id'))
            ->all();

        $blockedTableIds = BlockedSlot::query()
            ->whereDate('date', $day->toDateString())
            ->whereNotNull('table_id')
            ->where('starts_at', '<', $end->format('H:i:s'))
            ->where('ends_at', '>', $start->format('H:i:s'))
            ->pluck('table_id')
            ->all();

        return array_values(array_unique([...$reservationTableIds, ...$blockedTableIds]));
    }

    private function isManuallyBlocked(CarbonImmutable $day, CarbonImmutable $start, CarbonImmutable $end): bool
    {
        return BlockedSlot::query()
            ->whereDate('date', $day->toDateString())
            ->whereNull('area_id')
            ->whereNull('table_id')
            ->where('starts_at', '<', $end->format('H:i:s'))
            ->where('ends_at', '>', $start->format('H:i:s'))
            ->exists();
    }
}
