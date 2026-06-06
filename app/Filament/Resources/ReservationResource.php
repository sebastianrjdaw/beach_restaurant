<?php

namespace App\Filament\Resources;

use App\Enums\ReservationOrigin;
use App\Enums\ReservationStatus;
use App\Filament\Resources\ReservationResource\Pages;
use App\Models\Reservation;
use App\Services\AvailabilityService;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Forms\Get;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;

class ReservationResource extends Resource
{
    protected static ?string $model = Reservation::class;

    protected static ?string $navigationIcon = 'heroicon-o-calendar-days';

    protected static ?string $navigationLabel = 'Reservas';

    protected static ?string $modelLabel = 'reserva';

    protected static ?string $pluralModelLabel = 'reservas';

    protected static ?int $navigationSort = 1;

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\Section::make('Datos de la reserva')
                    ->columns([
                        'default' => 1,
                        'md' => 3,
                    ])
                    ->schema([
                        Forms\Components\DatePicker::make('reservation_date')
                            ->label('Fecha')
                            ->required()
                            ->native(false)
                            ->minDate(now()->toDateString())
                            ->default(now()->toDateString())
                            ->live()
                            ->disabledOn('edit'),
                        Forms\Components\TextInput::make('party_size')
                            ->label('Personas')
                            ->required()
                            ->numeric()
                            ->minValue(1)
                            ->maxValue(20)
                            ->default(2)
                            ->live()
                            ->disabledOn('edit'),
                        Forms\Components\Select::make('start_time')
                            ->label('Hora')
                            ->required()
                            ->options(fn (Get $get): array => self::availableSlotOptions($get))
                            ->helperText(fn (Get $get): string => self::slotHelperText($get))
                            ->disabledOn('edit'),
                        Forms\Components\TextInput::make('end_time')
                            ->label('Fin')
                            ->disabled()
                            ->dehydrated(false)
                            ->visibleOn('edit'),
                        Forms\Components\Select::make('status')
                            ->label('Estado')
                            ->required()
                            ->options(self::statusOptions())
                            ->default(ReservationStatus::Pending->value),
                        Forms\Components\Select::make('origin')
                            ->label('Origen')
                            ->options(self::originOptions())
                            ->default(ReservationOrigin::Phone->value)
                            ->required(),
                    ]),
                Forms\Components\Section::make('Cliente')
                    ->columns([
                        'default' => 1,
                        'md' => 2,
                    ])
                    ->schema([
                        Forms\Components\TextInput::make('customer_name')
                            ->label('Nombre')
                            ->required()
                            ->maxLength(120),
                        Forms\Components\TextInput::make('customer_phone')
                            ->label('Telefono')
                            ->tel()
                            ->maxLength(40),
                        Forms\Components\TextInput::make('customer_email')
                            ->label('Email')
                            ->email()
                            ->maxLength(255),
                        Forms\Components\Select::make('locale')
                            ->label('Idioma detectado')
                            ->options([
                                'es' => 'ES / nacional',
                                'en' => 'EN / extranjero',
                            ])
                            ->default('es')
                            ->required(),
                    ]),
                Forms\Components\Section::make('Notas')
                    ->schema([
                        Forms\Components\Textarea::make('comments')
                            ->label('Comentarios, alergias o preferencias')
                            ->rows(4)
                            ->maxLength(1000)
                            ->columnSpanFull(),
                    ]),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->defaultSort('reservation_date')
            ->columns([
                Tables\Columns\TextColumn::make('reservation_date')
                    ->label('Fecha')
                    ->date('d/m/Y')
                    ->sortable(),
                Tables\Columns\TextColumn::make('start_time')
                    ->label('Hora')
                    ->time('H:i')
                    ->sortable(),
                Tables\Columns\TextColumn::make('customer_name')
                    ->label('Cliente')
                    ->searchable()
                    ->description(fn (Reservation $record): ?string => $record->customer_phone),
                Tables\Columns\TextColumn::make('party_size')
                    ->label('Pers.')
                    ->alignCenter()
                    ->sortable(),
                Tables\Columns\TextColumn::make('status')
                    ->label('Estado')
                    ->badge()
                    ->formatStateUsing(fn (ReservationStatus $state): string => self::statusOptions()[$state->value] ?? $state->value)
                    ->color(fn (ReservationStatus $state): string => match ($state) {
                        ReservationStatus::Pending => 'warning',
                        ReservationStatus::Confirmed => 'success',
                        ReservationStatus::Cancelled => 'danger',
                        ReservationStatus::Completed => 'gray',
                        ReservationStatus::NoShow => 'danger',
                    })
                    ->sortable(),
                Tables\Columns\TextColumn::make('origin')
                    ->label('Origen')
                    ->formatStateUsing(fn (ReservationOrigin $state): string => self::originOptions()[$state->value] ?? $state->value)
                    ->toggleable(isToggledHiddenByDefault: true),
                Tables\Columns\TextColumn::make('created_at')
                    ->label('Solicitada')
                    ->since()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
            ])
            ->filters([
                Tables\Filters\SelectFilter::make('status')
                    ->label('Estado')
                    ->options(self::statusOptions()),
                Tables\Filters\Filter::make('today')
                    ->label('Hoy')
                    ->query(fn (Builder $query): Builder => $query->whereDate('reservation_date', today())),
                Tables\Filters\Filter::make('date_range')
                    ->label('Rango de fechas')
                    ->form([
                        Forms\Components\DatePicker::make('from')->label('Desde'),
                        Forms\Components\DatePicker::make('until')->label('Hasta'),
                    ])
                    ->query(function (Builder $query, array $data): Builder {
                        return $query
                            ->when($data['from'] ?? null, fn (Builder $query, string $date): Builder => $query->whereDate('reservation_date', '>=', $date))
                            ->when($data['until'] ?? null, fn (Builder $query, string $date): Builder => $query->whereDate('reservation_date', '<=', $date));
                    }),
            ])
            ->actions([
                Tables\Actions\Action::make('confirm')
                    ->label('Confirmar')
                    ->icon('heroicon-o-check-circle')
                    ->color('success')
                    ->visible(fn (Reservation $record): bool => $record->status === ReservationStatus::Pending)
                    ->action(fn (Reservation $record) => $record->update([
                        'status' => ReservationStatus::Confirmed,
                        'confirmed_at' => now(),
                    ])),
                Tables\Actions\Action::make('complete')
                    ->label('Completada')
                    ->icon('heroicon-o-flag')
                    ->color('gray')
                    ->visible(fn (Reservation $record): bool => in_array($record->status, [ReservationStatus::Pending, ReservationStatus::Confirmed], true))
                    ->action(fn (Reservation $record) => $record->update([
                        'status' => ReservationStatus::Completed,
                    ])),
                Tables\Actions\Action::make('no_show')
                    ->label('No show')
                    ->icon('heroicon-o-user-minus')
                    ->color('danger')
                    ->visible(fn (Reservation $record): bool => in_array($record->status, [ReservationStatus::Pending, ReservationStatus::Confirmed], true))
                    ->requiresConfirmation()
                    ->action(fn (Reservation $record) => $record->update([
                        'status' => ReservationStatus::NoShow,
                    ])),
                Tables\Actions\Action::make('cancel')
                    ->label('Cancelar')
                    ->icon('heroicon-o-x-circle')
                    ->color('danger')
                    ->visible(fn (Reservation $record): bool => in_array($record->status, [ReservationStatus::Pending, ReservationStatus::Confirmed], true))
                    ->requiresConfirmation()
                    ->action(fn (Reservation $record) => $record->update([
                        'status' => ReservationStatus::Cancelled,
                    ])),
                Tables\Actions\EditAction::make(),
            ])
            ->bulkActions([
                Tables\Actions\BulkActionGroup::make([
                    Tables\Actions\DeleteBulkAction::make(),
                ]),
            ]);
    }

    public static function getNavigationBadge(): ?string
    {
        $count = Reservation::query()
            ->where('status', ReservationStatus::Pending)
            ->count();

        return $count > 0 ? (string) $count : null;
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListReservations::route('/'),
            'create' => Pages\CreateReservation::route('/create'),
            'edit' => Pages\EditReservation::route('/{record}/edit'),
        ];
    }

    private static function availableSlotOptions(Get $get): array
    {
        $date = $get('reservation_date');
        $partySize = (int) ($get('party_size') ?: 1);

        if (! $date) {
            return [];
        }

        return collect(app(AvailabilityService::class)->bookableSlots($date))
            ->mapWithKeys(fn (array $slot): array => [$slot['time'] => "{$slot['label']} · {$slot['shift']}"])
            ->all();
    }

    private static function slotHelperText(Get $get): string
    {
        $date = $get('reservation_date');
        $time = $get('start_time');
        $partySize = (int) ($get('party_size') ?: 1);

        if (! $date || ! $time) {
            return 'Selecciona fecha y personas para ver los turnos registrados.';
        }

        $warnings = app(AvailabilityService::class)->operationalLimitWarnings($date, $time, $partySize);

        if ($warnings === []) {
            return 'Turno dentro de los limites operativos configurados.';
        }

        return implode(' ', $warnings).' Al ser una reserva telefonica o interna, puedes crearla igualmente.';
    }

    public static function statusOptions(): array
    {
        return [
            ReservationStatus::Pending->value => 'Pendiente',
            ReservationStatus::Confirmed->value => 'Confirmada',
            ReservationStatus::Cancelled->value => 'Cancelada',
            ReservationStatus::Completed->value => 'Completada',
            ReservationStatus::NoShow->value => 'No show',
        ];
    }

    public static function originOptions(): array
    {
        return [
            ReservationOrigin::Web->value => 'Web',
            ReservationOrigin::Phone->value => 'Telefono',
            ReservationOrigin::Admin->value => 'Admin',
        ];
    }
}
