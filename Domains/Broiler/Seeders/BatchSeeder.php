<?php

declare(strict_types=1);

namespace Domains\Broiler\Seeders;

use Domains\Auth\Models\Team;
use Domains\Broiler\Enums\BatchStatus;
use Domains\Broiler\Models\Batch;
use Domains\CRM\Models\Supplier;
use Domains\Shared\Enums\SupplierCategory;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Database\Seeder;

class BatchSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $teams = Team::all();
        $chickSuppliers = Supplier::query()->where('category', SupplierCategory::Chicks)->get();

        foreach ($teams as $team) {
            $this->createBatchesForTeam($team, $chickSuppliers);
        }
    }

    /**
     * @param  Collection<int, Supplier>  $chickSuppliers
     */
    private function createBatchesForTeam(Team $team, $chickSuppliers): void
    {
        $supplier = $chickSuppliers->random();

        // 1. Planned batch (future start date)
        Batch::query()->create([
            'team_id' => $team->id,
            'name' => 'January 2026 Batch',
            'batch_number' => 'BRO-2026-'.str_pad((string) $team->id, 3, '0', STR_PAD_LEFT).'-001',
            'start_date' => now()->addDays(15),
            'expected_end_date' => now()->addDays(57),
            'status' => BatchStatus::Planned,
            'initial_quantity' => 1500,
            'current_quantity' => 1500,
            'supplier_id' => $supplier->id,
            'target_weight_kg' => 2.40,
            'average_weight_kg' => null,
        ]);

        // 2. Active batch (started 20 days ago)
        Batch::query()->create([
            'team_id' => $team->id,
            'name' => 'December 2025 Batch A',
            'batch_number' => 'BRO-2025-'.str_pad((string) $team->id, 3, '0', STR_PAD_LEFT).'-012',
            'start_date' => now()->subDays(20),
            'expected_end_date' => now()->addDays(22),
            'status' => BatchStatus::Active,
            'initial_quantity' => 1000,
            'current_quantity' => 968, // Will be updated by DailyLogSeeder
            'supplier_id' => $chickSuppliers->random()->id,
            'target_weight_kg' => 2.30,
            'average_weight_kg' => 0.95, // ~20 days old
        ]);

        // 3. Another active batch (started 35 days ago - near harvest)
        Batch::query()->create([
            'team_id' => $team->id,
            'name' => 'November 2025 Batch',
            'batch_number' => 'BRO-2025-'.str_pad((string) $team->id, 3, '0', STR_PAD_LEFT).'-011',
            'start_date' => now()->subDays(35),
            'expected_end_date' => now()->addDays(7),
            'status' => BatchStatus::Active,
            'initial_quantity' => 1200,
            'current_quantity' => 1152, // Will be updated by DailyLogSeeder
            'supplier_id' => $chickSuppliers->random()->id,
            'target_weight_kg' => 2.50,
            'average_weight_kg' => 1.85, // ~35 days old
        ]);

        // 4. Harvesting batch (ready for sale)
        Batch::query()->create([
            'team_id' => $team->id,
            'name' => 'October 2025 Batch B',
            'batch_number' => 'BRO-2025-'.str_pad((string) $team->id, 3, '0', STR_PAD_LEFT).'-010',
            'start_date' => now()->subDays(44),
            'expected_end_date' => now()->subDays(2),
            'status' => BatchStatus::Harvesting,
            'initial_quantity' => 800,
            'current_quantity' => 762,
            'supplier_id' => $chickSuppliers->random()->id,
            'target_weight_kg' => 2.30,
            'average_weight_kg' => 2.28,
        ]);

        // 5. Closed batch (completed cycle - historical data)
        Batch::query()->create([
            'team_id' => $team->id,
            'name' => 'September 2025 Batch',
            'batch_number' => 'BRO-2025-'.str_pad((string) $team->id, 3, '0', STR_PAD_LEFT).'-008',
            'start_date' => now()->subDays(90),
            'expected_end_date' => now()->subDays(48),
            'actual_end_date' => now()->subDays(47),
            'status' => BatchStatus::Closed,
            'initial_quantity' => 1000,
            'current_quantity' => 958,
            'supplier_id' => $chickSuppliers->random()->id,
            'target_weight_kg' => 2.20,
            'average_weight_kg' => 2.25,
        ]);

        // 6. Another closed batch (excellent performance)
        Batch::query()->create([
            'team_id' => $team->id,
            'name' => 'August 2025 Batch',
            'batch_number' => 'BRO-2025-'.str_pad((string) $team->id, 3, '0', STR_PAD_LEFT).'-006',
            'start_date' => now()->subDays(120),
            'expected_end_date' => now()->subDays(78),
            'actual_end_date' => now()->subDays(76),
            'status' => BatchStatus::Closed,
            'initial_quantity' => 1500,
            'current_quantity' => 1462,
            'supplier_id' => $chickSuppliers->random()->id,
            'target_weight_kg' => 2.35,
            'average_weight_kg' => 2.38,
        ]);
    }
}
