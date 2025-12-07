<?php

namespace App\Filament\Resources\BatchResource\Widgets;

use Domains\Broiler\Models\Batch;
use Filament\Widgets\ChartWidget;
use Illuminate\Database\Eloquent\Model;

class MortalityChart extends ChartWidget
{
    protected ?string $heading = 'Daily Mortality Trend';

    public ?Model $record = null;

    protected function getData(): array
    {
        if (! $this->record instanceof Batch) {
            return [
                'datasets' => [],
                'labels' => [],
            ];
        }

        $dailyLogs = $this->record->dailyLogs()
            ->oldest('log_date')
            ->get();

        return [
            'datasets' => [
                [
                    'label' => 'Mortality Count',
                    'data' => $dailyLogs->pluck('mortality_count')->toArray(),
                    'borderColor' => 'rgb(239, 68, 68)',
                    'backgroundColor' => 'rgba(239, 68, 68, 0.1)',
                    'tension' => 0.3,
                ],
            ],
            'labels' => $dailyLogs->pluck('log_date')->map(fn ($date) => $date->format('M d'))->toArray(),
        ];
    }

    protected function getType(): string
    {
        return 'line';
    }
}
