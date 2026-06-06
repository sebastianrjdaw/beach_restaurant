<?php

namespace Database\Seeders;

use App\Models\Area;
use App\Models\Menu;
use App\Models\OpeningHour;
use App\Models\RestaurantSetting;
use App\Models\RestaurantTable;
use Illuminate\Database\Seeder;

class RestaurantSeeder extends Seeder
{
    public function run(): void
    {
        RestaurantSetting::query()->updateOrCreate(
            ['id' => 1],
            [
                'name' => 'Mar Azul Beach',
                'description' => [
                    'es' => 'Un restaurante junto al mar con arroces, pescado fresco y cocteles al atardecer.',
                    'en' => 'A seaside restaurant with rice dishes, fresh fish and sunset cocktails.',
                ],
                'email' => 'hola@marazul.test',
                'phone' => '+34 900 123 456',
                'address' => 'Paseo Maritimo 12',
                'city' => 'Valencia',
                'country' => 'Spain',
                'latitude' => 39.4699,
                'longitude' => -0.3763,
                'default_reservation_duration' => 90,
                'reservation_interval' => 30,
                'max_days_in_advance' => 30,
                'timezone' => 'Europe/Madrid',
                'default_locale' => 'es',
                'locales' => ['es', 'en'],
            ],
        );

        foreach ([2, 3, 4, 5, 6, 7] as $weekday) {
            OpeningHour::query()->updateOrCreate(
                ['weekday' => $weekday, 'opens_at' => '13:00:00'],
                ['closes_at' => '23:00:00', 'is_closed' => false, 'label' => 'Lunch and dinner'],
            );
        }

        $terrace = Area::query()->updateOrCreate(
            ['id' => 1],
            ['name' => ['es' => 'Terraza', 'en' => 'Terrace'], 'is_active' => true, 'sort_order' => 1],
        );
        $salon = Area::query()->updateOrCreate(
            ['id' => 2],
            ['name' => ['es' => 'Salon', 'en' => 'Dining room'], 'is_active' => true, 'sort_order' => 2],
        );

        foreach ([['T1', 2], ['T2', 4], ['T3', 6]] as [$name, $capacity]) {
            RestaurantTable::query()->updateOrCreate(
                ['name' => $name],
                ['area_id' => $terrace->id, 'capacity' => $capacity, 'is_active' => true],
            );
        }

        foreach ([['S1', 2], ['S2', 4], ['S3', 8]] as [$name, $capacity]) {
            RestaurantTable::query()->updateOrCreate(
                ['name' => $name],
                ['area_id' => $salon->id, 'capacity' => $capacity, 'is_active' => true],
            );
        }

        $menu = Menu::query()->updateOrCreate(
            ['id' => 1],
            ['name' => ['es' => 'Carta principal', 'en' => 'Main menu'], 'is_active' => true],
        );

        $starters = $menu->categories()->updateOrCreate(
            ['id' => 1],
            ['name' => ['es' => 'Entrantes', 'en' => 'Starters'], 'sort_order' => 1],
        );
        $mains = $menu->categories()->updateOrCreate(
            ['id' => 2],
            ['name' => ['es' => 'Principales', 'en' => 'Mains'], 'sort_order' => 2],
        );

        $starters->items()->updateOrCreate(
            ['id' => 1],
            [
                'name' => ['es' => 'Ensalada de tomate', 'en' => 'Tomato salad'],
                'description' => ['es' => 'Tomate valenciano, bonito y aceite de oliva.', 'en' => 'Local tomato, tuna and olive oil.'],
                'price' => 12.50,
                'sort_order' => 1,
            ],
        );
        $mains->items()->updateOrCreate(
            ['id' => 2],
            [
                'name' => ['es' => 'Arroz del senyoret', 'en' => 'Seafood rice'],
                'description' => ['es' => 'Arroz seco con marisco pelado y fumet casero.', 'en' => 'Dry rice with peeled seafood and house stock.'],
                'price' => 19.00,
                'sort_order' => 1,
            ],
        );
    }
}
