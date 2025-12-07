<?php

namespace App\Filament\Pages;

use Domains\CRM\Models\Customer;
use Domains\CRM\Models\Supplier;
use Domains\Finance\Models\Expense;
use Filament\Facades\Filament;
use Filament\Pages\Dashboard as BaseDashboard;
use Filament\Widgets\StatsOverviewWidget\Stat;

class Dashboard extends BaseDashboard
{
    public function getStats(): array
    {
        $team = Filament::getTenant();

        if (! $team) {
            return [];
        }

        $customersCount = Customer::query()->count();
        $suppliersCount = Supplier::query()->count();

        $totalExpenses = Expense::query()
            ->sum('amount') / 100;

        $expensesThisMonth = Expense::query()
            ->whereYear('created_at', now()->year)
            ->whereMonth('created_at', now()->month)
            ->sum('amount') / 100;

        return [
            Stat::make('Customers', $customersCount)
                ->description('Registered in farm')
                ->icon('heroicon-o-user-group')
                ->color('info'),

            Stat::make('Suppliers', $suppliersCount)
                ->description('Global reference data')
                ->icon('heroicon-o-truck')
                ->color('success'),

            Stat::make('Total Expenses', 'BWP '.number_format($totalExpenses, 2))
                ->description('All time')
                ->icon('heroicon-o-receipt-percent')
                ->color('warning'),

            Stat::make('This Month', 'BWP '.number_format($expensesThisMonth, 2))
                ->description(now()->format('F Y'))
                ->icon('heroicon-o-calendar')
                ->color('danger'),
        ];
    }
}
