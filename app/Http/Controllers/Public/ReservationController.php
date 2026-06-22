<?php

namespace App\Http\Controllers\Public;

use App\Enums\ReservationOrigin;
use App\Enums\ReservationStatus;
use App\Http\Controllers\Controller;
use App\Http\Requests\StoreReservationRequest;
use App\Models\Area;
use App\Models\Reservation;
use App\Notifications\ReservationCustomerNotification;
use App\Services\AvailabilityService;
use App\Services\ReservationService;
use Carbon\CarbonImmutable;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Notification;
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
            'areas' => Area::query()
                ->where('is_active', true)
                ->orderBy('sort_order')
                ->get(['id', 'name']),
        ]);
    }

    public function availability(Request $request, AvailabilityService $availability): JsonResponse
    {
        $validated = $request->validate([
            'date' => ['required', 'date'],
            'party_size' => ['nullable', 'integer', 'min:1', 'max:20'],
            'preferred_area_id' => ['nullable', 'integer', 'exists:areas,id'],
        ]);

        return response()->json([
            'slots' => $availability->publicSlots(
                $validated['date'],
                $validated['party_size'] ?? null,
                $validated['preferred_area_id'] ?? null,
            ),
        ]);
    }

    public function store(StoreReservationRequest $request, ReservationService $reservations): RedirectResponse
    {
        $data = $request->validated();
        $settings = app(AvailabilityService::class)->settings();
        $data['status'] = $this->initialWebStatus($data, $settings);

        try {
            $reservation = $reservations->create($data, ReservationOrigin::Web);
        } catch (RuntimeException) {
            return back()->withErrors([
                'start_time' => __('The selected time is no longer available.'),
            ])->withInput();
        }

        $this->notifyCustomerAfterCreation($reservation);

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

    public function verifyEmail(string $token): RedirectResponse
    {
        $reservation = Reservation::query()
            ->where('public_token', $token)
            ->where('status', ReservationStatus::PendingEmailVerification->value)
            ->firstOrFail();

        $settings = app(AvailabilityService::class)->settings();
        $expiresAt = $reservation->created_at->copy()->addMinutes((int) $settings->email_verification_expiration_minutes);

        if (now()->greaterThan($expiresAt)) {
            return redirect()->route('home')->withErrors([
                'reservation' => 'El enlace de confirmacion ha caducado.',
            ]);
        }

        $reservation->update([
            'status' => ReservationStatus::Confirmed,
            'email_verified_at' => now(),
            'confirmed_at' => now(),
        ]);

        $this->notifyCustomer($reservation, 'confirmed');

        return redirect()->route('reservations.confirmation', [
            'code' => $reservation->confirmation_code,
        ]);
    }

    public function cancelForm(string $token): Response
    {
        $reservation = Reservation::query()
            ->where('public_token', $token)
            ->firstOrFail();

        return Inertia::render('ReservationCancel', [
            'reservation' => [
                'customer_name' => $reservation->customer_name,
                'reservation_date' => $reservation->reservation_date->format('Y-m-d'),
                'start_time' => substr((string) $reservation->start_time, 0, 5),
                'party_size' => $reservation->party_size,
                'status' => $reservation->status->value,
            ],
            'canCancel' => $this->canCancelPublicly($reservation),
            'token' => $token,
        ]);
    }

    public function cancel(string $token, Request $request): RedirectResponse
    {
        $reservation = Reservation::query()
            ->where('public_token', $token)
            ->firstOrFail();

        if (! $this->canCancelPublicly($reservation)) {
            return back()->withErrors([
                'cancel' => 'Para cancelar esta reserva, por favor llama directamente al restaurante.',
            ]);
        }

        $reservation->update([
            'status' => ReservationStatus::Cancelled,
            'cancelled_at' => now(),
            'cancel_reason' => $request->input('cancel_reason'),
        ]);

        $this->notifyCustomer($reservation, 'cancelled');

        return redirect()->route('home');
    }

    private function initialWebStatus(array $data, object $settings): ReservationStatus
    {
        if (
            $settings->large_party_requires_manual_confirmation
            && (int) $data['party_size'] >= (int) $settings->large_party_threshold
        ) {
            return ReservationStatus::Pending;
        }

        return match ($settings->web_reservation_confirmation_mode) {
            'auto' => ReservationStatus::Confirmed,
            'auto_with_email_verification' => ReservationStatus::PendingEmailVerification,
            default => ReservationStatus::Pending,
        };
    }

    private function notifyCustomerAfterCreation(Reservation $reservation): void
    {
        if (! $reservation->customer_email) {
            return;
        }

        if ($reservation->status === ReservationStatus::PendingEmailVerification) {
            $this->notifyCustomer($reservation, 'verification', route('reservations.verify-email', $reservation->public_token));

            return;
        }

        $this->notifyCustomer(
            $reservation,
            $reservation->status === ReservationStatus::Confirmed ? 'confirmed' : 'pending',
            route('reservations.cancel.form', $reservation->public_token),
        );
    }

    private function notifyCustomer(Reservation $reservation, string $type, ?string $actionUrl = null): void
    {
        if (! $reservation->customer_email) {
            return;
        }

        Notification::route('mail', $reservation->customer_email)
            ->notify(new ReservationCustomerNotification($reservation, $type, $actionUrl));
    }

    private function canCancelPublicly(Reservation $reservation): bool
    {
        $settings = app(AvailabilityService::class)->settings();

        if (! $settings->allow_public_cancellations || $reservation->status === ReservationStatus::Cancelled) {
            return false;
        }

        $startsAt = CarbonImmutable::parse(
            $reservation->reservation_date->format('Y-m-d').' '.substr((string) $reservation->start_time, 0, 5),
            $settings->timezone,
        );

        return $startsAt->diffInHours(now($settings->timezone), false) <= -((int) $settings->min_hours_before_public_cancellation);
    }
}
