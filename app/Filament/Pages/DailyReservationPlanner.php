<?php

namespace App\Filament\Pages;

use App\Enums\ReservationStatus;
use App\Filament\Resources\ReservationResource;
use App\Models\Reservation;
use App\Models\RestaurantSetting;
use App\Notifications\ReservationCustomerNotification;
use App\Services\AvailabilityService;
use Carbon\CarbonImmutable;
use Filament\Forms;
use Filament\Forms\Concerns\InteractsWithForms;
use Filament\Forms\Contracts\HasForms;
use Filament\Forms\Form;
use Filament\Notifications\Notification as FilamentNotification;
use Filament\Pages\Page;
use Illuminate\Support\Facades\Notification;

class DailyReservationPlanner extends Page implements HasForms
{
    use InteractsWithForms;

    protected static ?string $navigationIcon = 'heroicon-o-calendar';

    protected static ?string $navigationLabel = 'Planning diario';

    protected static ?string $title = 'Planning diario de reservas';

    protected static ?int $navigationSort = 2;

    protected static string $view = 'filament.pages.daily-reservation-planner';

    public array $moves = [];

    public ?int $editingReservationId = null;

    public ?string $planningDate = null;

    public function mount(): void
    {
        $this->planningDate = today()->toDateString();
        $this->form->fill([
            'planningDate' => $this->planningDate,
        ]);
    }

    public function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\DatePicker::make('planningDate')
                    ->label('Dia')
                    ->native(false)
                    ->format('Y-m-d')
                    ->displayFormat('d/m/Y')
                    ->minDate(today()->toDateString())
                    ->maxDate(today()->addDays((int) $this->settings()->max_days_in_advance)->toDateString())
                    ->required()
                    ->live()
                    ->afterStateUpdated(function (?string $state): void {
                        $this->planningDate = $this->normalizeDate($state);
                    }),
            ])
            ->columns([
                'default' => 1,
                'md' => 3,
            ]);
    }

    public function getPlanProperty(): array
    {
        return app(AvailabilityService::class)->dailyPlan($this->selectedDate());
    }

    public function getTotalsProperty(): array
    {
        $slots = collect($this->plan)->flatten(1);

        return [
            'reservations' => (int) $slots->sum('reservations_count'),
            'guests' => (int) $slots->sum('guests_count'),
            'tables' => (int) $slots->sum('tables_count'),
        ];
    }

    public function createReservationUrl(): string
    {
        return ReservationResource::getUrl('create');
    }

    public function openReservationModal(int $reservationId): void
    {
        $reservation = Reservation::query()->findOrFail($reservationId);
        $this->editingReservationId = $reservation->id;
        $this->moves[$reservation->id] = [
            'date' => $reservation->reservation_date->format('Y-m-d'),
            'time' => substr((string) $reservation->start_time, 0, 5),
            'notify_email' => false,
            'notify_whatsapp' => false,
        ];
    }

    public function closeReservationModal(): void
    {
        $this->editingReservationId = null;
    }

    public function getEditingReservationProperty(): ?Reservation
    {
        if ($this->editingReservationId === null) {
            return null;
        }

        return Reservation::query()
            ->with('tables.area')
            ->find($this->editingReservationId);
    }

    public function confirmReservation(int $reservationId): void
    {
        $reservation = Reservation::query()->findOrFail($reservationId);
        $reservation->update([
            'status' => ReservationStatus::Confirmed,
            'confirmed_at' => $reservation->confirmed_at ?? now(),
        ]);

        if ($reservation->customer_email) {
            Notification::route('mail', $reservation->customer_email)
                ->notify(new ReservationCustomerNotification($reservation->refresh(), 'confirmed', route('reservations.cancel.form', $reservation->public_token)));
        }

        $this->closeReservationModal();
        $this->refreshPlanner();

        FilamentNotification::make()->title('Reserva confirmada')->success()->send();
    }

    public function cancelReservation(int $reservationId): void
    {
        Reservation::query()->findOrFail($reservationId)->update([
            'status' => ReservationStatus::Cancelled,
            'cancelled_at' => now(),
        ]);

        $this->closeReservationModal();
        $this->refreshPlanner();

        FilamentNotification::make()->title('Reserva cancelada')->success()->send();
    }

    public function completeReservation(int $reservationId): void
    {
        Reservation::query()->findOrFail($reservationId)->update([
            'status' => ReservationStatus::Completed,
        ]);

        $this->closeReservationModal();
        $this->refreshPlanner();

        FilamentNotification::make()->title('Reserva completada')->success()->send();
    }

    public function markNoShow(int $reservationId): void
    {
        Reservation::query()->findOrFail($reservationId)->update([
            'status' => ReservationStatus::NoShow,
        ]);

        $this->closeReservationModal();
        $this->refreshPlanner();

        FilamentNotification::make()->title('Reserva marcada como no-show')->warning()->send();
    }

    public function moveReservation(int $reservationId): void
    {
        $reservation = Reservation::query()->with('tables')->findOrFail($reservationId);
        $move = $this->moves[$reservationId] ?? [];
        $date = $move['date'] ?? $reservation->reservation_date->format('Y-m-d');
        $startTime = $move['time'] ?? substr((string) $reservation->start_time, 0, 5);
        $notifyEmail = (bool) ($move['notify_email'] ?? false);
        $notifyWhatsapp = (bool) ($move['notify_whatsapp'] ?? false);
        $settings = $this->settings();
        $endTime = CarbonImmutable::parse($date.' '.$startTime, $settings->timezone)
            ->addMinutes((int) $settings->default_reservation_duration)
            ->format('H:i');

        $tables = app(AvailabilityService::class)->firstAvailableTables(
            $date,
            $startTime,
            $endTime,
            (int) $reservation->party_size,
            $reservation->preferred_area_id,
            (bool) $settings->strict_area_preference,
            $reservation->id,
        );

        if ($tables->sum('capacity') < (int) $reservation->party_size) {
            FilamentNotification::make()
                ->title('No hay disponibilidad para mover la reserva')
                ->danger()
                ->send();

            return;
        }

        $reservation->update([
            'reservation_date' => $date,
            'start_time' => $startTime,
            'end_time' => $endTime,
        ]);
        $reservation->tables()->sync($tables->pluck('id')->all());

        if ($notifyEmail && $reservation->customer_email) {
            Notification::route('mail', $reservation->customer_email)
                ->notify(new ReservationCustomerNotification($reservation->refresh(), 'changed', route('reservations.cancel.form', $reservation->public_token)));
        }

        if ($notifyWhatsapp) {
            FilamentNotification::make()
                ->title('WhatsApp queda preparado para futura integracion')
                ->body('Todavia no hay proveedor WhatsApp conectado.')
                ->info()
                ->send();
        }

        unset($this->moves[$reservationId]);

        $this->closeReservationModal();
        $this->refreshPlanner();

        FilamentNotification::make()->title('Reserva movida')->success()->send();
    }

    public function refreshPlanner(): void
    {
        $this->planningDate = $this->normalizeDate($this->planningDate);

        // Livewire rerenders computed planning data after this action.
    }

    private function selectedDate(): string
    {
        return $this->normalizeDate($this->planningDate);
    }

    private function settings(): RestaurantSetting
    {
        return RestaurantSetting::query()->firstOrFail();
    }

    private function normalizeDate(?string $date): string
    {
        return CarbonImmutable::parse($date ?: today()->toDateString(), $this->settings()->timezone)->toDateString();
    }
}
