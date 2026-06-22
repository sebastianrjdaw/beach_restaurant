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
            'web_reservation_confirmation_mode' => $settings->web_reservation_confirmation_mode,
            'email_verification_expiration_minutes' => $settings->email_verification_expiration_minutes,
            'allow_public_cancellations' => $settings->allow_public_cancellations,
            'min_hours_before_public_cancellation' => $settings->min_hours_before_public_cancellation,
            'strict_area_preference' => $settings->strict_area_preference,
            'min_guests_online' => $settings->min_guests_online,
            'max_guests_online' => $settings->max_guests_online,
            'large_party_requires_manual_confirmation' => $settings->large_party_requires_manual_confirmation,
            'large_party_threshold' => $settings->large_party_threshold,
            'min_minutes_before_reservation' => $settings->min_minutes_before_reservation,
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
                Forms\Components\Section::make('Confirmacion y reglas online')
                    ->columns([
                        'default' => 1,
                        'md' => 3,
                    ])
                    ->schema([
                        Forms\Components\Select::make('web_reservation_confirmation_mode')
                            ->label('Modo confirmacion web')
                            ->required()
                            ->options([
                                'manual' => 'Manual',
                                'auto' => 'Automatica',
                                'auto_with_email_verification' => 'Automatica con verificacion email',
                            ]),
                        Forms\Components\TextInput::make('email_verification_expiration_minutes')
                            ->label('Caducidad verificacion')
                            ->suffix('min')
                            ->required()
                            ->numeric()
                            ->minValue(5)
                            ->maxValue(1440),
                        Forms\Components\TextInput::make('min_minutes_before_reservation')
                            ->label('Antelacion minima')
                            ->suffix('min')
                            ->required()
                            ->numeric()
                            ->minValue(0)
                            ->maxValue(1440),
                        Forms\Components\TextInput::make('min_guests_online')
                            ->label('Min. comensales online')
                            ->required()
                            ->numeric()
                            ->minValue(1)
                            ->maxValue(50),
                        Forms\Components\TextInput::make('max_guests_online')
                            ->label('Max. comensales online')
                            ->required()
                            ->numeric()
                            ->minValue(1)
                            ->maxValue(100),
                        Forms\Components\TextInput::make('large_party_threshold')
                            ->label('Grupo grande desde')
                            ->required()
                            ->numeric()
                            ->minValue(1)
                            ->maxValue(100),
                        Forms\Components\Toggle::make('large_party_requires_manual_confirmation')
                            ->label('Grupos grandes requieren revision manual'),
                        Forms\Components\Toggle::make('strict_area_preference')
                            ->label('Zona preferida estricta'),
                    ]),
                Forms\Components\Section::make('Cancelacion publica')
                    ->columns([
                        'default' => 1,
                        'md' => 2,
                    ])
                    ->schema([
                        Forms\Components\Toggle::make('allow_public_cancellations')
                            ->label('Permitir cancelaciones publicas'),
                        Forms\Components\TextInput::make('min_hours_before_public_cancellation')
                            ->label('Margen minimo para cancelar')
                            ->suffix('h')
                            ->required()
                            ->numeric()
                            ->minValue(0)
                            ->maxValue(168),
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
