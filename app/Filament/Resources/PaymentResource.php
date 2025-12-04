<?php

namespace App\Filament\Resources;

use App\Filament\Resources\PaymentResource\Pages;
use BackedEnum;
use Domains\Finance\Enums\PaymentMethod;
use Domains\Finance\Models\Payment;
use Filament\Actions;
use Filament\Forms;
use Filament\Resources\Resource;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;
use Filament\Tables;
use Filament\Tables\Table;
use UnitEnum;

class PaymentResource extends Resource
{
    protected static ?string $model = Payment::class;

    protected static string|BackedEnum|null $navigationIcon = 'heroicon-o-credit-card';

    protected static string|UnitEnum|null $navigationGroup = 'Finance';

    protected static ?int $navigationSort = 2;

    protected static ?string $recordTitleAttribute = 'id';

    public static function form(Schema $form): Schema
    {
        return $form->schema([
            Section::make('Payment Information')
                ->description('Record invoice payment details')
                ->schema([
                    Forms\Components\Select::make('invoice_id')
                        ->relationship('invoice', 'invoice_number')
                        ->required()
                        ->searchable()
                        ->helperText('Select invoice to record payment'),
                    Forms\Components\TextInput::make('amount')
                        ->required()
                        ->numeric()
                        ->step(0.01)
                        ->minValue(0)
                        ->helperText('Payment amount in BWP'),
                    Forms\Components\Select::make('payment_method')
                        ->options(PaymentMethod::class)
                        ->required()
                        ->default(PaymentMethod::Cash),
                    Forms\Components\TextInput::make('reference')
                        ->maxLength(100)
                        ->helperText('Reference number (check #, transaction ID, etc.)'),
                    Forms\Components\DatePicker::make('payment_date')
                        ->required()
                        ->default(now()),
                ])->columns(2),

            Section::make('Additional Information')
                ->schema([
                    Forms\Components\Select::make('recorded_by')
                        ->relationship('recordedBy', 'name')
                        ->required()
                        ->searchable()
                        ->helperText('User recording this payment'),
                    Forms\Components\Textarea::make('notes')
                        ->maxLength(500)
                        ->helperText('Additional notes or details'),
                ]),
        ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('invoice.invoice_number')
                    ->label('Invoice')
                    ->searchable()
                    ->sortable(),
                Tables\Columns\TextColumn::make('invoice.customer.name')
                    ->label('Customer')
                    ->searchable(),
                Tables\Columns\TextColumn::make('amount')
                    ->label('Amount')
                    ->formatStateUsing(fn ($state) => 'BWP '.number_format($state / 100, 2))
                    ->sortable()
                    ->numeric(),
                Tables\Columns\TextColumn::make('payment_method')
                    ->badge()
                    ->colors([
                        'gray' => PaymentMethod::Cash->value,
                        'info' => PaymentMethod::Bank->value,
                        'warning' => PaymentMethod::Cheque->value,
                        'success' => PaymentMethod::Mobile->value,
                    ])
                    ->sortable(),
                Tables\Columns\TextColumn::make('reference')
                    ->searchable(),
                Tables\Columns\TextColumn::make('recorded_by.name')
                    ->label('Recorded By')
                    ->searchable(),
                Tables\Columns\TextColumn::make('payment_date')
                    ->dateTime()
                    ->sortable(),
                Tables\Columns\TextColumn::make('created_at')
                    ->dateTime()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
            ])
            ->filters([
                Tables\Filters\SelectFilter::make('payment_method')
                    ->options(PaymentMethod::class),
                Tables\Filters\Filter::make('payment_date')
                    ->form([
                        Forms\Components\DatePicker::make('from'),
                        Forms\Components\DatePicker::make('to'),
                    ])
                    ->query(function ($query, array $data) {
                        return $query
                            ->when($data['from'] ?? null, fn ($q) => $q->whereDate('payment_date', '>=', $data['from']))
                            ->when($data['to'] ?? null, fn ($q) => $q->whereDate('payment_date', '<=', $data['to']));
                    }),
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
            'index' => Pages\ListPayments::route('/'),
            'create' => Pages\CreatePayment::route('/create'),
            'edit' => Pages\EditPayment::route('/{record}/edit'),
        ];
    }
}
