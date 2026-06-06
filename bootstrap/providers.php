<?php

use App\Providers\AppServiceProvider;
use App\Providers\Filament\AdminPanelProvider;

return [
    AppServiceProvider::class,
    Inertia\ServiceProvider::class,
    Livewire\LivewireServiceProvider::class,
    Filament\Support\SupportServiceProvider::class,
    Filament\Actions\ActionsServiceProvider::class,
    Filament\Notifications\NotificationsServiceProvider::class,
    Filament\Infolists\InfolistsServiceProvider::class,
    Filament\Forms\FormsServiceProvider::class,
    Filament\Tables\TablesServiceProvider::class,
    Filament\Widgets\WidgetsServiceProvider::class,
    Filament\FilamentServiceProvider::class,
    AdminPanelProvider::class,
];
