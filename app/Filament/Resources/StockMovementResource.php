<?php

namespace App\Filament\Resources;

use App\Filament\Resources\StockMovementResource\Pages;
use BackedEnum;
use Domains\Inventory\Enums\MovementType;
use Domains\Inventory\Models\StockMovement;
use Filament\Resources\Resource;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;
use Filament\Tables;
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
                    Schema\Components\TextInput::make('product.name')
                        ->label('Product')
                        ->readOnly(),
                    Schema\Components\TextInput::make('warehouse.name')
                        ->label('Warehouse')
                        ->readOnly(),
                    Schema\Components\TextInput::make('quantity')
                        ->numeric()
                        ->readOnly(),
                    Schema\Components\TextInput::make('movement_type')
                        ->readOnly(),
                    Schema\Components\TextInput::make('reason')
                        ->readOnly(),
                    Schema\Components\Textarea::make('notes')
                        ->readOnly(),
                    Schema\Components\TextInput::make('recorded_by.name')
                        ->label('Recorded By')
                        ->readOnly(),
                    Schema\Components\TextInput::make('created_at')
                        ->dateTime()
                        ->readOnly(),
                ])->columns(2),
        ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('product.name')
                    ->label('Product')
                    ->searchable()
                    ->sortable(),
                Tables\Columns\TextColumn::make('warehouse.name')
                    ->label('Warehouse')
                    ->searchable(),
                Tables\Columns\TextColumn::make('quantity')
                    ->numeric()
                    ->sortable(),
                Tables\Columns\TextColumn::make('movement_type')
                    ->badge()
                    ->colors([
                        'success' => MovementType::In->value,
                        'danger' => MovementType::Out->value,
                        'warning' => MovementType::Adjustment->value,
                        'info' => MovementType::Transfer->value,
                    ])
                    ->sortable(),
                Tables\Columns\TextColumn::make('reason')
                    ->searchable(),
                Tables\Columns\TextColumn::make('recorded_by.name')
                    ->label('Recorded By')
                    ->searchable(),
                Tables\Columns\TextColumn::make('created_at')
                    ->dateTime()
                    ->sortable(),
            ])
            ->filters([
                Tables\Filters\SelectFilter::make('movement_type')
                    ->options(MovementType::class),
                Tables\Filters\SelectFilter::make('product_id')
                    ->relationship('product', 'name')
                    ->searchable(),
                Tables\Filters\SelectFilter::make('warehouse_id')
                    ->relationship('warehouse', 'name'),
                Tables\Filters\Filter::make('created_at')
                    ->form([
                        Tables\Columns\TextColumn::make('from'),
                        Tables\Columns\TextColumn::make('to'),
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
            'index' => Pages\ListStockMovements::route('/'),
            'view' => Pages\ViewStockMovement::route('/{record}'),
        ];
    }
}
