<?php

declare(strict_types=1);

namespace Domains\Auth\Seeders;

use Domains\Auth\Models\Team;
use Domains\Auth\Models\User;
use Illuminate\Database\Seeder;
use Spatie\Permission\Models\Role;

class UserSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Get all teams
        $teams = Team::all();

        // Get roles
        $superAdminRole = Role::query()->where('name', 'Super Admin')->first();
        $farmManagerRole = Role::query()->where('name', 'Farm Manager')->first();
        $partnerRole = Role::query()->where('name', 'Partner')->first();
        $fieldWorkerRole = Role::query()->where('name', 'Field Worker')->first();

        // Super Admin - has access to all teams
        $superAdmin = User::query()->firstOrCreate(['email' => 'kenna@omkom.com'], [
            'name' => 'System Admin',
            'password' => bcrypt('password'),
            'email_verified_at' => now(),
        ]);

        // Assign Super Admin to all teams with Super Admin role
        foreach ($teams as $team) {
            if (! $superAdmin->teams()->where('team_id', $team->id)->exists()) {
                $superAdmin->teams()->attach($team->id, ['role_id' => $superAdminRole->id]);
            }
        }
        $superAdmin->update(['current_team_id' => $teams->first()->id]);

        // Farm Manager for each team
        $farmManagers = [
            [
                'name' => 'Kenna Manager',
                'email' => 'manager@kenna.local',
                'team_index' => 0,
            ],
            [
                'name' => 'Farm 2 Manager',
                'email' => 'manager@farm2.local',
                'team_index' => 1,
            ],
        ];

        foreach ($farmManagers as $managerData) {
            $teamIndex = $managerData['team_index'];
            unset($managerData['team_index']);

            $manager = User::query()->firstOrCreate(['email' => $managerData['email']], [
                ...$managerData,
                'password' => bcrypt('password'),
                'email_verified_at' => now(),
            ]);

            $team = $teams->get($teamIndex);
            if (! $manager->teams()->where('team_id', $team->id)->exists()) {
                $manager->teams()->attach($team->id, ['role_id' => $farmManagerRole->id]);
            }
            $manager->update(['current_team_id' => $team->id]);
        }

        // Partners (can belong to multiple teams)
        $partners = [
            [
                'name' => 'Partner One',
                'email' => 'partner1@example.com',
                'team_indices' => [0, 1],
            ],
            [
                'name' => 'Partner Two',
                'email' => 'partner2@example.com',
                'team_indices' => [1, 2],
            ],
        ];

        foreach ($partners as $partnerData) {
            $teamIndices = $partnerData['team_indices'];
            unset($partnerData['team_indices']);

            $partner = User::query()->firstOrCreate(['email' => $partnerData['email']], [
                ...$partnerData,
                'password' => bcrypt('password'),
                'email_verified_at' => now(),
            ]);

            foreach ($teamIndices as $index) {
                $team = $teams->get($index);
                if (! $partner->teams()->where('team_id', $team->id)->exists()) {
                    $partner->teams()->attach($team->id, ['role_id' => $partnerRole->id]);
                }
            }
            if ($partner->current_team_id === null) {
                $partner->update(['current_team_id' => $teams->get($teamIndices[0])->id]);
            }
        }

        // Field Workers (assigned to specific teams)
        $fieldWorkers = [
            [
                'name' => 'Field Worker 1',
                'email' => 'worker1@example.com',
                'team_index' => 0,
            ],
            [
                'name' => 'Field Worker 2',
                'email' => 'worker2@example.com',
                'team_index' => 0,
            ],
            [
                'name' => 'Field Worker 3',
                'email' => 'worker3@example.com',
                'team_index' => 1,
            ],
        ];

        foreach ($fieldWorkers as $workerData) {
            $teamIndex = $workerData['team_index'];
            unset($workerData['team_index']);

            $worker = User::query()->firstOrCreate(['email' => $workerData['email']], [
                ...$workerData,
                'password' => bcrypt('password'),
                'email_verified_at' => now(),
            ]);

            $team = $teams->get($teamIndex);
            if (! $worker->teams()->where('team_id', $team->id)->exists()) {
                $worker->teams()->attach($team->id, ['role_id' => $fieldWorkerRole->id]);
            }
            $worker->update(['current_team_id' => $team->id]);
        }
    }
}
