<?php

namespace Database\Seeders;

use Domains\Auth\Models\Team;
use Domains\Finance\Models\Expense;
use Domains\Shared\Enums\ExpenseCategory;
use Illuminate\Database\Seeder;

class ExpenseSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $teams = Team::all();

        // Expense templates with realistic farm expenses
        $expenseTemplates = [
            // Feed expenses
            [
                'description' => 'Bulk feed purchase - starter feed',
                'amount' => 450000, // 4500 cents = 45.00 BWP
                'category' => ExpenseCategory::Feed,
                'currency' => 'BWP',
                'allocatable_type' => null,
                'allocatable_id' => null,
            ],
            [
                'description' => 'Layer feed - premium mix',
                'amount' => 320000,
                'category' => ExpenseCategory::Feed,
                'currency' => 'BWP',
                'allocatable_type' => null,
                'allocatable_id' => null,
            ],
            // Labor expenses
            [
                'description' => 'Worker wages - weekly',
                'amount' => 200000,
                'category' => ExpenseCategory::Labor,
                'currency' => 'BWP',
                'allocatable_type' => null,
                'allocatable_id' => null,
            ],
            [
                'description' => 'Casual labor - coop cleaning',
                'amount' => 80000,
                'category' => ExpenseCategory::Labor,
                'currency' => 'BWP',
                'allocatable_type' => null,
                'allocatable_id' => null,
            ],
            // Utilities
            [
                'description' => 'Electricity bill - monthly',
                'amount' => 150000,
                'category' => ExpenseCategory::Utilities,
                'currency' => 'BWP',
                'allocatable_type' => null,
                'allocatable_id' => null,
            ],
            [
                'description' => 'Water bill - monthly',
                'amount' => 50000,
                'category' => ExpenseCategory::Utilities,
                'currency' => 'BWP',
                'allocatable_type' => null,
                'allocatable_id' => null,
            ],
            // Equipment
            [
                'description' => 'Water pump replacement',
                'amount' => 800000,
                'category' => ExpenseCategory::Equipment,
                'currency' => 'BWP',
                'allocatable_type' => null,
                'allocatable_id' => null,
            ],
            [
                'description' => 'Feeding equipment - troughs and drinkers',
                'amount' => 300000,
                'category' => ExpenseCategory::Equipment,
                'currency' => 'BWP',
                'allocatable_type' => null,
                'allocatable_id' => null,
            ],
            // Maintenance
            [
                'description' => 'Coop repair - roof maintenance',
                'amount' => 200000,
                'category' => ExpenseCategory::Maintenance,
                'currency' => 'BWP',
                'allocatable_type' => null,
                'allocatable_id' => null,
            ],
            [
                'description' => 'Fence repair and reinforcement',
                'amount' => 150000,
                'category' => ExpenseCategory::Maintenance,
                'currency' => 'BWP',
                'allocatable_type' => null,
                'allocatable_id' => null,
            ],
            // Healthcare
            [
                'description' => 'Vaccination program - chicks',
                'amount' => 120000,
                'category' => ExpenseCategory::Healthcare,
                'currency' => 'BWP',
                'allocatable_type' => null,
                'allocatable_id' => null,
            ],
            [
                'description' => 'Antibiotic and vitamin supplements',
                'amount' => 80000,
                'category' => ExpenseCategory::Healthcare,
                'currency' => 'BWP',
                'allocatable_type' => null,
                'allocatable_id' => null,
            ],
            // Transportation
            [
                'description' => 'Transport of feed supplies',
                'amount' => 100000,
                'category' => ExpenseCategory::Transportation,
                'currency' => 'BWP',
                'allocatable_type' => null,
                'allocatable_id' => null,
            ],
            [
                'description' => 'Vehicle fuel and maintenance',
                'amount' => 180000,
                'category' => ExpenseCategory::Transportation,
                'currency' => 'BWP',
                'allocatable_type' => null,
                'allocatable_id' => null,
            ],
            // Miscellaneous
            [
                'description' => 'Supplies and materials',
                'amount' => 95000,
                'category' => ExpenseCategory::Other,
                'currency' => 'BWP',
                'allocatable_type' => null,
                'allocatable_id' => null,
            ],
            [
                'description' => 'License and permit renewal',
                'amount' => 250000,
                'category' => ExpenseCategory::Other,
                'currency' => 'BWP',
                'allocatable_type' => null,
                'allocatable_id' => null,
            ],
        ];

        // Create expenses for each team
        foreach ($teams as $team) {
            foreach ($expenseTemplates as $expenseData) {
                Expense::create([
                    ...$expenseData,
                    'team_id' => $team->id,
                    'ocr_data' => null,
                    'receipt_path' => null,
                ]);
            }
        }
    }
}
