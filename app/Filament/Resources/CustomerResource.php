<?php

namespace App\Filament\Resources;

use App\Filament\Resources\CustomerResource\Pages;
use Domains\CRM\Models\Customer;
use Domains\Shared\Enums\CustomerType;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;

class CustomerResource extends Resource
{
    protected static ?string $model = Customer::class;

    protected static ?string $navigationIcon = 'heroicon-o-user-group';

    protected static ?int $navigationSort = 1;

    protected static ?string $navigationGroup = 'CRM';

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\Section::make('Customer Information')
                    ->description('Basic customer details')
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
                        Forms\Components\Select::make('type')
                            ->options(CustomerType::class)
                            ->required()
                            ->default(CustomerType::Retail),
                    ])->columns(2),

                Forms\Components\Section::make('Financial Terms')
                    ->description('Credit and payment information')
                    ->schema([
                        Forms\Components\TextInput::make('credit_limit')
                            ->numeric()
                            ->minValue(0)
                            ->helperText('Credit limit in cents/thebe'),
                        Forms\Components\Textarea::make('payment_terms')
                            ->helperText('e.g., Net 30, COD, 50% deposit'),
                    ])->columns(2),

                Forms\Components\Section::make('Additional Information')
                    ->schema([
                        Forms\Components\Textarea::make('notes')
                            ->maxLength(500)
                            ->columnSpanFull(),
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
                Tables\Columns\BadgeColumn::make('type')
                    ->colors([
                        'primary' => CustomerType::Wholesale->value,
                        'success' => CustomerType::Retail->value,
                    ]),
                Tables\Columns\TextColumn::make('credit_limit')
                    ->numeric()
                    ->label('Credit Limit')
                    ->formatStateUsing(fn($state) => $state ? number_format($state / 100, 2) . ' BWP' : '—'),
                Tables\Columns\TextColumn::make('created_at')
                    ->dateTime()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
            ])
            ->filters([
                Tables\Filters\SelectFilter::make('type')
                    ->options(CustomerType::class),
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
            'index' => Pages\ListCustomers::route('/'),
            'create' => Pages\CreateCustomer::route('/create'),
            'edit' => Pages\EditCustomer::route('/{record}/edit'),
        ];
    }
}
