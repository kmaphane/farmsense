<?php

namespace Domains\Auth\Factories;

use Domains\Auth\Models\Team;
use Domains\Auth\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Database\Eloquent\Model;

/**
 * @extends Factory<Team>
 */
class TeamFactory extends Factory
{
    /**
     * The name of the factory's corresponding model.
     *
     * @var class-string<Model>
     */
    protected $model = Team::class;

    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'owner_id' => User::factory(),
            'name' => fake()->company(),
            'subscription_plan' => fake()->randomElement(['Basic', 'Pro', 'Enterprise']),
        ];
    }

    /**
     * Set the owner of the team.
     */
    public function ownedBy(User $user): static
    {
        return $this->state(fn (array $attributes) => [
            'owner_id' => $user->id,
        ]);
    }

    /**
     * Set the subscription plan to Basic.
     */
    public function basic(): static
    {
        return $this->state(fn (array $attributes) => [
            'subscription_plan' => 'Basic',
        ]);
    }

    /**
     * Set the subscription plan to Pro.
     */
    public function pro(): static
    {
        return $this->state(fn (array $attributes) => [
            'subscription_plan' => 'Pro',
        ]);
    }

    /**
     * Set the subscription plan to Enterprise.
     */
    public function enterprise(): static
    {
        return $this->state(fn (array $attributes) => [
            'subscription_plan' => 'Enterprise',
        ]);
    }
}
