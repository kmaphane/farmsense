<?php

declare(strict_types=1);

namespace Domains\Auth\Seeders;

use Domains\Auth\Models\Team;
use Domains\Auth\Models\User;
use Illuminate\Database\Seeder;

class TeamSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $teams = [
            [
                'name' => 'Kenna\'s Farm',
                'subscription_plan' => 'Pro',
                'owner_email' => 'kenna@example.com',
            ],
            [
                'name' => 'Test Farm 2',
                'subscription_plan' => 'Basic',
                'owner_email' => 'farm2@example.com',
            ],
            [
                'name' => 'Enterprise Farm',
                'subscription_plan' => 'Enterprise',
                'owner_email' => 'enterprise@example.com',
            ],
        ];

        foreach ($teams as $teamData) {
            $ownerEmail = $teamData['owner_email'];
            unset($teamData['owner_email']);

            // Create owner user if doesn't exist
            $owner = User::query()->firstOrCreate(['email' => $ownerEmail], [
                'name' => ucfirst(explode('@', $ownerEmail)[0]),
                'password' => bcrypt('password'),
            ]);

            // Create team with owner
            $team = Team::query()->create([
                ...$teamData,
                'owner_id' => $owner->id,
            ]);

            // Set owner's current team
            $owner->update(['current_team_id' => $team->id]);
        }
    }
}
