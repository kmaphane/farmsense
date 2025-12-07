<?php

declare(strict_types=1);

namespace Domains\Inventory\Seeders;

use Domains\Auth\Models\User;
use Domains\Inventory\Enums\MovementType;
use Domains\Inventory\Models\Product;
use Domains\Inventory\Models\StockMovement;
use Domains\Inventory\Models\Warehouse;
use Illuminate\Database\Seeder;

class StockMovementSeeder extends Seeder
{
    public function run(): void
    {
        $products = Product::all();
        $warehouses = Warehouse::all();
        $users = User::all();

        $reasons = ['Initial stock', 'Stock received', 'Usage', 'Damaged', 'Expired', 'Transfer', 'Adjustment'];

        foreach ($products->take(50) as $product) {
            $teamId = $product->team_id;
            $warehouse = $warehouses->where('team_id', $teamId)->random();
            $user = $users->where('current_team_id', $teamId)->first() ?? $users->first();

            if ($warehouse && $user) {
                StockMovement::query()->create([
                    'team_id' => $teamId,
                    'product_id' => $product->id,
                    'warehouse_id' => $warehouse->id,
                    'quantity' => rand(10, 100),
                    'movement_type' => MovementType::In,
                    'reason' => 'Initial stock',
                    'notes' => 'Stock initialized from supplier',
                    'recorded_by' => $user->id,
                ]);
            }
        }

        // Add some outgoing movements
        foreach ($products->take(30) as $product) {
            $teamId = $product->team_id;
            $warehouse = $warehouses->where('team_id', $teamId)->random();
            $user = $users->where('current_team_id', $teamId)->first() ?? $users->first();

            if ($warehouse && $user) {
                StockMovement::query()->create([
                    'team_id' => $teamId,
                    'product_id' => $product->id,
                    'warehouse_id' => $warehouse->id,
                    'quantity' => rand(5, 50),
                    'movement_type' => MovementType::Out,
                    'reason' => $reasons[array_rand($reasons)],
                    'notes' => 'Regular usage',
                    'recorded_by' => $user->id,
                ]);
            }
        }
    }
}
