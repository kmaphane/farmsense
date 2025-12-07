<?php

namespace App\Filament\Resources\BatchResource\RelationManagers;

use Filament\Actions\CreateAction;
use Filament\Actions\DeleteAction;
use Filament\Actions\EditAction;
use Filament\Actions\ViewAction;
use Filament\Forms\Components\DatePicker;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Resources\RelationManagers\RelationManager;
use Filament\Schemas\Schema;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\Filter;
use Filament\Tables\Table;
use Illuminate\Support\Facades\Auth;

class DailyLogsRelationManager extends RelationManager
{
    protected static string $relationship = 'dailyLogs';

    protected static ?string $title = 'Daily Logs';

    protected static ?string $recordTitleAttribute = 'log_date';

    public function table(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('log_date')
                    ->date()
                    ->sortable(),
                TextColumn::make('mortality_count')
                    ->numeric()
                    ->label('Mortality'),
                TextColumn::make('feed_consumed_kg')
                    ->numeric(decimalPlaces: 2)
                    ->label('Feed (kg)'),
                TextColumn::make('water_consumed_liters')
                    ->numeric(decimalPlaces: 2)
                    ->label('Water (L)')
                    ->toggleable(),
                TextColumn::make('temperature_celsius')
                    ->numeric(decimalPlaces: 1)
                    ->label('Temp (°C)')
                    ->toggleable(),
                TextColumn::make('humidity_percent')
                    ->numeric(decimalPlaces: 1)
                    ->label('Humidity (%)')
                    ->toggleable(),
                TextColumn::make('ammonia_ppm')
                    ->numeric(decimalPlaces: 1)
                    ->label('Ammonia (PPM)')
                    ->toggleable(),
                TextColumn::make('recorder.name')
                    ->label('Recorded By')
                    ->toggleable(isToggledHiddenByDefault: true),
            ])
            ->defaultSort('log_date', 'desc')
            ->filters([
                Filter::make('log_date')
                    ->schema([
                        DatePicker::make('from')->native(false),
                        DatePicker::make('to')->native(false),
                    ])
                    ->query(fn ($query, array $data) => $query
                        ->when($data['from'] ?? null,
                            fn ($q) => $q->whereDate('log_date', '>=', $data['from'])
                        )
                        ->when($data['to'] ?? null,
                            fn ($q) => $q->whereDate('log_date', '<=', $data['to'])
                        )
                    ),
            ])
            ->recordActions([
                ViewAction::make(),
                EditAction::make()
                    ->disabled(fn ($record) => ! $record->isEditable()),
                DeleteAction::make()
                    ->disabled(fn ($record) => ! $record->isEditable()),
            ])
            ->toolbarActions([
                CreateAction::make()
                    ->mutateFormDataUsing(function (array $data) {
                        $data['team_id'] = Auth::user()->current_team_id;
                        $data['batch_id'] = $this->getOwnerRecord()->id;
                        $data['recorded_by'] = Auth::id();

                        return $data;
                    })
                    ->after(function ($record) {
                        // Update batch current_quantity after creating a daily log
                        $batch = $this->getOwnerRecord();
                        $previousQuantity = $batch->current_quantity ?? $batch->initial_quantity;
                        $newQuantity = $previousQuantity - $record->mortality_count;
                        $batch->update(['current_quantity' => max(0, $newQuantity)]);
                    }),
            ]);
    }

    public function form(Schema $form): Schema
    {
        return $form
            ->schema([
                DatePicker::make('log_date')
                    ->required()
                    ->default(now())
                    ->native(false)
                    ->unique(
                        table: 'daily_logs',
                        column: 'log_date',
                        modifyRuleUsing: fn ($rule) => $rule->where('batch_id', $this->getOwnerRecord()->id),
                        ignoreRecord: true
                    )
                    ->helperText('One log per day per batch'),
                TextInput::make('mortality_count')
                    ->required()
                    ->numeric()
                    ->default(0)
                    ->minValue(0)
                    ->label('Mortality Count'),
                TextInput::make('feed_consumed_kg')
                    ->required()
                    ->numeric()
                    ->step(0.01)
                    ->minValue(0)
                    ->label('Feed Consumed (kg)'),
                TextInput::make('water_consumed_liters')
                    ->numeric()
                    ->step(0.01)
                    ->minValue(0)
                    ->label('Water Consumed (Liters)'),
                TextInput::make('temperature_celsius')
                    ->numeric()
                    ->step(0.1)
                    ->label('Temperature (°C)'),
                TextInput::make('humidity_percent')
                    ->numeric()
                    ->step(0.1)
                    ->minValue(0)
                    ->maxValue(100)
                    ->label('Humidity (%)'),
                TextInput::make('ammonia_ppm')
                    ->numeric()
                    ->step(0.1)
                    ->minValue(0)
                    ->label('Ammonia (PPM)')
                    ->helperText('Safe levels: < 25 PPM'),
                Textarea::make('notes')
                    ->maxLength(500)
                    ->columnSpanFull(),
            ])
            ->columns(2);
    }
}
