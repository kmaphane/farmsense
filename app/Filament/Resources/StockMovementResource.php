<?php

namespace App\Filament\Resources;

use App\Filament\Resources\StockMovementResource\Pages\ListStockMovements;
use App\Filament\Resources\StockMovementResource\Pages\ViewStockMovement;
use BackedEnum;
use Domains\Inventory\Enums\MovementType;
use Domains\Inventory\Models\StockMovement;
use Filament\Resources\Resource;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;
use Filament\Schemas\Schema\Components\Textarea;
use Filament\Schemas\Schema\Components\TextInput;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\Filter;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Table;
use UnitEnum;

class StockMovementResource extends Resource
{
    protected static ?string $model = StockMovement::class;

    protected static string|BackedEnum|null $navigationIcon = 'heroicon-o-arrow-path';

    protected static string|UnitEnum|null $navigationGroup = 'Inventory';

    protected static ?int $navigationSort = 3;

    protected static ?string $recordTitleAttribute = 'id';

    public static function form(Schema $form): Schema
    {
        return $form->schema([
            Section::make('Movement Details')
                ->description('Stock movement audit record (read-only)')
                ->schema([
                    TextInput::make('product.name')
                        ->label('Product')
                        ->readOnly(),
                    TextInput::make('warehouse.name')
                        ->label('Warehouse')
                        ->readOnly(),
                    TextInput::make('quantity')
                        ->numeric()
                        ->readOnly(),
                    TextInput::make('movement_type')
                        ->readOnly(),
                    TextInput::make('reason')
                        ->readOnly(),
                    Textarea::make('notes')
                        ->readOnly(),
                    TextInput::make('recorded_by.name')
                        ->label('Recorded By')
                        ->readOnly(),
                    TextInput::make('created_at')
                        ->dateTime()
                        ->readOnly(),
                ])->columns(2),
        ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('product.name')
                    ->label('Product')
                    ->searchable()
                    ->sortable(),
                TextColumn::make('warehouse.name')
                    ->label('Warehouse')
                    ->searchable(),
                TextColumn::make('quantity')
                    ->numeric()
                    ->sortable(),
                TextColumn::make('movement_type')
                    ->badge()
                    ->colors([
                        'success' => MovementType::In->value,
                        'danger' => MovementType::Out->value,
                        'warning' => MovementType::Adjustment->value,
                        'info' => MovementType::Transfer->value,
                    ])
                    ->sortable(),
                TextColumn::make('reason')
                    ->searchable(),
                TextColumn::make('recorded_by.name')
                    ->label('Recorded By')
                    ->searchable(),
                TextColumn::make('created_at')
                    ->dateTime()
                    ->sortable(),
            ])
            ->filters([
                SelectFilter::make('movement_type')
                    ->options(MovementType::class),
                SelectFilter::make('product_id')
                    ->relationship('product', 'name')
                    ->searchable(),
                SelectFilter::make('warehouse_id')
                    ->relationship('warehouse', 'name'),
                Filter::make('created_at')
                    ->form([
                        TextColumn::make('from'),
                        TextColumn::make('to'),
                    ])
                    ->query(function ($query, array $data) {
                        return $query
                            ->when($data['from'] ?? null, fn ($q) => $q->whereDate('created_at', '>=', $data['from']))
                            ->when($data['to'] ?? null, fn ($q) => $q->whereDate('created_at', '<=', $data['to']));
                    }),
            ])
            ->recordActions([])
            ->toolbarActions([]);
    }

    public static function getRelations(): array
    {
        return [];
    }

    public static function getPages(): array
    {
        return [
            'index' => ListStockMovements::route('/'),
            'view' => ViewStockMovement::route('/{record}'),
        ];
    }
}
