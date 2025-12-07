<?php

declare(strict_types=1);

namespace Domains\Inventory\Seeders;

use Domains\Auth\Models\Team;
use Domains\Inventory\Enums\PackageUnit;
use Domains\Inventory\Enums\ProductType;
use Domains\Inventory\Models\Product;
use Illuminate\Database\Seeder;

class ProductSeeder extends Seeder
{
    public function run(): void
    {
        // Get all teams
        $teams = Team::all();

        // Inventory products (feed, medicine, equipment)
        $inventoryProducts = [
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

        // Poultry products (for sale)
        $poultryProducts = [
            [
                'name' => 'Live Broiler',
                'description' => 'Live broiler chicken ready for sale',
                'type' => ProductType::LiveBird,
                'unit' => 'bird',
                'selling_price_cents' => 8200, // BWP 82.00
                'units_per_package' => 1,
                'package_unit' => PackageUnit::Single,
                'yield_per_bird' => 1,
            ],
            [
                'name' => 'Whole Chicken',
                'description' => 'Dressed whole chicken',
                'type' => ProductType::WholeChicken,
                'unit' => 'bird',
                'selling_price_cents' => 8000, // BWP 80.00
                'units_per_package' => 1,
                'package_unit' => PackageUnit::Single,
                'yield_per_bird' => 1,
            ],
            [
                'name' => 'Chicken Pieces (0.5kg)',
                'description' => 'Portioned chicken pieces - 0.5kg pack',
                'type' => ProductType::ChickenPieces,
                'unit' => 'pack',
                'selling_price_cents' => 3000, // BWP 30.00
                'units_per_package' => 1,
                'package_unit' => PackageUnit::Pack,
                'yield_per_bird' => 1, // 1 piece pack from portioning (not direct from slaughter)
            ],
            [
                'name' => 'Chicken Feet (Runaways)',
                'description' => 'Pack of 10 chicken feet',
                'type' => ProductType::Offal,
                'unit' => 'pack',
                'selling_price_cents' => 1200, // BWP 12.00
                'units_per_package' => 10,
                'package_unit' => PackageUnit::Pack,
                'yield_per_bird' => 2, // 2 feet per bird
            ],
            [
                'name' => 'Chicken Necks (Melala)',
                'description' => 'Pack of 6 chicken necks',
                'type' => ProductType::Offal,
                'unit' => 'pack',
                'selling_price_cents' => 1200, // BWP 12.00
                'units_per_package' => 6,
                'package_unit' => PackageUnit::Pack,
                'yield_per_bird' => 1,
            ],
            [
                'name' => 'Gizzards (Dintshu)',
                'description' => 'Pack of 6 gizzards',
                'type' => ProductType::Offal,
                'unit' => 'pack',
                'selling_price_cents' => 1500, // BWP 15.00
                'units_per_package' => 6,
                'package_unit' => PackageUnit::Pack,
                'yield_per_bird' => 1,
            ],
            [
                'name' => 'Chicken Livers (Debete)',
                'description' => 'Pack of 6 livers',
                'type' => ProductType::Offal,
                'unit' => 'pack',
                'selling_price_cents' => 1200, // BWP 12.00
                'units_per_package' => 6,
                'package_unit' => PackageUnit::Pack,
                'yield_per_bird' => 1,
            ],
            [
                'name' => 'Chicken Hearts (Dipelo)',
                'description' => 'Pack of 10 hearts',
                'type' => ProductType::Offal,
                'unit' => 'pack',
                'selling_price_cents' => 500, // BWP 5.00
                'units_per_package' => 10,
                'package_unit' => PackageUnit::Pack,
                'yield_per_bird' => 1,
            ],
            [
                'name' => 'Intestines (Mala)',
                'description' => '1 cup of cleaned intestines',
                'type' => ProductType::Offal,
                'unit' => 'cup',
                'selling_price_cents' => 1500, // BWP 15.00
                'units_per_package' => 1,
                'package_unit' => PackageUnit::Cup,
                'yield_per_bird' => 1,
            ],
            [
                'name' => 'Chicken Heads (Dithogo)',
                'description' => 'Pack of 20 heads',
                'type' => ProductType::Offal,
                'unit' => 'pack',
                'selling_price_cents' => 2000, // BWP 20.00
                'units_per_package' => 20,
                'package_unit' => PackageUnit::Pack,
                'yield_per_bird' => 1,
            ],
            [
                'name' => 'Manure (Motshetelo)',
                'description' => '50kg bag of chicken manure',
                'type' => ProductType::ByProduct,
                'unit' => 'bag',
                'selling_price_cents' => 5000, // BWP 50.00
                'units_per_package' => 1,
                'package_unit' => PackageUnit::Bag,
                'yield_per_bird' => 0, // Collected at batch closure, not per bird
            ],
        ];

        foreach ($teams as $team) {
            // Create inventory products
            foreach ($inventoryProducts as $product) {
                Product::query()->create([
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

            // Create poultry products
            foreach ($poultryProducts as $product) {
                Product::query()->create([
                    'team_id' => $team->id,
                    'name' => $product['name'],
                    'description' => $product['description'],
                    'type' => $product['type'],
                    'unit' => $product['unit'],
                    'quantity_on_hand' => 0, // Start with zero stock
                    'reorder_level' => 0,
                    'unit_cost' => null,
                    'selling_price_cents' => $product['selling_price_cents'],
                    'units_per_package' => $product['units_per_package'],
                    'package_unit' => $product['package_unit'],
                    'yield_per_bird' => $product['yield_per_bird'],
                    'is_active' => true,
                ]);
            }
        }
    }
}
