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
    public function availableSlots(string $date, ?int $partySize = null): array
    {
        $settings = $this->settings();
        $day = CarbonImmutable::parse($date, $settings->timezone);
        $window = $this->openingWindow($day);

        if ($window === null) {
            return [];
        }

        [$opensAt, $closesAt] = $window;
        $duration = (int) $settings->default_reservation_duration;
        $interval = (int) $settings->reservation_interval;
        $slots = [];

        for ($slot = $opensAt; $slot->addMinutes($duration)->lessThanOrEqualTo($closesAt); $slot = $slot->addMinutes($interval)) {
            $endsAt = $slot->addMinutes($duration);

            if ($this->hasCapacity($day, $slot, $endsAt, $partySize ?? 1)) {
                $slots[] = [
                    'time' => $slot->format('H:i'),
                    'ends_at' => $endsAt->format('H:i'),
                    'label' => $slot->format('H:i'),
                ];
            }
        }

        return $slots;
    }

    public function firstAvailableTables(string $date, string $startTime, string $endTime, int $partySize): Collection
    {
        $day = CarbonImmutable::parse($date);
        $start = CarbonImmutable::parse($date.' '.$startTime);
        $end = CarbonImmutable::parse($date.' '.$endTime);
        $busyTableIds = $this->busyTableIds($day, $start, $end);

        $tables = RestaurantTable::query()
            ->where('is_active', true)
            ->whereNotIn('id', $busyTableIds)
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
                'name' => 'Beach Restaurant',
                'default_reservation_duration' => 90,
                'reservation_interval' => 30,
                'timezone' => 'Europe/Madrid',
                'default_locale' => 'es',
                'locales' => ['es', 'en'],
            ]);
    }

    private function openingWindow(CarbonImmutable $day): ?array
    {
        $specialDay = SpecialDay::query()->whereDate('date', $day->toDateString())->first();

        if ($specialDay?->is_closed) {
            return null;
        }

        if ($specialDay && $specialDay->opens_at && $specialDay->closes_at) {
            return [
                CarbonImmutable::parse($day->toDateString().' '.$specialDay->opens_at),
                CarbonImmutable::parse($day->toDateString().' '.$specialDay->closes_at),
            ];
        }

        $openingHour = OpeningHour::query()
            ->where('weekday', $day->dayOfWeekIso)
            ->where('is_closed', false)
            ->first();

        if ($openingHour === null) {
            return null;
        }

        return [
            CarbonImmutable::parse($day->toDateString().' '.$openingHour->opens_at),
            CarbonImmutable::parse($day->toDateString().' '.$openingHour->closes_at),
        ];
    }

    private function hasCapacity(CarbonImmutable $day, CarbonImmutable $start, CarbonImmutable $end, int $partySize): bool
    {
        if ($this->isManuallyBlocked($day, $start, $end)) {
            return false;
        }

        return $this->firstAvailableTables($day->toDateString(), $start->format('H:i'), $end->format('H:i'), $partySize)
            ->sum('capacity') >= $partySize;
    }

    private function busyTableIds(CarbonImmutable $day, CarbonImmutable $start, CarbonImmutable $end): array
    {
        $reservationTableIds = Reservation::query()
            ->whereDate('reservation_date', $day->toDateString())
            ->whereIn('status', [ReservationStatus::Pending->value, ReservationStatus::Confirmed->value])
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
