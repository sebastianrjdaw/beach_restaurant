<?php

namespace Tests\Unit;

use App\Models\Area;
use App\Models\OpeningHour;
use App\Models\Reservation;
use App\Models\RestaurantSetting;
use App\Models\RestaurantTable;
use App\Services\AvailabilityService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class AvailabilityServiceTest extends TestCase
{
    use RefreshDatabase;

    public function test_it_returns_slots_when_capacity_exists(): void
    {
        $this->seedAvailabilityData();

        $slots = app(AvailabilityService::class)->availableSlots('2026-06-05', 2);

        $this->assertNotEmpty($slots);
        $this->assertContains('13:00', array_column($slots, 'time'));
    }

    public function test_it_hides_overlapping_slots_without_capacity(): void
    {
        $this->seedAvailabilityData();

        $table = RestaurantTable::query()->firstOrFail();
        $reservation = Reservation::query()->create([
            'reservation_date' => '2026-06-05',
            'start_time' => '13:00',
            'end_time' => '14:30',
            'party_size' => 2,
            'customer_name' => 'Existing Booking',
            'status' => 'confirmed',
            'origin' => 'phone',
            'confirmation_code' => 'ABC12345',
        ]);
        $reservation->tables()->sync([$table->id]);

        $slots = app(AvailabilityService::class)->availableSlots('2026-06-05', 2);

        $this->assertNotContains('13:00', array_column($slots, 'time'));
    }

    private function seedAvailabilityData(): void
    {
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
        RestaurantTable::query()->create(['area_id' => $area->id, 'name' => 'T1', 'capacity' => 2]);
    }
}
