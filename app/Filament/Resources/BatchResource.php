<?php

namespace App\Filament\Resources;

use App\Filament\Resources\BatchResource\Pages\BatchAnalytics;
use App\Filament\Resources\BatchResource\Pages\CreateBatch;
use App\Filament\Resources\BatchResource\Pages\EditBatch;
use App\Filament\Resources\BatchResource\Pages\ListBatches;
use App\Filament\Resources\BatchResource\Pages\ViewBatch;
use App\Filament\Resources\BatchResource\RelationManagers\DailyLogsRelationManager;
use App\Filament\Resources\BatchResource\RelationManagers\ExpensesRelationManager;
use BackedEnum;
use Domains\Broiler\Enums\BatchStatus;
use Domains\Broiler\Models\Batch;
use Filament\Actions\CreateAction;
use Filament\Actions\EditAction;
use Filament\Actions\ViewAction;
use Filament\Forms\Components\DatePicker;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Resources\Resource;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\Filter;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Table;
use UnitEnum;

class BatchResource extends Resource
{
    protected static ?string $model = Batch::class;

    protected static string|BackedEnum|null $navigationIcon = 'heroicon-o-calendar-date-range';

    protected static ?int $navigationSort = 1;

    protected static string|UnitEnum|null $navigationGroup = 'Broiler Management';

    protected static ?string $recordTitleAttribute = 'name';

    public static function form(Schema $form): Schema
    {
        return $form
            ->schema([
                Section::make('Batch Information')
                    ->description('Basic batch details and planning')
                    ->schema([
                        TextInput::make('name')
                            ->required()
                            ->maxLength(255)
                            ->autofocus()
                            ->helperText('e.g., "Spring 2025 Batch"'),
                        TextInput::make('batch_number')
                            ->required()
                            ->unique(ignoreRecord: true)
                            ->maxLength(255)
                            ->helperText('Auto-generated batch identifier'),
                        Select::make('status')
                            ->options(BatchStatus::class)
                            ->required()
                            ->default(BatchStatus::Planned)
                            ->disabled(fn ($record) => $record !== null),
                        Select::make('supplier_id')
                            ->relationship('supplier', 'name')
                            ->searchable()
                            ->preload()
                            ->helperText('Chick supplier'),
                    ])->columns(2),

                Section::make('Schedule')
                    ->schema([
                        DatePicker::make('start_date')
                            ->required()
                            ->default(now())
                            ->native(false),
                        DatePicker::make('expected_end_date')
                            ->native(false)
                            ->helperText('Typical broiler cycle: 35-42 days'),
                        DatePicker::make('actual_end_date')
                            ->native(false)
                            ->disabled()
                            ->helperText('Set automatically when batch is closed'),
                    ])->columns(3),

                Section::make('Flock Details')
                    ->schema([
                        TextInput::make('initial_quantity')
                            ->required()
                            ->numeric()
                            ->minValue(1)
                            ->helperText('Number of chicks received'),
                        TextInput::make('current_quantity')
                            ->numeric()
                            ->disabled()
                            ->helperText('Updated automatically from daily logs'),
                        TextInput::make('target_weight_kg')
                            ->numeric()
                            ->step(0.01)
                            ->minValue(0)
                            ->helperText('Target harvest weight per bird (kg)'),
                        TextInput::make('average_weight_kg')
                            ->numeric()
                            ->step(0.01)
                            ->minValue(0)
                            ->disabled(fn ($record) => $record?->status !== BatchStatus::Closed)
                            ->helperText('Final average weight (set at closure)'),
                    ])->columns(2),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('batch_number')
                    ->searchable()
                    ->sortable(),
                TextColumn::make('name')
                    ->searchable()
                    ->sortable(),
                TextColumn::make('status')
                    ->badge()
                    ->sortable(),
                TextColumn::make('start_date')
                    ->date()
                    ->sortable(),
                TextColumn::make('current_quantity')
                    ->numeric()
                    ->sortable()
                    ->label('Birds'),
                TextColumn::make('age_in_days')
                    ->label('Age (days)')
                    ->sortable(query: function ($query, string $direction) {
                        return $query->orderBy('start_date', $direction === 'asc' ? 'desc' : 'asc');
                    }),
                TextColumn::make('supplier.name')
                    ->searchable()
                    ->toggleable(isToggledHiddenByDefault: true),
                TextColumn::make('created_at')
                    ->dateTime()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
            ])
            ->defaultSort('start_date', 'desc')
            ->filters([
                SelectFilter::make('status')
                    ->options(BatchStatus::class)
                    ->multiple(),
                Filter::make('start_date')
                    ->form([
                        DatePicker::make('from')->native(false),
                        DatePicker::make('to')->native(false),
                    ])
                    ->query(function ($query, array $data) {
                        return $query
                            ->when($data['from'] ?? null,
                                fn ($q) => $q->whereDate('start_date', '>=', $data['from'])
                            )
                            ->when($data['to'] ?? null,
                                fn ($q) => $q->whereDate('start_date', '<=', $data['to'])
                            );
                    }),
            ])
            ->recordActions([
                ViewAction::make(),
                EditAction::make(),
            ])
            ->toolbarActions([
                CreateAction::make(),
            ]);
    }

    public static function getRelations(): array
    {
        return [
            DailyLogsRelationManager::class,
            ExpensesRelationManager::class,
        ];
    }

    public static function getPages(): array
    {
        return [
            'index' => ListBatches::route('/'),
            'create' => CreateBatch::route('/create'),
            'view' => ViewBatch::route('/{record}'),
            'edit' => EditBatch::route('/{record}/edit'),
            'analytics' => BatchAnalytics::route('/{record}/analytics'),
        ];
    }
}
