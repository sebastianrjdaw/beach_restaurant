<?php

namespace Tests\Feature;

use App\Enums\ReservationStatus;
use App\Filament\Pages\DailyReservationPlanner;
use App\Models\User;
use App\Services\ReservationService;
use Database\Seeders\RestaurantSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Livewire\Livewire;
use Tests\TestCase;

class AdminReservationPanelTest extends TestCase
{
    use RefreshDatabase;

    public function test_admin_reservation_pages_render(): void
    {
        $this->seed(RestaurantSeeder::class);

        $user = User::factory()->create();

        $this->actingAs($user)
            ->get('/admin/daily-reservation-planner')
            ->assertOk();

        $this->actingAs($user)
            ->get('/admin/reservation-settings')
            ->assertOk();

        $this->actingAs($user)
            ->get('/admin/reservations')
            ->assertOk();
    }

    public function test_daily_planner_renders_existing_reservations(): void
    {
        $this->seed(RestaurantSeeder::class);
        $date = '2026-06-07';

        $reservation = app(ReservationService::class)->create([
            'reservation_date' => $date,
            'start_time' => '20:30',
            'party_size' => 2,
            'customer_name' => 'Ada Lovelace',
            'customer_email' => 'ada@example.com',
            'customer_phone' => '+34900123456',
            'locale' => 'es',
            'comments' => 'Sin gluten',
            'status' => ReservationStatus::Pending,
        ]);

        $user = User::factory()->create();

        Livewire::actingAs($user)
            ->test(DailyReservationPlanner::class)
            ->set('planningDate', 'jun 7, 2026')
            ->assertSee('Ada Lovelace')
            ->assertSee('20:30')
            ->assertSee('2 pax')
            ->assertSee('Reservas')
            ->call('openReservationModal', $reservation->id)
            ->assertSee('Editar reserva')
            ->assertSee('Mover reserva')
            ->assertSee('Guardar cambio');
    }
}
