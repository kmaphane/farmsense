<?php

namespace Domains\Shared\Tests\Feature;

use Domains\Auth\Models\Team;
use Domains\Auth\Models\User;
use Domains\Auth\Seeders\RoleAndPermissionSeeder;
use Domains\CRM\Models\Customer;
use Domains\Finance\Models\Expense;
use Domains\Shared\Enums\CustomerType;
use Domains\Shared\Enums\ExpenseCategory;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Spatie\Permission\Models\Role;
use Tests\TestCase;

class MultiTenancyScopingTest extends TestCase
{
    use RefreshDatabase;

    protected User $user;

    protected Team $team1;

    protected Team $team2;

    protected function setUp(): void
    {
        parent::setUp();
        $this->seed(RoleAndPermissionSeeder::class);

        $superAdminRole = Role::findByName('Super Admin', 'web');

        $this->user = User::factory()->create();
        $this->user->assignRole($superAdminRole);

        $this->team1 = Team::factory()->create();
        $this->team2 = Team::factory()->create();

        // Assign user to both teams
        $this->user->teams()->attach($this->team1->id, ['role_id' => $superAdminRole->id]);
        $this->user->teams()->attach($this->team2->id, ['role_id' => $superAdminRole->id]);
    }

    /**
     * Test that customers are automatically scoped to current team.
     */
    public function test_customers_are_scoped_to_current_team(): void
    {
        // Create customers in different teams
        $customer1 = Customer::factory()->create([
            'team_id' => $this->team1->id,
            'type' => CustomerType::Wholesale,
        ]);
        $customer2 = Customer::factory()->create([
            'team_id' => $this->team2->id,
            'type' => CustomerType::Retail,
        ]);

        // Set current team to team1
        $this->user->update(['current_team_id' => $this->team1->id]);
        request()->merge(['team_id' => $this->team1->id]);

        // Query customers
        $customers = Customer::query()->get();

        expect($customers->count())->toBe(1);
        expect($customers->first()->id)->toBe($customer1->id);
    }

    /**
     * Test that expenses are automatically scoped to current team.
     */
    public function test_expenses_are_scoped_to_current_team(): void
    {
        // Create expenses in different teams
        $expense1 = Expense::factory()->create([
            'team_id' => $this->team1->id,
            'category' => ExpenseCategory::Feed,
        ]);
        $expense2 = Expense::factory()->create([
            'team_id' => $this->team2->id,
            'category' => ExpenseCategory::Labor,
        ]);

        // Set current team to team1
        $this->user->update(['current_team_id' => $this->team1->id]);
        request()->merge(['team_id' => $this->team1->id]);

        // Query expenses
        $expenses = Expense::query()->get();

        expect($expenses->count())->toBe(1);
        expect($expenses->first()->id)->toBe($expense1->id);
    }

    /**
     * Test that scopeWithoutTeamScope bypasses team filtering.
     */
    public function test_without_team_scope_returns_all_records(): void
    {
        // Create customers in different teams
        Customer::factory()->create([
            'team_id' => $this->team1->id,
            'type' => CustomerType::Wholesale,
        ]);
        Customer::factory()->create([
            'team_id' => $this->team2->id,
            'type' => CustomerType::Retail,
        ]);

        // Set current team to team1
        $this->user->update(['current_team_id' => $this->team1->id]);
        request()->merge(['team_id' => $this->team1->id]);

        // Query without team scope
        $customers = Customer::query()->withoutTeamScope()->get();

        expect($customers->count())->toBe(2);
    }

    /**
     * Test that scopeBelongsToTeam filters by specific team.
     */
    public function test_belongs_to_team_scope_filters_by_team(): void
    {
        // Create customers in different teams
        $customer1 = Customer::factory()->create([
            'team_id' => $this->team1->id,
            'type' => CustomerType::Wholesale,
        ]);
        $customer2 = Customer::factory()->create([
            'team_id' => $this->team2->id,
            'type' => CustomerType::Retail,
        ]);

        // Query with explicit team scope
        $customers = Customer::query()->belongsToTeam($this->team2->id)->get();

        expect($customers->count())->toBe(1);
        expect($customers->first()->id)->toBe($customer2->id);
    }

    /**
     * Test that switching teams updates context for queries.
     */
    public function test_switching_teams_changes_query_scope(): void
    {
        $customer1 = Customer::factory()->create([
            'team_id' => $this->team1->id,
            'type' => CustomerType::Wholesale,
        ]);
        $customer2 = Customer::factory()->create([
            'team_id' => $this->team2->id,
            'type' => CustomerType::Retail,
        ]);

        // Query from team1
        $this->user->update(['current_team_id' => $this->team1->id]);
        request()->merge(['team_id' => $this->team1->id]);
        $customersTeam1 = Customer::query()->get();

        expect($customersTeam1->count())->toBe(1);
        expect($customersTeam1->first()->id)->toBe($customer1->id);

        // Switch to team2
        $this->user->update(['current_team_id' => $this->team2->id]);
        request()->merge(['team_id' => $this->team2->id]);
        $customersTeam2 = Customer::query()->get();

        expect($customersTeam2->count())->toBe(1);
        expect($customersTeam2->first()->id)->toBe($customer2->id);
    }
}
