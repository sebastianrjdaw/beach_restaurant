<?php

namespace App\Filament\Pages;

use App\Models\RestaurantSetting;
use Filament\Forms;
use Filament\Forms\Concerns\InteractsWithForms;
use Filament\Forms\Contracts\HasForms;
use Filament\Forms\Form;
use Filament\Notifications\Notification;
use Filament\Pages\Page;

class ReservationSettings extends Page implements HasForms
{
    use InteractsWithForms;

    protected static ?string $navigationIcon = 'heroicon-o-adjustments-horizontal';

    protected static ?string $navigationLabel = 'Configuracion reservas';

    protected static ?string $title = 'Configuracion de reservas';

    protected static ?int $navigationSort = 3;

    protected static string $view = 'filament.pages.reservation-settings';

    public ?array $data = [];

    public function mount(): void
    {
        $settings = RestaurantSetting::query()->firstOrFail();

        $this->form->fill([
            'default_reservation_duration' => $settings->default_reservation_duration,
            'reservation_interval' => $settings->reservation_interval,
            'max_days_in_advance' => $settings->max_days_in_advance,
            'max_reservations_per_slot' => $settings->max_reservations_per_slot,
            'max_guests_per_slot' => $settings->max_guests_per_slot,
        ]);
    }

    public function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\Section::make('Calendario y turnos')
                    ->columns([
                        'default' => 1,
                        'md' => 3,
                    ])
                    ->schema([
                        Forms\Components\TextInput::make('max_days_in_advance')
                            ->label('Dias maximos reservables')
                            ->helperText('Limita el calendario publico y la vista de planificacion.')
                            ->required()
                            ->numeric()
                            ->minValue(1)
                            ->maxValue(365),
                        Forms\Components\TextInput::make('default_reservation_duration')
                            ->label('Duracion reserva')
                            ->suffix('min')
                            ->required()
                            ->numeric()
                            ->minValue(30)
                            ->maxValue(300),
                        Forms\Components\TextInput::make('reservation_interval')
                            ->label('Intervalo entre horas')
                            ->suffix('min')
                            ->required()
                            ->numeric()
                            ->minValue(15)
                            ->maxValue(120),
                    ]),
                Forms\Components\Section::make('Limites por turno')
                    ->description('Estos limites controlan la venta online. En admin se avisa si se superan, pero se permite crear reservas telefonicas o internas.')
                    ->columns([
                        'default' => 1,
                        'md' => 2,
                    ])
                    ->schema([
                        Forms\Components\TextInput::make('max_reservations_per_slot')
                            ->label('Maximo reservas por hora')
                            ->numeric()
                            ->minValue(1)
                            ->maxValue(200),
                        Forms\Components\TextInput::make('max_guests_per_slot')
                            ->label('Maximo comensales por hora')
                            ->numeric()
                            ->minValue(1)
                            ->maxValue(500),
                    ]),
            ])
            ->statePath('data');
    }

    public function save(): void
    {
        RestaurantSetting::query()->firstOrFail()->update($this->form->getState());

        Notification::make()
            ->title('Configuracion guardada')
            ->success()
            ->send();
    }
}
