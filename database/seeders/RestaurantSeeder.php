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
                'name' => 'Restaurante A Saina',
                'description' => [
                    'es' => 'Cocina gallega frente al Atlantico, con pescado fresco, marisco y arroces junto a la Playa da Frouxeira.',
                    'en' => 'Galician cooking by the Atlantic, with fresh fish, seafood and rice dishes beside Playa da Frouxeira.',
                ],
                'email' => 'reservas@asaina.test',
                'phone' => '+34 981 48 00 00',
                'address' => 'Avenida de Saina, 30',
                'city' => 'Valdovino',
                'country' => 'Spain',
                'latitude' => 43.6154,
                'longitude' => -8.1461,
                'default_reservation_duration' => 90,
                'reservation_interval' => 30,
                'max_days_in_advance' => 30,
                'max_reservations_per_slot' => 6,
                'max_guests_per_slot' => 24,
                'timezone' => 'Europe/Madrid',
                'default_locale' => 'es',
                'locales' => ['es', 'en'],
            ],
        );

        foreach ([2, 3, 4, 5, 6, 7] as $weekday) {
            OpeningHour::query()->updateOrCreate(
                ['weekday' => $weekday, 'opens_at' => '13:00:00'],
                ['closes_at' => '16:30:00', 'is_closed' => false, 'label' => 'Comida'],
            );

            OpeningHour::query()->updateOrCreate(
                ['weekday' => $weekday, 'opens_at' => '20:30:00'],
                ['closes_at' => '23:30:00', 'is_closed' => false, 'label' => 'Cena'],
            );
        }

        $terrace = Area::query()->updateOrCreate(
            ['id' => 1],
            ['name' => ['es' => 'Terraza', 'en' => 'Terrace'], 'is_active' => true, 'sort_order' => 1],
        );
        $salon = Area::query()->updateOrCreate(
            ['id' => 2],
            ['name' => ['es' => 'Interior', 'en' => 'Dining room'], 'is_active' => true, 'sort_order' => 2],
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
        $menu->categories()->with('items')->get()->each(function ($category) {
            $category->items()->delete();
        });

        $categories = [
            ['Entrantes frios', 'Cold starters', [
                ['Ensalada clasica', 'Classic salad', 9.50],
                ['Ensaladilla rusa casera', 'Homemade potato salad', 8.50],
                ['Salpicon de marisco', 'Seafood salpicon', 16.00],
                ['Empanada gallega del dia', 'Galician empanada of the day', 7.50],
            ]],
            ['Entrantes calientes', 'Hot starters', [
                ['Pulpo a feira', 'Galician octopus', 18.00],
                ['Mejillones al vapor', 'Steamed mussels', 12.00],
                ['Almejas a la marinera', 'Clams marinera style', 19.00],
                ['Navajas a la plancha', 'Grilled razor clams', 16.50],
                ['Croquetas caseras de marisco', 'Homemade seafood croquettes', 10.50],
            ]],
            ['Pescados', 'Fish', [
                ['Bacalao a la gallega', 'Galician style cod', 18.50],
                ['Bacalao a la plancha', 'Grilled cod', 17.50],
                ['Rape en salsa marinera', 'Monkfish in seafood sauce', 22.00],
                ['Calamares de la ria', 'Local squid', 16.00],
            ]],
            ['Mariscos', 'Seafood', [
                ['Zamburinas a la plancha', 'Grilled small scallops', 18.00],
                ['Langostinos cocidos', 'Cooked prawns', 16.00],
                ['Berberechos al vapor', 'Steamed cockles', 15.00],
            ]],
            ['Arroces', 'Rice dishes', [
                ['Paella de marisco', 'Seafood paella', 19.00],
                ['Arroz marinero', 'Seafood rice', 21.00],
                ['Paella mixta', 'Mixed paella', 17.00],
            ]],
            ['Postres', 'Desserts', [
                ['Tarta de queso casera', 'Homemade cheesecake', 6.00],
                ['Flan de huevo', 'Egg flan', 5.00],
                ['Arroz con leche', 'Rice pudding', 5.50],
                ['Canitas rellenas de crema', 'Cream filled pastries', 6.50],
            ]],
        ];

        foreach ($categories as $categoryIndex => [$nameEs, $nameEn, $items]) {
            $category = $menu->categories()->updateOrCreate(
                ['id' => $categoryIndex + 1],
                ['name' => ['es' => $nameEs, 'en' => $nameEn], 'sort_order' => $categoryIndex + 1],
            );

            foreach ($items as $itemIndex => [$itemEs, $itemEn, $price]) {
                $category->items()->updateOrCreate(
                    ['id' => (($categoryIndex + 1) * 100) + $itemIndex + 1],
                    [
                        'name' => ['es' => $itemEs, 'en' => $itemEn],
                        'description' => ['es' => 'Producto fresco y elaboracion sencilla de la casa.', 'en' => 'Fresh produce and simple house preparation.'],
                        'price' => $price,
                        'sort_order' => $itemIndex + 1,
                    ],
                );
            }
        }
    }
}
