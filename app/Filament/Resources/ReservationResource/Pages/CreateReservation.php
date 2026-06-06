<?php

namespace App\Filament\Resources\ReservationResource\Pages;

use App\Enums\ReservationOrigin;
use App\Enums\ReservationStatus;
use App\Filament\Resources\ReservationResource;
use App\Models\Reservation;
use App\Services\ReservationService;
use Filament\Notifications\Notification;
use Filament\Resources\Pages\CreateRecord;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Validation\ValidationException;
use RuntimeException;

class CreateReservation extends CreateRecord
{
    protected static string $resource = ReservationResource::class;

    protected function handleRecordCreation(array $data): Model
    {
        try {
            /** @var Reservation $reservation */
            $reservation = app(ReservationService::class)->create(
                $data,
                ReservationOrigin::tryFrom($data['origin'] ?? '') ?? ReservationOrigin::Admin,
            );
        } catch (RuntimeException) {
            throw ValidationException::withMessages([
                'start_time' => 'No hay disponibilidad para ese dia, hora y numero de personas.',
            ]);
        }

        if (($data['status'] ?? null) === ReservationStatus::Confirmed->value) {
            $reservation->update(['confirmed_at' => now()]);
        }

        Notification::make()
            ->title('Reserva creada')
            ->success()
            ->send();

        return $reservation;
    }
}
