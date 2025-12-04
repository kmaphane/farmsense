<?php

namespace App\Filament\Resources;

use App\Filament\Resources\ProductResource\Pages;
use BackedEnum;
use Domains\Inventory\Enums\ProductType;
use Domains\Inventory\Models\Product;
use Filament\Actions;
use Filament\Forms;
use Filament\Resources\Resource;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;
use Filament\Tables;
use Filament\Tables\Table;
use UnitEnum;

class ProductResource extends Resource
{
    protected static ?string $model = Product::class;

    protected static string|BackedEnum|null $navigationIcon = 'heroicon-o-cube';

    protected static string|UnitEnum|null $navigationGroup = 'Inventory';

    protected static ?int $navigationSort = 1;

    protected static ?string $recordTitleAttribute = 'name';

    public static function form(Schema $form): Schema
    {
        return $form->schema([
            Section::make('Product Information')
                ->description('Define product details and stock parameters')
                ->schema([
                    Forms\Components\TextInput::make('name')
                        ->required()
                        ->maxLength(255)
                        ->autofocus(),
                    Forms\Components\TextInput::make('description')
                        ->maxLength(500),
                    Forms\Components\Select::make('type')
                        ->options(ProductType::class)
                        ->required()
                        ->default(ProductType::Other),
                    Forms\Components\TextInput::make('unit')
                        ->required()
                        ->default('bag')
                        ->maxLength(50),
                ])->columns(2),

            Section::make('Stock Management')
                ->schema([
                    Forms\Components\TextInput::make('quantity_on_hand')
                        ->numeric()
                        ->default(0)
                        ->helperText('Current stock quantity'),
                    Forms\Components\TextInput::make('reorder_level')
                        ->numeric()
                        ->default(0)
                        ->helperText('Alert level for reordering'),
                    Forms\Components\TextInput::make('unit_cost')
                        ->numeric()
                        ->step(0.01)
                        ->helperText('Cost per unit in currency'),
                ])->columns(3),

            Section::make('Status')
                ->schema([
                    Forms\Components\Toggle::make('is_active')
                        ->default(true)
                        ->helperText('Inactive products won\'t appear in selections'),
                ]),
        ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('name')
                    ->searchable()
                    ->sortable(),
                Tables\Columns\TextColumn::make('type')
                    ->badge()
                    ->colors([
                        'primary' => ProductType::Feed->value,
                        'warning' => ProductType::Medicine->value,
                        'info' => ProductType::Packaging->value,
                    ]),
                Tables\Columns\TextColumn::make('unit')
                    ->label('Unit'),
                Tables\Columns\TextColumn::make('quantity_on_hand')
                    ->label('Stock')
                    ->numeric()
                    ->sortable(),
                Tables\Columns\TextColumn::make('reorder_level')
                    ->label('Reorder Level')
                    ->numeric(),
                Tables\Columns\TextColumn::make('unit_cost')
                    ->label('Unit Cost')
                    ->formatStateUsing(fn ($state) => $state ? 'BWP '.number_format($state / 100, 2) : '—'),
                Tables\Columns\IconColumn::make('is_active')
                    ->boolean()
                    ->sortable(),
            ])
            ->filters([
                Tables\Filters\SelectFilter::make('type')
                    ->options(ProductType::class),
                Tables\Filters\TernaryFilter::make('is_active')
                    ->nullable(),
            ])
            ->recordActions([
                Actions\EditAction::make(),
                Actions\DeleteAction::make(),
            ])
            ->toolbarActions([
                Actions\BulkActionGroup::make([
                    Actions\DeleteBulkAction::make(),
                ]),
            ]);
    }

    public static function getRelations(): array
    {
        return [];
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListProducts::route('/'),
            'create' => Pages\CreateProduct::route('/create'),
            'edit' => Pages\EditProduct::route('/{record}/edit'),
        ];
    }
}
