<?php

namespace App\Filament\Resources;

use App\Filament\Resources\SupplierResource\Pages;
use Domains\CRM\Models\Supplier;
use Domains\Shared\Enums\SupplierCategory;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;

class SupplierResource extends Resource
{
    protected static ?string $model = Supplier::class;

    protected static ?string $navigationIcon = 'heroicon-o-truck';

    protected static ?int $navigationSort = 2;

    protected static ?string $navigationGroup = 'CRM';

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\Section::make('Supplier Information')
                    ->description('Global supplier reference data accessible to all teams')
                    ->schema([
                        Forms\Components\TextInput::make('name')
                            ->required()
                            ->maxLength(255)
                            ->autofocus(),
                        Forms\Components\TextInput::make('email')
                            ->email()
                            ->maxLength(255),
                        Forms\Components\TextInput::make('phone')
                            ->tel()
                            ->maxLength(20),
                        Forms\Components\Select::make('category')
                            ->options(SupplierCategory::class)
                            ->required()
                            ->default(SupplierCategory::Feed),
                    ])->columns(2),

                Forms\Components\Section::make('Performance & Pricing')
                    ->schema([
                        Forms\Components\Slider::make('performance_rating')
                            ->minValue(1)
                            ->maxValue(5)
                            ->step(0.5)
                            ->helperText('Rate supplier quality 1-5 stars'),
                        Forms\Components\TextInput::make('current_price_per_unit')
                            ->numeric()
                            ->step(0.01)
                            ->minValue(0)
                            ->helperText('Current market price for budgeting reference (Phase 2+ API integration)'),
                        Forms\Components\Toggle::make('is_active')
                            ->default(true)
                            ->helperText('Inactive suppliers won\'t appear in selection dropdowns'),
                    ])->columns(2),

                Forms\Components\Section::make('Notes')
                    ->schema([
                        Forms\Components\Textarea::make('notes')
                            ->maxLength(500)
                            ->helperText('Payment terms, special handling, contacts, etc.'),
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
                Tables\Columns\TextColumn::make('email')
                    ->searchable()
                    ->icon('heroicon-m-envelope'),
                Tables\Columns\TextColumn::make('phone')
                    ->searchable()
                    ->icon('heroicon-m-phone'),
                Tables\Columns\BadgeColumn::make('category')
                    ->colors([
                        'primary' => SupplierCategory::Feed->value,
                        'success' => SupplierCategory::Chicks->value,
                        'warning' => SupplierCategory::Meds->value,
                    ]),
                Tables\Columns\TextColumn::make('performance_rating')
                    ->label('Rating')
                    ->formatStateUsing(fn($state) => $state ? str_repeat('★', (int) $state) . str_repeat('☆', 5 - (int) $state) : '—'),
                Tables\Columns\TextColumn::make('current_price_per_unit')
                    ->label('Current Price')
                    ->formatStateUsing(fn($state) => $state ? 'BWP ' . number_format($state, 2) : '—')
                    ->sortable()
                    ->toggleable(),
                Tables\Columns\IconColumn::make('is_active')
                    ->label('Active')
                    ->boolean()
                    ->sortable()
                    ->toggleable(),
                Tables\Columns\TextColumn::make('created_at')
                    ->dateTime()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
            ])
            ->filters([
                Tables\Filters\SelectFilter::make('category')
                    ->options(SupplierCategory::class),
                Tables\Filters\TernaryFilter::make('is_active')
                    ->nullable(),
            ])
            ->actions([
                Tables\Actions\EditAction::make(),
                Tables\Actions\DeleteAction::make(),
            ])
            ->bulkActions([
                Tables\Actions\BulkActionGroup::make([
                    Tables\Actions\DeleteBulkAction::make(),
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
            'index' => Pages\ListSuppliers::route('/'),
            'create' => Pages\CreateSupplier::route('/create'),
            'edit' => Pages\EditSupplier::route('/{record}/edit'),
        ];
    }
}
