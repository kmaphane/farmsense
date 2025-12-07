<?php

namespace Domains\Auth\Tests\Feature;

use Domains\Auth\Models\Team;
use Domains\Auth\Models\User;
use Spatie\Permission\Models\Role;
use Tests\TestCase;

class TeamManagementTest extends TestCase
{
    /**
     * Test that a user can belong to multiple teams.
     */
    public function test_user_can_belong_to_multiple_teams(): void
    {
        $user = User::factory()->create();
        $team1 = Team::factory()->create();
        $team2 = Team::factory()->create();

        // Attach user to teams
        $user->teams()->attach($team1->id, ['role_id' => 1]);
        $user->teams()->attach($team2->id, ['role_id' => 2]);

        expect($user->teams->count())->toBe(2);
        expect($user->teams->pluck('id')->toArray())->toContain($team1->id, $team2->id);
    }

    /**
     * Test that a user can have different roles on different teams.
     */
    public function test_user_can_have_different_roles_per_team(): void
    {
        $user = User::factory()->create();
        $team1 = Team::factory()->create();
        $team2 = Team::factory()->create();

        $superAdminRole = Role::query()->where('name', 'Super Admin')->first();
        $farmManagerRole = Role::query()->where('name', 'Farm Manager')->first();

        // Attach with different roles
        $user->teams()->attach($team1->id, ['role_id' => $superAdminRole->id]);
        $user->teams()->attach($team2->id, ['role_id' => $farmManagerRole->id]);

        $userTeam1 = $user->teams()->where('team_id', $team1->id)->first();
        $userTeam2 = $user->teams()->where('team_id', $team2->id)->first();

        expect($userTeam1->pivot->role_id)->toBe($superAdminRole->id);
        expect($userTeam2->pivot->role_id)->toBe($farmManagerRole->id);
    }

    /**
     * Test that a team can have multiple users.
     */
    public function test_team_can_have_multiple_users(): void
    {
        $team = Team::factory()->create();
        $user1 = User::factory()->create();
        $user2 = User::factory()->create();
        $user3 = User::factory()->create();

        $team->users()->attach($user1->id, ['role_id' => 1]);
        $team->users()->attach($user2->id, ['role_id' => 2]);
        $team->users()->attach($user3->id, ['role_id' => 1]);

        expect($team->users->count())->toBe(3);
    }

    /**
     * Test that a user can set their current team.
     */
    public function test_user_can_set_current_team(): void
    {
        $user = User::factory()->create();
        $team1 = Team::factory()->create();
        $team2 = Team::factory()->create();

        $user->teams()->attach($team1->id, ['role_id' => 1]);
        $user->teams()->attach($team2->id, ['role_id' => 1]);

        $user->setCurrentTeam($team2);

        expect($user->current_team_id)->toBe($team2->id);
        expect($user->currentTeam()->id)->toBe($team2->id);
    }

    /**
     * Test that a team owner is correctly identified.
     */
    public function test_team_owner_is_correctly_identified(): void
    {
        $owner = User::factory()->create();
        $team = Team::factory()->create(['owner_id' => $owner->id]);

        expect($team->owner()->is($owner))->toBeTrue();
        expect($owner->isTeamOwner($team))->toBeTrue();

        $otherUser = User::factory()->create();
        expect($otherUser->isTeamOwner($team))->toBeFalse();
    }

    /**
     * Test that a user can check team access.
     */
    public function test_user_can_check_team_access(): void
    {
        $user = User::factory()->create();
        $team = Team::factory()->create();
        $otherTeam = Team::factory()->create();

        $user->teams()->attach($team->id, ['role_id' => 1]);

        expect($user->hasTeamAccess($team))->toBeTrue();
        expect($user->hasTeamAccess($otherTeam))->toBeFalse();
    }
}
