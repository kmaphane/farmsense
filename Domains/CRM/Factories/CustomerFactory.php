<?php

namespace Domains\CRM\Factories;

use Domains\Auth\Models\Team;
use Domains\CRM\Models\Customer;
use Domains\Shared\Enums\CustomerType;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Database\Eloquent\Model;

/**
 * @extends Factory<Customer>
 */
class CustomerFactory extends Factory
{
    /**
     * The name of the factory's corresponding model.
     *
     * @var class-string<Model>
     */
    protected $model = Customer::class;

    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'team_id' => Team::factory(),
            'name' => fake()->name(),
            'email' => fake()->unique()->safeEmail(),
            'phone' => fake()->phoneNumber(),
            'type' => fake()->randomElement(CustomerType::cases()),
            'credit_limit' => fake()->numberBetween(0, 100000), // In cents
            'payment_terms' => fake()->optional()->sentence(),
            'notes' => fake()->optional()->paragraph(),
        ];
    }

    /**
     * Assign the customer to a team.
     */
    public function forTeam(Team $team): static
    {
        return $this->state(fn (array $attributes) => [
            'team_id' => $team->id,
        ]);
    }

    /**
     * Set the customer type to Retail.
     */
    public function retail(): static
    {
        return $this->state(fn (array $attributes) => [
            'type' => CustomerType::Retail,
        ]);
    }

    /**
     * Set the customer type to Wholesale.
     */
    public function wholesale(): static
    {
        return $this->state(fn (array $attributes) => [
            'type' => CustomerType::Wholesale,
        ]);
    }

    /**
     * Set the customer type to Restaurant.
     */
    public function restaurant(): static
    {
        return $this->state(fn (array $attributes) => [
            'type' => CustomerType::Restaurant,
        ]);
    }

    /**
     * Set a specific credit limit.
     */
    public function withCreditLimit(int $amount): static
    {
        return $this->state(fn (array $attributes) => [
            'credit_limit' => $amount,
        ]);
    }
}
