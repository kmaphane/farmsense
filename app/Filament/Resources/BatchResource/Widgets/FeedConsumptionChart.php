<?php

namespace App\Filament\Resources\BatchResource\Widgets;

use Domains\Broiler\Models\Batch;
use Filament\Widgets\ChartWidget;
use Illuminate\Database\Eloquent\Model;

class FeedConsumptionChart extends ChartWidget
{
    protected ?string $heading = 'Feed Consumption Trend';

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
            ->orderBy('log_date')
            ->get();

        return [
            'datasets' => [
                [
                    'label' => 'Feed Consumed (kg)',
                    'data' => $dailyLogs->pluck('feed_consumed_kg')->toArray(),
                    'borderColor' => 'rgb(59, 130, 246)',
                    'backgroundColor' => 'rgba(59, 130, 246, 0.1)',
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
