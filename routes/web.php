<?php

use App\Http\Controllers\Public\ReservationController;
use App\Models\Menu;
use App\Services\AvailabilityService;
use Illuminate\Support\Facades\App;
use Illuminate\Support\Facades\Route;
use Inertia\Inertia;

Route::get('/', function (AvailabilityService $availability) {
    $locale = request('lang', App::getLocale());
    $locale = in_array($locale, ['es', 'en'], true) ? $locale : 'es';
    App::setLocale($locale);

    return Inertia::render('Home', [
        'locale' => $locale,
        'settings' => $availability->settings(),
        'menus' => Menu::query()
            ->where('is_active', true)
            ->with(['categories.items' => fn ($query) => $query->where('is_available', true)->orderBy('sort_order')])
            ->get(),
    ]);
})->name('home');

Route::get('/reservar', [ReservationController::class, 'create'])->name('reservations.create');
Route::post('/reservar', [ReservationController::class, 'store'])->name('reservations.store');
Route::get('/reservas/disponibilidad', [ReservationController::class, 'availability'])->name('reservations.availability');
Route::get('/reservas/confirmacion/{code}', [ReservationController::class, 'confirmation'])->name('reservations.confirmation');
Route::get('/reservas/verificar-email/{token}', [ReservationController::class, 'verifyEmail'])->name('reservations.verify-email');
Route::get('/reservas/{token}/cancelar', [ReservationController::class, 'cancelForm'])->name('reservations.cancel.form');
Route::post('/reservas/{token}/cancelar', [ReservationController::class, 'cancel'])->name('reservations.cancel');
