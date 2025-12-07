<?php

namespace Domains\Auth\Tests\Feature;

use Domains\Auth\Models\Team;
use Domains\Auth\Models\User;
use Spatie\Permission\Models\Role;
use Tests\TestCase;

class AuthorizationTest extends TestCase
{
    protected User $superAdmin;

    protected User $farmManager;

    protected User $otherUser;

    protected Team $team;

    protected function setUp(): void
    {
        parent::setUp();

        $superAdminRole = Role::query()->where('name', 'Super Admin')->first();
        $farmManagerRole = Role::query()->where('name', 'Farm Manager')->first();

        $this->superAdmin = User::factory()->create();
        $this->farmManager = User::factory()->create();
        $this->otherUser = User::factory()->create();
        $this->team = Team::factory()->create(['owner_id' => $this->superAdmin->id]);

        $this->superAdmin->teams()->attach($this->team->id, ['role_id' => $superAdminRole->id]);
        $this->superAdmin->update(['current_team_id' => $this->team->id]);

        $this->farmManager->teams()->attach($this->team->id, ['role_id' => $farmManagerRole->id]);
        $this->farmManager->update(['current_team_id' => $this->team->id]);
    }

    /**
     * Test super admin can view any user.
     */
    public function test_super_admin_can_view_any_user(): void
    {
        expect($this->superAdmin->can('view', $this->farmManager))->toBeTrue();
    }

    /**
     * Test user can view themselves.
     */
    public function test_user_can_view_themselves(): void
    {
        expect($this->farmManager->can('view', $this->farmManager))->toBeTrue();
    }

    /**
     * Test farm manager cannot view other users.
     */
    public function test_farm_manager_cannot_view_other_users(): void
    {
        expect($this->farmManager->can('view', $this->otherUser))->toBeFalse();
    }

    /**
     * Test super admin can create users.
     */
    public function test_super_admin_can_create_user(): void
    {
        expect($this->superAdmin->can('create', User::class))->toBeTrue();
    }

    /**
     * Test farm manager cannot create users.
     */
    public function test_farm_manager_cannot_create_user(): void
    {
        expect($this->farmManager->can('create', User::class))->toBeFalse();
    }

    /**
     * Test super admin can delete users.
     */
    public function test_super_admin_can_delete_user(): void
    {
        $user = User::factory()->create();
        expect($this->superAdmin->can('delete', $user))->toBeTrue();
    }

    /**
     * Test super admin can view any team.
     */
    public function test_super_admin_can_view_any_team(): void
    {
        $otherTeam = Team::factory()->create();
        expect($this->superAdmin->can('view', $otherTeam))->toBeTrue();
    }

    /**
     * Test team member can view team.
     */
    public function test_team_member_can_view_team(): void
    {
        expect($this->farmManager->can('view', $this->team))->toBeTrue();
    }

    /**
     * Test non-member cannot view team.
     */
    public function test_non_member_cannot_view_team(): void
    {
        expect($this->otherUser->can('view', $this->team))->toBeFalse();
    }

    /**
     * Test team owner can update team.
     */
    public function test_team_owner_can_update_team(): void
    {
        expect($this->superAdmin->can('update', $this->team))->toBeTrue();
    }

    /**
     * Test non-owner cannot update team.
     */
    public function test_non_owner_cannot_update_team(): void
    {
        expect($this->farmManager->can('update', $this->team))->toBeFalse();
    }

    /**
     * Test only super admin can create teams.
     */
    public function test_only_super_admin_can_create_team(): void
    {
        expect($this->superAdmin->can('create', Team::class))->toBeTrue();
        expect($this->farmManager->can('create', Team::class))->toBeFalse();
    }
}
