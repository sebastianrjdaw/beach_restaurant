<?php

namespace Tests\Feature;

use App\Models\Area;
use App\Models\OpeningHour;
use App\Models\RestaurantSetting;
use App\Models\RestaurantTable;
use Illuminate\Foundation\Http\Middleware\ValidateCsrfToken;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class CreateReservationTest extends TestCase
{
    use RefreshDatabase;

    public function test_public_reservation_can_be_created(): void
    {
        $this->withoutMiddleware(ValidateCsrfToken::class);

        RestaurantSetting::query()->create([
            'name' => 'Test Restaurant',
            'default_reservation_duration' => 90,
            'reservation_interval' => 30,
            'timezone' => 'Europe/Madrid',
            'default_locale' => 'es',
            'locales' => ['es', 'en'],
        ]);
        OpeningHour::query()->create([
            'weekday' => 5,
            'opens_at' => '13:00',
            'closes_at' => '16:00',
            'is_closed' => false,
        ]);
        $area = Area::query()->create(['name' => ['es' => 'Terraza', 'en' => 'Terrace']]);
        RestaurantTable::query()->create(['area_id' => $area->id, 'name' => 'T1', 'capacity' => 4]);

        $response = $this->post('/reservar', [
            'reservation_date' => '2026-06-12',
            'start_time' => '13:00',
            'party_size' => 2,
            'customer_name' => 'Ada Lovelace',
            'customer_email' => 'ada@example.com',
            'customer_phone' => '+34900123456',
            'locale' => 'es',
            'comments' => 'Sin gluten',
        ]);

        $response->assertRedirect();
        $this->assertDatabaseHas('reservations', [
            'customer_email' => 'ada@example.com',
            'origin' => 'web',
            'status' => 'pending',
        ]);
    }
}
