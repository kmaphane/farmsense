<?php

declare(strict_types=1);

namespace Domains\Inventory\Seeders;

use Domains\Auth\Models\Team;
use Domains\Inventory\Enums\ProductType;
use Domains\Inventory\Models\Product;
use Illuminate\Database\Seeder;

class ProductSeeder extends Seeder
{
    public function run(): void
    {
        // Get all teams
        $teams = Team::all();

        $products = [
            [
                'name' => 'Starter Feed 21%',
                'description' => 'High-protein starter feed for young chicks (0-4 weeks)',
                'type' => ProductType::Feed,
                'unit' => 'bag',
                'quantity_on_hand' => 150,
                'reorder_level' => 50,
                'unit_cost' => 145000, // BWP 1450.00
            ],
            [
                'name' => 'Grower Feed 18%',
                'description' => 'Medium-protein grower feed for growing broilers (4-6 weeks)',
                'type' => ProductType::Feed,
                'unit' => 'bag',
                'quantity_on_hand' => 200,
                'reorder_level' => 100,
                'unit_cost' => 130000, // BWP 1300.00
            ],
            [
                'name' => 'Finisher Feed 15%',
                'description' => 'Lower-protein finisher feed for final fattening (6+ weeks)',
                'type' => ProductType::Feed,
                'unit' => 'bag',
                'quantity_on_hand' => 180,
                'reorder_level' => 80,
                'unit_cost' => 120000, // BWP 1200.00
            ],
            [
                'name' => 'Coccidiosis Prevention',
                'description' => 'Medicine to prevent and treat coccidiosis',
                'type' => ProductType::Medicine,
                'unit' => 'bottle',
                'quantity_on_hand' => 25,
                'reorder_level' => 10,
                'unit_cost' => 35000, // BWP 350.00
            ],
            [
                'name' => 'Newcastle Disease Vaccine',
                'description' => 'Live vaccine for Newcastle disease protection',
                'type' => ProductType::Medicine,
                'unit' => 'bottle',
                'quantity_on_hand' => 30,
                'reorder_level' => 15,
                'unit_cost' => 42000, // BWP 420.00
            ],
            [
                'name' => 'Vitamin & Mineral Supplement',
                'description' => 'Complete vitamin and mineral supplement for poultry',
                'type' => ProductType::Medicine,
                'unit' => 'bottle',
                'quantity_on_hand' => 15,
                'reorder_level' => 8,
                'unit_cost' => 28000, // BWP 280.00
            ],
            [
                'name' => 'Cardboard Crates (20kg)',
                'description' => 'Sturdy cardboard transport crates for live birds',
                'type' => ProductType::Packaging,
                'unit' => 'crate',
                'quantity_on_hand' => 500,
                'reorder_level' => 200,
                'unit_cost' => 12000, // BWP 120.00
            ],
            [
                'name' => 'Plastic Water Drinkers',
                'description' => '10-liter capacity plastic drinkers',
                'type' => ProductType::Equipment,
                'unit' => 'piece',
                'quantity_on_hand' => 45,
                'reorder_level' => 20,
                'unit_cost' => 65000, // BWP 650.00
            ],
            [
                'name' => 'Feeder Pans (Round)',
                'description' => 'Round metal feeding pans',
                'type' => ProductType::Equipment,
                'unit' => 'piece',
                'quantity_on_hand' => 80,
                'reorder_level' => 40,
                'unit_cost' => 32000, // BWP 320.00
            ],
            [
                'name' => 'Heat Lamp - 250W',
                'description' => 'Infrared heat lamp for brooding',
                'type' => ProductType::Equipment,
                'unit' => 'piece',
                'quantity_on_hand' => 12,
                'reorder_level' => 5,
                'unit_cost' => 95000, // BWP 950.00
            ],
        ];

        foreach ($teams as $team) {
            foreach ($products as $product) {
                Product::create([
                    'team_id' => $team->id,
                    'name' => $product['name'],
                    'description' => $product['description'],
                    'type' => $product['type'],
                    'unit' => $product['unit'],
                    'quantity_on_hand' => $product['quantity_on_hand'],
                    'reorder_level' => $product['reorder_level'],
                    'unit_cost' => $product['unit_cost'],
                    'is_active' => true,
                ]);
            }
        }
    }
}
