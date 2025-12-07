<?php

namespace App\Filament\Resources;

use App\Filament\Resources\ProductResource\Pages\CreateProduct;
use App\Filament\Resources\ProductResource\Pages\EditProduct;
use App\Filament\Resources\ProductResource\Pages\ListProducts;
use BackedEnum;
use Domains\Inventory\Enums\ProductType;
use Domains\Inventory\Models\Product;
use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteAction;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Toggle;
use Filament\Resources\Resource;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;
use Filament\Tables\Columns\IconColumn;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Filters\TernaryFilter;
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
                    TextInput::make('name')
                        ->required()
                        ->maxLength(255)
                        ->autofocus(),
                    TextInput::make('description')
                        ->maxLength(500),
                    Select::make('type')
                        ->options(ProductType::class)
                        ->required()
                        ->default(ProductType::Other),
                    TextInput::make('unit')
                        ->required()
                        ->default('bag')
                        ->maxLength(50),
                ])->columns(2),

            Section::make('Stock Management')
                ->schema([
                    TextInput::make('quantity_on_hand')
                        ->numeric()
                        ->default(0)
                        ->helperText('Current stock quantity'),
                    TextInput::make('reorder_level')
                        ->numeric()
                        ->default(0)
                        ->helperText('Alert level for reordering'),
                    TextInput::make('unit_cost')
                        ->numeric()
                        ->step(0.01)
                        ->helperText('Cost per unit in currency'),
                ])->columns(3),

            Section::make('Status')
                ->schema([
                    Toggle::make('is_active')
                        ->default(true)
                        ->helperText('Inactive products won\'t appear in selections'),
                ]),
        ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('name')
                    ->searchable()
                    ->sortable(),
                TextColumn::make('type')
                    ->badge()
                    ->colors([
                        'primary' => ProductType::Feed->value,
                        'warning' => ProductType::Medicine->value,
                        'info' => ProductType::Packaging->value,
                    ]),
                TextColumn::make('unit')
                    ->label('Unit'),
                TextColumn::make('quantity_on_hand')
                    ->label('Stock')
                    ->numeric()
                    ->sortable(),
                TextColumn::make('reorder_level')
                    ->label('Reorder Level')
                    ->numeric(),
                TextColumn::make('unit_cost')
                    ->label('Unit Cost')
                    ->formatStateUsing(fn ($state) => $state ? 'BWP '.number_format($state / 100, 2) : '—'),
                IconColumn::make('is_active')
                    ->boolean()
                    ->sortable(),
            ])
            ->filters([
                SelectFilter::make('type')
                    ->options(ProductType::class),
                TernaryFilter::make('is_active')
                    ->nullable(),
            ])
            ->recordActions([
                EditAction::make(),
                DeleteAction::make(),
            ])
            ->toolbarActions([
                BulkActionGroup::make([
                    DeleteBulkAction::make(),
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
            'index' => ListProducts::route('/'),
            'create' => CreateProduct::route('/create'),
            'edit' => EditProduct::route('/{record}/edit'),
        ];
    }
}
