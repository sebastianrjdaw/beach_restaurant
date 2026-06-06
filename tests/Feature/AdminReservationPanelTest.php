<?php

namespace Tests\Feature;

use App\Models\User;
use Database\Seeders\RestaurantSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
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
}
