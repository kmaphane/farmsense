<?php

declare(strict_types=1);

namespace Database\Factories;

use Domains\Auth\Models\Team;
use Illuminate\Database\Eloquent\Factories\Factory;

class TeamFactory extends Factory
{
    protected $model = Team::class;

    public function definition(): array
    {
        $user = \Domains\Auth\Models\User::factory()->create();

        return [
            'name' => $this->faker->company(),
            'owner_id' => $user->id,
        ];
    }
}
