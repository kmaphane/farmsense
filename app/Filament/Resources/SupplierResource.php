<?php

namespace App\Filament\Resources;

use App\Filament\Resources\SupplierResource\Pages\CreateSupplier;
use App\Filament\Resources\SupplierResource\Pages\EditSupplier;
use App\Filament\Resources\SupplierResource\Pages\ListSuppliers;
use BackedEnum;
use Domains\CRM\Models\Supplier;
use Domains\Shared\Enums\SupplierCategory;
use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteAction;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Slider;
use Filament\Forms\Components\Textarea;
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

class SupplierResource extends Resource
{
    protected static ?string $model = Supplier::class;

    protected static bool $isScopedToTenant = false;

    protected static string|BackedEnum|null $navigationIcon = 'heroicon-o-truck';

    protected static ?int $navigationSort = 2;

    protected static string|UnitEnum|null $navigationGroup = 'CRM';

    protected static ?string $recordTitleAttribute = 'name';

    public static function form(Schema $form): Schema
    {
        return $form
            ->schema([
                Section::make('Supplier Information')
                    ->description('Global supplier reference data accessible to all teams')
                    ->schema([
                        TextInput::make('name')
                            ->required()
                            ->maxLength(255)
                            ->autofocus(),
                        TextInput::make('email')
                            ->email()
                            ->maxLength(255),
                        TextInput::make('phone')
                            ->tel()
                            ->maxLength(20),
                        Select::make('category')
                            ->options(SupplierCategory::class)
                            ->required()
                            ->default(SupplierCategory::Feed),
                    ])->columns(2),

                Section::make('Performance & Pricing')
                    ->schema([
                        Slider::make('performance_rating')
                            ->minValue(1)
                            ->maxValue(5)
                            ->step(0.5)
                            ->helperText('Rate supplier quality 1-5 stars'),
                        TextInput::make('current_price_per_unit')
                            ->numeric()
                            ->step(0.01)
                            ->minValue(0)
                            ->helperText('Current market price for budgeting reference (Phase 2+ API integration)'),
                        Toggle::make('is_active')
                            ->default(true)
                            ->helperText('Inactive suppliers won\'t appear in selection dropdowns'),
                    ])->columns(2),

                Section::make('Notes')
                    ->schema([
                        Textarea::make('notes')
                            ->maxLength(500)
                            ->helperText('Payment terms, special handling, contacts, etc.'),
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
                TextColumn::make('email')
                    ->searchable()
                    ->icon('heroicon-m-envelope'),
                TextColumn::make('phone')
                    ->searchable()
                    ->icon('heroicon-m-phone'),
                TextColumn::make('category')
                    ->badge()
                    ->colors([
                        'primary' => SupplierCategory::Feed->value,
                        'success' => SupplierCategory::Chicks->value,
                        'warning' => SupplierCategory::Meds->value,
                    ]),
                TextColumn::make('performance_rating')
                    ->label('Rating')
                    ->formatStateUsing(fn ($state) => $state ? str_repeat('★', (int) $state).str_repeat('☆', 5 - (int) $state) : '—'),
                TextColumn::make('current_price_per_unit')
                    ->label('Current Price')
                    ->formatStateUsing(fn ($state) => $state ? 'BWP '.number_format($state, 2) : '—')
                    ->sortable()
                    ->toggleable(),
                IconColumn::make('is_active')
                    ->label('Active')
                    ->boolean()
                    ->sortable()
                    ->toggleable(),
                TextColumn::make('created_at')
                    ->dateTime()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
            ])
            ->filters([
                SelectFilter::make('category')
                    ->options(SupplierCategory::class),
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
        return [
            //
        ];
    }

    public static function getPages(): array
    {
        return [
            'index' => ListSuppliers::route('/'),
            'create' => CreateSupplier::route('/create'),
            'edit' => EditSupplier::route('/{record}/edit'),
        ];
    }
}
