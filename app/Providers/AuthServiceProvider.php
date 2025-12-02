<?php

namespace App\Providers;

// use Illuminate\Support\Facades\Gate;
use App\Models\User;
use Domains\Auth\Models\Team;
use Domains\Auth\Policies\TeamPolicy;
use Domains\Auth\Policies\UserPolicy;
use Domains\CRM\Models\Customer;
use Domains\CRM\Models\Supplier;
use Domains\CRM\Policies\CustomerPolicy;
use Domains\CRM\Policies\SupplierPolicy;
use Domains\Finance\Models\Expense;
use Domains\Finance\Policies\ExpensePolicy;
use Illuminate\Foundation\Support\Providers\AuthServiceProvider as ServiceProvider;

class AuthServiceProvider extends ServiceProvider
{
    /**
     * The model to policy mappings for the application.
     *
     * @var array<class-string, class-string>
     */
    protected $policies = [
        User::class => UserPolicy::class,
        Team::class => TeamPolicy::class,
        Customer::class => CustomerPolicy::class,
        Supplier::class => SupplierPolicy::class,
        Expense::class => ExpensePolicy::class,
    ];

    /**
     * Register any authentication / authorization services.
     */
    public function boot(): void
    {
        //
    }
}
