<?php

namespace Domains\CRM\Factories;

use Domains\CRM\Models\Supplier;
use Domains\Shared\Enums\SupplierCategory;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Database\Eloquent\Model;

/**
 * @extends Factory<Supplier>
 */
class SupplierFactory extends Factory
{
    /**
     * The name of the factory's corresponding model.
     *
     * @var class-string<Model>
     */
    protected $model = Supplier::class;

    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'name' => fake()->company(),
            'email' => fake()->unique()->companyEmail(),
            'phone' => fake()->phoneNumber(),
            'category' => fake()->randomElement(SupplierCategory::cases()),
            'performance_rating' => fake()->randomFloat(2, 1, 5),
            'current_price_per_unit' => fake()->randomFloat(2, 10, 500),
            'notes' => fake()->optional()->sentence(),
            'is_active' => true,
        ];
    }

    /**
     * Create an inactive supplier.
     */
    public function inactive(): static
    {
        return $this->state(fn (array $attributes) => [
            'is_active' => false,
        ]);
    }

    /**
     * Set the supplier category to Feed.
     */
    public function feed(): static
    {
        return $this->state(fn (array $attributes) => [
            'category' => SupplierCategory::Feed,
        ]);
    }

    /**
     * Set the supplier category to Chicks.
     */
    public function chicks(): static
    {
        return $this->state(fn (array $attributes) => [
            'category' => SupplierCategory::Chicks,
        ]);
    }

    /**
     * Set the supplier category to Equipment.
     */
    public function equipment(): static
    {
        return $this->state(fn (array $attributes) => [
            'category' => SupplierCategory::Equipment,
        ]);
    }

    /**
     * Set the supplier category to Medication.
     */
    public function medication(): static
    {
        return $this->state(fn (array $attributes) => [
            'category' => SupplierCategory::Medication,
        ]);
    }
}
