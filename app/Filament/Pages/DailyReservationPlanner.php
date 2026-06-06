<?php

namespace App\Filament\Pages;

use App\Filament\Resources\ReservationResource;
use App\Models\RestaurantSetting;
use App\Services\AvailabilityService;
use Filament\Forms;
use Filament\Forms\Concerns\InteractsWithForms;
use Filament\Forms\Contracts\HasForms;
use Filament\Forms\Form;
use Filament\Pages\Page;

class DailyReservationPlanner extends Page implements HasForms
{
    use InteractsWithForms;

    protected static ?string $navigationIcon = 'heroicon-o-calendar';

    protected static ?string $navigationLabel = 'Planning diario';

    protected static ?string $title = 'Planning diario de reservas';

    protected static ?int $navigationSort = 2;

    protected static string $view = 'filament.pages.daily-reservation-planner';

    public ?array $data = [];

    public function mount(): void
    {
        $this->form->fill([
            'date' => today()->toDateString(),
        ]);
    }

    public function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\DatePicker::make('date')
                    ->label('Dia')
                    ->native(false)
                    ->minDate(today()->toDateString())
                    ->maxDate(today()->addDays((int) $this->settings()->max_days_in_advance)->toDateString())
                    ->required()
                    ->live(),
            ])
            ->columns([
                'default' => 1,
                'md' => 3,
            ])
            ->statePath('data');
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

    private function selectedDate(): string
    {
        return $this->data['date'] ?? today()->toDateString();
    }

    private function settings(): RestaurantSetting
    {
        return RestaurantSetting::query()->firstOrFail();
    }
}
