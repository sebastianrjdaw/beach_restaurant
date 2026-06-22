<?php

namespace Tests\Feature;

use App\Enums\ReservationStatus;
use App\Http\Middleware\VerifyCsrfToken;
use App\Models\Area;
use App\Models\OpeningHour;
use App\Models\Reservation;
use App\Models\RestaurantSetting;
use App\Models\RestaurantTable;
use App\Notifications\ReservationCustomerNotification;
use App\Services\ReservationService;
use Carbon\CarbonImmutable;
use Illuminate\Foundation\Http\Middleware\ValidateCsrfToken;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Notification;
use Illuminate\Support\Str;
use Tests\TestCase;

class PublicReservationPhaseOneTest extends TestCase
{
    use RefreshDatabase;

    public function test_web_reservation_manual_mode_stays_pending(): void
    {
        Notification::fake();
        $this->withoutMiddleware(ValidateCsrfToken::class);
        $this->seedAvailabilityData(['web_reservation_confirmation_mode' => 'manual']);

        $this->post('/reservar', $this->reservationPayload())->assertRedirect();

        $this->assertDatabaseHas('reservations', [
            'customer_email' => 'ada@example.com',
            'status' => ReservationStatus::Pending->value,
        ]);
        Notification::assertSentOnDemand(ReservationCustomerNotification::class);
    }

    public function test_web_reservation_auto_mode_is_confirmed(): void
    {
        $this->withoutMiddleware(ValidateCsrfToken::class);
        $this->seedAvailabilityData(['web_reservation_confirmation_mode' => 'auto']);

        $this->post('/reservar', $this->reservationPayload())->assertRedirect();

        $this->assertDatabaseHas('reservations', [
            'customer_email' => 'ada@example.com',
            'status' => ReservationStatus::Confirmed->value,
        ]);
    }

    public function test_web_reservation_email_verification_mode_waits_for_token(): void
    {
        $this->withoutMiddleware(ValidateCsrfToken::class);
        $this->seedAvailabilityData(['web_reservation_confirmation_mode' => 'auto_with_email_verification']);

        $this->post('/reservar', $this->reservationPayload())->assertRedirect();

        $reservation = Reservation::query()->firstOrFail();
        $this->assertSame(ReservationStatus::PendingEmailVerification, $reservation->status);

        $this->get("/reservas/verificar-email/{$reservation->public_token}")->assertRedirect();
        $this->assertSame(ReservationStatus::Confirmed, $reservation->refresh()->status);
    }

    public function test_public_reservation_respects_minimum_lead_time(): void
    {
        $this->withoutMiddleware(ValidateCsrfToken::class);
        $this->seedAvailabilityData(['min_minutes_before_reservation' => 60]);

        $this->post('/reservar', $this->reservationPayload([
            'reservation_date' => CarbonImmutable::now('Europe/Madrid')->toDateString(),
            'start_time' => '00:01',
        ]))->assertSessionHasErrors('start_time');
    }

    public function test_large_party_requires_manual_confirmation_even_in_auto_mode(): void
    {
        $this->withoutMiddleware(ValidateCsrfToken::class);
        $this->seedAvailabilityData([
            'web_reservation_confirmation_mode' => 'auto',
            'large_party_requires_manual_confirmation' => true,
            'large_party_threshold' => 8,
            'max_guests_online' => 12,
        ], tableCapacity: 12);

        $this->post('/reservar', $this->reservationPayload(['party_size' => 9]))->assertRedirect();

        $this->assertDatabaseHas('reservations', [
            'party_size' => 9,
            'status' => ReservationStatus::Pending->value,
        ]);
    }

    public function test_preferred_area_is_saved(): void
    {
        $this->withoutMiddleware(ValidateCsrfToken::class);
        $area = $this->seedAvailabilityData();

        $this->post('/reservar', $this->reservationPayload([
            'preferred_area_id' => $area->id,
        ]))->assertRedirect();

        $this->assertDatabaseHas('reservations', [
            'preferred_area_id' => $area->id,
        ]);
    }

    public function test_public_reservation_can_be_cancelled_with_token(): void
    {
        $this->withoutMiddleware(ValidateCsrfToken::class);
        $this->seedAvailabilityData([
            'allow_public_cancellations' => true,
            'min_hours_before_public_cancellation' => 3,
        ]);
        $reservation = app(ReservationService::class)->create($this->reservationPayload([
            'reservation_date' => '2026-06-12',
            'start_time' => '13:00',
            'status' => ReservationStatus::Confirmed,
        ]));

        $this->post("/reservas/{$reservation->public_token}/cancelar", [
            'cancel_reason' => 'Cambio de planes',
        ])->assertRedirect();

        $this->assertSame(ReservationStatus::Cancelled, $reservation->refresh()->status);
    }

    public function test_public_reservation_cannot_be_cancelled_too_late(): void
    {
        CarbonImmutable::setTestNow(CarbonImmutable::parse('2026-06-12 11:00', 'Europe/Madrid'));
        $this->withoutMiddleware(ValidateCsrfToken::class);
        $this->seedAvailabilityData([
            'allow_public_cancellations' => true,
            'min_hours_before_public_cancellation' => 3,
        ]);
        $reservation = app(ReservationService::class)->create($this->reservationPayload([
            'reservation_date' => '2026-06-12',
            'start_time' => '13:00',
            'status' => ReservationStatus::Confirmed,
        ]));

        $this->post("/reservas/{$reservation->public_token}/cancelar")->assertSessionHasErrors('cancel');
        $this->assertSame(ReservationStatus::Confirmed, $reservation->refresh()->status);
        CarbonImmutable::setTestNow();
    }

    private function seedAvailabilityData(array $settings = [], int $tableCapacity = 4): Area
    {
        RestaurantSetting::query()->create([
            'name' => 'Test Restaurant',
            'default_reservation_duration' => 90,
            'reservation_interval' => 30,
            'max_days_in_advance' => 30,
            'max_reservations_per_slot' => 10,
            'max_guests_per_slot' => 40,
            'web_reservation_confirmation_mode' => 'manual',
            'email_verification_expiration_minutes' => 30,
            'allow_public_cancellations' => true,
            'min_hours_before_public_cancellation' => 3,
            'strict_area_preference' => false,
            'min_guests_online' => 1,
            'max_guests_online' => 10,
            'large_party_requires_manual_confirmation' => true,
            'large_party_threshold' => 8,
            'min_minutes_before_reservation' => 0,
            'timezone' => 'Europe/Madrid',
            'default_locale' => 'es',
            'locales' => ['es', 'en'],
            ...$settings,
        ]);
        OpeningHour::query()->create([
            'weekday' => 5,
            'opens_at' => '13:00',
            'closes_at' => '16:00',
            'is_closed' => false,
            'label' => 'Comida',
        ]);
        $area = Area::query()->create(['name' => ['es' => 'Terraza', 'en' => 'Terrace']]);
        RestaurantTable::query()->create(['area_id' => $area->id, 'name' => 'T1', 'capacity' => $tableCapacity]);

        return $area;
    }

    private function reservationPayload(array $overrides = []): array
    {
        return [
            'reservation_date' => '2026-06-12',
            'start_time' => '13:00',
            'party_size' => 2,
            'preferred_area_id' => null,
            'customer_name' => 'Ada Lovelace',
            'customer_email' => 'ada@example.com',
            'customer_phone' => '+34900123456',
            'locale' => 'es',
            'comments' => 'Sin gluten',
            ...$overrides,
        ];
    }
}
