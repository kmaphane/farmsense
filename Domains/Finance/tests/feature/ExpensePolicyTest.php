<?php

namespace Domains\Finance\Tests\Feature;

use Domains\Auth\Models\Team;
use Domains\Auth\Models\User;
use Domains\Finance\Models\Expense;
use Domains\Shared\Enums\ExpenseCategory;
use Spatie\Permission\Models\Role;
use Tests\TestCase;

class ExpensePolicyTest extends TestCase
{
    protected User $superAdmin;
    protected User $farmManager;
    protected User $partner;
    protected User $fieldWorker;
    protected Team $team1;
    protected Team $team2;

    public function setUp(): void
    {
        parent::setUp();

        // Get roles
        $superAdminRole = Role::where('name', 'Super Admin')->first();
        $farmManagerRole = Role::where('name', 'Farm Manager')->first();
        $partnerRole = Role::where('name', 'Partner')->first();
        $fieldWorkerRole = Role::where('name', 'Field Worker')->first();

        // Create users
        $this->superAdmin = User::factory()->create();
        $this->farmManager = User::factory()->create();
        $this->partner = User::factory()->create();
        $this->fieldWorker = User::factory()->create();

        // Create teams
        $this->team1 = Team::factory()->create();
        $this->team2 = Team::factory()->create();

        // Assign users to teams with roles
        $this->superAdmin->teams()->attach($this->team1->id, ['role_id' => $superAdminRole->id]);
        $this->superAdmin->teams()->attach($this->team2->id, ['role_id' => $superAdminRole->id]);
        $this->superAdmin->update(['current_team_id' => $this->team1->id]);

        $this->farmManager->teams()->attach($this->team1->id, ['role_id' => $farmManagerRole->id]);
        $this->farmManager->update(['current_team_id' => $this->team1->id]);

        $this->partner->teams()->attach($this->team1->id, ['role_id' => $partnerRole->id]);
        $this->partner->update(['current_team_id' => $this->team1->id]);

        $this->fieldWorker->teams()->attach($this->team1->id, ['role_id' => $fieldWorkerRole->id]);
        $this->fieldWorker->update(['current_team_id' => $this->team1->id]);
    }

    /**
     * Test super admin can view any expense.
     */
    public function test_super_admin_can_view_any_expense(): void
    {
        $expense = Expense::factory()->create([
            'team_id' => $this->team1->id,
            'category' => ExpenseCategory::Feed,
        ]);

        expect($this->superAdmin->can('view', $expense))->toBeTrue();
    }

    /**
     * Test farm manager can view expense in their team.
     */
    public function test_farm_manager_can_view_expense_in_their_team(): void
    {
        $expense = Expense::factory()->create([
            'team_id' => $this->team1->id,
            'category' => ExpenseCategory::Feed,
        ]);

        expect($this->farmManager->can('view', $expense))->toBeTrue();
    }

    /**
     * Test farm manager cannot view expense from other team.
     */
    public function test_farm_manager_cannot_view_expense_from_other_team(): void
    {
        $expense = Expense::factory()->create([
            'team_id' => $this->team2->id,
            'category' => ExpenseCategory::Feed,
        ]);

        expect($this->farmManager->can('view', $expense))->toBeFalse();
    }

    /**
     * Test partner can view expense in their team.
     */
    public function test_partner_can_view_expense_in_their_team(): void
    {
        $expense = Expense::factory()->create([
            'team_id' => $this->team1->id,
            'category' => ExpenseCategory::Feed,
        ]);

        expect($this->partner->can('view', $expense))->toBeTrue();
    }

    /**
     * Test farm manager can create expense.
     */
    public function test_farm_manager_can_create_expense(): void
    {
        expect($this->farmManager->can('create', Expense::class))->toBeTrue();
    }

    /**
     * Test field worker cannot create expense.
     */
    public function test_field_worker_cannot_create_expense(): void
    {
        expect($this->fieldWorker->can('create', Expense::class))->toBeFalse();
    }

    /**
     * Test farm manager can update expense in their team.
     */
    public function test_farm_manager_can_update_expense_in_their_team(): void
    {
        $expense = Expense::factory()->create([
            'team_id' => $this->team1->id,
            'category' => ExpenseCategory::Feed,
        ]);

        expect($this->farmManager->can('update', $expense))->toBeTrue();
    }

    /**
     * Test farm manager cannot update expense from other team.
     */
    public function test_farm_manager_cannot_update_expense_from_other_team(): void
    {
        $expense = Expense::factory()->create([
            'team_id' => $this->team2->id,
            'category' => ExpenseCategory::Feed,
        ]);

        expect($this->farmManager->can('update', $expense))->toBeFalse();
    }

    /**
     * Test farm manager can delete expense in their team.
     */
    public function test_farm_manager_can_delete_expense_in_their_team(): void
    {
        $expense = Expense::factory()->create([
            'team_id' => $this->team1->id,
            'category' => ExpenseCategory::Feed,
        ]);

        expect($this->farmManager->can('delete', $expense))->toBeTrue();
    }

    /**
     * Test super admin can view any expenses.
     */
    public function test_super_admin_can_view_any_expenses(): void
    {
        expect($this->superAdmin->can('viewAny', Expense::class))->toBeTrue();
    }

    /**
     * Test partner can view any expenses in their team.
     */
    public function test_partner_can_view_any_expenses(): void
    {
        expect($this->partner->can('viewAny', Expense::class))->toBeTrue();
    }
}
