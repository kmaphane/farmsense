<?php

namespace App\Filament\Pages;

use Domains\Auth\Models\Team;
use Domains\CRM\Models\Customer;
use Domains\CRM\Models\Supplier;
use Domains\Finance\Models\Expense;
use Filament\Pages\Dashboard as BaseDashboard;
use Filament\Widgets\StatsOverviewWidget\Stat;
use Illuminate\Support\Facades\Auth;

class Dashboard extends BaseDashboard
{
    public function getStats(): array
    {
        $user = Auth::user();
        $team = $user->currentTeam;

        if (! $team) {
            return [];
        }

        $customersCount = Customer::query()->where('team_id', $team->id)->count();
        $suppliersCount = Supplier::query()->count();

        $totalExpenses = Expense::query()->where('team_id', $team->id)
            ->sum('amount') / 100;

        $expensesThisMonth = Expense::query()->where('team_id', $team->id)
            ->whereYear('created_at', now()->year)
            ->whereMonth('created_at', now()->month)
            ->sum('amount') / 100;

        $teamsCount = Team::query()->count();

        return [
            Stat::make('Team', $team->name)
                ->description('Current team context')
                ->icon('heroicon-o-building-office')
                ->color('primary'),

            Stat::make('Customers', $customersCount)
                ->description('Registered in team')
                ->icon('heroicon-o-user-group')
                ->color('info'),

            Stat::make('Suppliers', $suppliersCount)
                ->description('Global reference data')
                ->icon('heroicon-o-truck')
                ->color('success'),

            Stat::make('Total Expenses', 'BWP '.number_format($totalExpenses, 2))
                ->description('All time (team-scoped)')
                ->icon('heroicon-o-receipt-percent')
                ->color('warning'),

            Stat::make('This Month', 'BWP '.number_format($expensesThisMonth, 2))
                ->description(now()->format('F Y'))
                ->icon('heroicon-o-calendar')
                ->color('danger'),

            Stat::make('Teams', $teamsCount)
                ->description('In system')
                ->icon('heroicon-o-building-office')
                ->color('gray'),
        ];
    }
}
