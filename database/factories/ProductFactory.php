<?php

declare(strict_types=1);

namespace Database\Factories;

use Domains\Inventory\Enums\PackageUnit;
use Domains\Inventory\Enums\ProductType;
use Domains\Inventory\Models\Product;
use Illuminate\Database\Eloquent\Factories\Factory;

class ProductFactory extends Factory
{
    protected $model = Product::class;

    public function definition(): array
    {
        return [
            'team_id' => 1,
            'name' => $this->faker->word(),
            'description' => $this->faker->sentence(),
            'type' => $this->faker->randomElement(ProductType::cases()),
            'unit' => $this->faker->randomElement(['kg', 'bag', 'liter', 'pack']),
            'quantity_on_hand' => $this->faker->numberBetween(0, 1000),
            'reorder_level' => $this->faker->numberBetween(0, 100),
            'unit_cost' => $this->faker->numberBetween(100, 10000),
            'selling_price_cents' => $this->faker->numberBetween(200, 20000),
            'units_per_package' => $this->faker->numberBetween(1, 10),
            'package_unit' => $this->faker->randomElement(PackageUnit::cases()),
            'yield_per_bird' => $this->faker->numberBetween(1, 2),
            'is_active' => true,
        ];
    }
}
