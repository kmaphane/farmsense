<?php

namespace App\Filament\Resources\BatchResource\Pages;

use App\Filament\Resources\BatchResource;
use Domains\Broiler\Actions\CloseBatchAction;
use Domains\Broiler\Enums\BatchStatus;
use Domains\Broiler\Services\BatchCalculationService;
use Filament\Actions;
use Filament\Forms;
use Filament\Notifications\Notification;
use Filament\Resources\Pages\ViewRecord;
use Filament\Support\Enums\Width;

class ViewBatch extends ViewRecord
{
    protected static string $resource = BatchResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\Action::make('analytics')
                ->label('View Analytics')
                ->icon('heroicon-o-chart-bar')
                ->url(fn () => BatchResource::getUrl('analytics', ['record' => $this->record]))
                ->color('info'),
            Actions\Action::make('start_batch')
                ->label('Start Batch')
                ->icon('heroicon-o-play')
                ->requiresConfirmation()
                ->visible(fn () => $this->record->status === BatchStatus::Planned)
                ->action(function () {
                    $action = app(CloseBatchAction::class);
                    $action->transitionToActive($this->record);
                    Notification::make()
                        ->success()
                        ->title('Batch Started')
                        ->body('The batch is now active.')
                        ->send();
                }),
            Actions\Action::make('start_harvesting')
                ->label('Start Harvesting')
                ->icon('heroicon-o-scissors')
                ->requiresConfirmation()
                ->visible(fn () => $this->record->status === BatchStatus::Active)
                ->action(function () {
                    $action = app(CloseBatchAction::class);
                    $action->transitionToHarvesting($this->record);
                    Notification::make()
                        ->success()
                        ->title('Harvesting Started')
                        ->body('The batch is now in harvesting phase.')
                        ->send();
                }),
            Actions\Action::make('close_batch')
                ->label('Close Batch')
                ->icon('heroicon-o-lock-closed')
                ->requiresConfirmation()
                ->modalWidth(Width::Medium)
                ->visible(fn () => $this->record->status === BatchStatus::Harvesting)
                ->form([
                    Forms\Components\TextInput::make('average_weight_kg')
                        ->label('Average Weight (kg)')
                        ->required()
                        ->numeric()
                        ->step(0.01)
                        ->minValue(0)
                        ->helperText('Enter the final average weight per bird'),
                ])
                ->action(function (array $data) {
                    $action = app(CloseBatchAction::class);
                    $action->execute($this->record, $data['average_weight_kg']);
                    Notification::make()
                        ->success()
                        ->title('Batch Closed')
                        ->body('The batch has been closed successfully.')
                        ->send();
                }),
            Actions\EditAction::make(),
        ];
    }

    protected function getHeaderWidgets(): array
    {
        return [
            BatchResource\Widgets\BatchStatsOverview::class,
        ];
    }
}
