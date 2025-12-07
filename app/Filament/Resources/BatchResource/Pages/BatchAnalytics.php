<?php

namespace App\Filament\Resources\BatchResource\Pages;

use App\Filament\Resources\BatchResource;
use App\Filament\Resources\BatchResource\Widgets\BatchStatsOverview;
use App\Filament\Resources\BatchResource\Widgets\FeedConsumptionChart;
use App\Filament\Resources\BatchResource\Widgets\MortalityChart;
use Domains\Broiler\Services\BatchCalculationService;
use Filament\Actions\Action;
use Filament\Resources\Pages\Page;
use Filament\Support\Enums\IconPosition;

class BatchAnalytics extends Page
{
    protected static string $resource = BatchResource::class;

    protected string $view = 'filament.resources.batch-resource.pages.batch-analytics';

    public $record;

    protected $calculationService;

    public function mount($record): void
    {
        $this->record = $this->getRecord();
        $this->calculationService = resolve(BatchCalculationService::class);
    }

    public function getRecord(): mixed
    {
        return BatchResource::getModel()::query()->findOrFail(request()->route('record'));
    }

    protected function getHeaderWidgets(): array
    {
        return [
            BatchStatsOverview::class,
            MortalityChart::class,
            FeedConsumptionChart::class,
        ];
    }

    public function getTitle(): string
    {
        return 'Analytics - '.$this->record->name;
    }

    protected function getHeaderActions(): array
    {
        return [
            Action::make('back')
                ->label('Back to Batch')
                ->icon('heroicon-o-arrow-left')
                ->iconPosition(IconPosition::Before)
                ->url(fn () => BatchResource::getUrl('view', ['record' => $this->record]))
                ->color('gray'),
        ];
    }
}
