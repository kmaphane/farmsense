<?php

namespace Database\Seeders;

use Domains\Auth\Models\Team;
use Domains\Inventory\Models\Warehouse;
use Illuminate\Database\Seeder;

class WarehouseSeeder extends Seeder
{
    public function run(): void
    {
        $teams = Team::all();

        $warehouses = [
            [
                'name' => 'Main Feed Store',
                'location' => 'Building A - Ground Floor',
                'capacity' => 500,
            ],
            [
                'name' => 'Chick Brooding House',
                'location' => 'Building B',
                'capacity' => 200,
            ],
            [
                'name' => 'Medicine & Equipment Store',
                'location' => 'Building C - Locked Storage',
                'capacity' => 100,
            ],
        ];

        foreach ($teams as $team) {
            foreach ($warehouses as $warehouse) {
                Warehouse::create([
                    'team_id' => $team->id,
                    'name' => $warehouse['name'],
                    'location' => $warehouse['location'],
                    'capacity' => $warehouse['capacity'],
                    'is_active' => true,
                ]);
            }
        }
    }
}
