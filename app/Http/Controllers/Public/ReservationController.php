<?php

namespace App\Http\Controllers\Public;

use App\Enums\ReservationOrigin;
use App\Http\Controllers\Controller;
use App\Http\Requests\StoreReservationRequest;
use App\Services\AvailabilityService;
use App\Services\ReservationService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;
use RuntimeException;

class ReservationController extends Controller
{
    public function create(AvailabilityService $availability): Response
    {
        $locale = request('lang', app()->getLocale());
        $locale = in_array($locale, ['es', 'en'], true) ? $locale : 'es';

        return Inertia::render('ReservationCreate', [
            'locale' => $locale,
            'settings' => $availability->settings(),
        ]);
    }

    public function availability(Request $request, AvailabilityService $availability): JsonResponse
    {
        $validated = $request->validate([
            'date' => ['required', 'date'],
            'party_size' => ['nullable', 'integer', 'min:1', 'max:20'],
        ]);

        return response()->json([
            'slots' => $availability->publicSlots($validated['date'], $validated['party_size'] ?? null),
        ]);
    }

    public function store(StoreReservationRequest $request, ReservationService $reservations): RedirectResponse
    {
        try {
            $reservation = $reservations->create($request->validated(), ReservationOrigin::Web);
        } catch (RuntimeException) {
            return back()->withErrors([
                'start_time' => __('The selected time is no longer available.'),
            ])->withInput();
        }

        return redirect()->route('reservations.confirmation', [
            'code' => $reservation->confirmation_code,
        ]);
    }

    public function confirmation(string $code): Response
    {
        return Inertia::render('ReservationSuccess', [
            'confirmationCode' => $code,
        ]);
    }
}
