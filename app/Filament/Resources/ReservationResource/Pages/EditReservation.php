<?php

namespace App\Filament\Resources\ReservationResource\Pages;

use App\Enums\ReservationStatus;
use App\Filament\Resources\ReservationResource;
use Filament\Actions;
use Filament\Resources\Pages\EditRecord;

class EditReservation extends EditRecord
{
    protected static string $resource = ReservationResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\Action::make('confirm')
                ->label('Confirmar')
                ->icon('heroicon-o-check-circle')
                ->color('success')
                ->visible(fn (): bool => $this->record->status === ReservationStatus::Pending)
                ->action(function (): void {
                    $this->record->update([
                        'status' => ReservationStatus::Confirmed,
                        'confirmed_at' => now(),
                    ]);
                    $this->refreshFormData(['status', 'confirmed_at']);
                }),
            Actions\DeleteAction::make(),
        ];
    }

    protected function mutateFormDataBeforeSave(array $data): array
    {
        if (($data['status'] ?? null) === ReservationStatus::Confirmed->value && $this->record->confirmed_at === null) {
            $data['confirmed_at'] = now();
        }

        return $data;
    }
}
