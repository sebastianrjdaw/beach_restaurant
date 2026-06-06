<?php

namespace App\Filament\Widgets;

use App\Enums\ReservationStatus;
use App\Models\Reservation;
use Filament\Widgets\StatsOverviewWidget as BaseWidget;
use Filament\Widgets\StatsOverviewWidget\Stat;

class ReservationStats extends BaseWidget
{
    protected function getStats(): array
    {
        $todayReservations = Reservation::query()
            ->whereDate('reservation_date', today());

        return [
            Stat::make(
                'Pendientes',
                Reservation::query()->where('status', ReservationStatus::Pending)->count(),
            )
                ->description('Reservas solicitadas sin confirmar')
                ->color('warning'),
            Stat::make('Reservas de hoy', (clone $todayReservations)->count())
                ->description(today()->format('d/m/Y'))
                ->color('success'),
            Stat::make('Comensales hoy', (clone $todayReservations)->sum('party_size'))
                ->description('Personas previstas')
                ->color('info'),
        ];
    }
}
