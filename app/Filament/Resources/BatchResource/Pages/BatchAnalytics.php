<?php

namespace App\Filament\Resources\BatchResource\Pages;

use App\Filament\Resources\BatchResource;
use Domains\Broiler\Services\BatchCalculationService;
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
        $this->calculationService = app(BatchCalculationService::class);
    }

    public function getRecord(): mixed
    {
        return BatchResource::getModel()::findOrFail(request()->route('record'));
    }

    protected function getHeaderWidgets(): array
    {
        return [
            BatchResource\Widgets\BatchStatsOverview::class,
            BatchResource\Widgets\MortalityChart::class,
            BatchResource\Widgets\FeedConsumptionChart::class,
        ];
    }

    public function getTitle(): string
    {
        return 'Analytics - ' . $this->record->name;
    }

    protected function getHeaderActions(): array
    {
        return [
            \Filament\Actions\Action::make('back')
                ->label('Back to Batch')
                ->icon('heroicon-o-arrow-left')
                ->iconPosition(IconPosition::Before)
                ->url(fn () => BatchResource::getUrl('view', ['record' => $this->record]))
                ->color('gray'),
        ];
    }
}
