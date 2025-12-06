<?php

namespace App\Filament\Resources\BatchResource\Widgets;

use Domains\Broiler\Models\Batch;
use Domains\Broiler\Services\BatchCalculationService;
use Filament\Widgets\StatsOverviewWidget as BaseWidget;
use Filament\Widgets\StatsOverviewWidget\Stat;
use Illuminate\Database\Eloquent\Model;

class BatchStatsOverview extends BaseWidget
{
    public ?Model $record = null;

    protected function getStats(): array
    {
        if (! $this->record instanceof Batch) {
            return [];
        }

        $service = app(BatchCalculationService::class);
        $stats = $service->getBatchStatistics($this->record);

        return [
            Stat::make('Age', $stats['age_in_days'] . ' days')
                ->description('Days since start')
                ->icon('heroicon-o-calendar')
                ->color('info'),

            Stat::make('Current Quantity', number_format($stats['current_quantity']))
                ->description('Birds alive')
                ->icon('heroicon-o-home-modern')
                ->color('success'),

            Stat::make('Mortality Rate', $stats['mortality_rate'] . '%')
                ->description($stats['total_mortality'] . ' deaths total')
                ->icon('heroicon-o-exclamation-triangle')
                ->color($stats['mortality_rate'] > 5 ? 'danger' : 'success'),

            Stat::make('Feed Consumed', number_format($stats['total_feed_consumed'], 2) . ' kg')
                ->description('Total feed used')
                ->icon('heroicon-o-cube')
                ->color('warning'),

            Stat::make('FCR', $stats['fcr'])
                ->description('Feed Conversion Ratio (target: 1.6-1.9)')
                ->icon('heroicon-o-chart-bar')
                ->color($stats['fcr'] >= 1.6 && $stats['fcr'] <= 1.9 ? 'success' : 'warning'),

            Stat::make('EPEF', $stats['epef'])
                ->description('Production Efficiency (target: 300-400)')
                ->icon('heroicon-o-trophy')
                ->color($stats['epef'] >= 300 ? 'success' : 'warning'),
        ];
    }
}
