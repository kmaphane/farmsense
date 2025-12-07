<?php

namespace App\Filament\Resources;

use App\Filament\Resources\PaymentResource\Pages\CreatePayment;
use App\Filament\Resources\PaymentResource\Pages\EditPayment;
use App\Filament\Resources\PaymentResource\Pages\ListPayments;
use BackedEnum;
use Domains\Finance\Enums\PaymentMethod;
use Domains\Finance\Models\Payment;
use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteAction;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Forms\Components\DatePicker;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Resources\Resource;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\Filter;
use Filament\Tables\Filters\SelectFilter;
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
                    Select::make('invoice_id')
                        ->relationship('invoice', 'invoice_number')
                        ->required()
                        ->searchable()
                        ->helperText('Select invoice to record payment'),
                    TextInput::make('amount')
                        ->required()
                        ->numeric()
                        ->step(0.01)
                        ->minValue(0)
                        ->helperText('Payment amount in BWP'),
                    Select::make('payment_method')
                        ->options(PaymentMethod::class)
                        ->required()
                        ->default(PaymentMethod::Cash),
                    TextInput::make('reference')
                        ->maxLength(100)
                        ->helperText('Reference number (check #, transaction ID, etc.)'),
                    DatePicker::make('payment_date')
                        ->required()
                        ->default(now()),
                ])->columns(2),

            Section::make('Additional Information')
                ->schema([
                    Select::make('recorded_by')
                        ->relationship('recordedBy', 'name')
                        ->required()
                        ->searchable()
                        ->helperText('User recording this payment'),
                    Textarea::make('notes')
                        ->maxLength(500)
                        ->helperText('Additional notes or details'),
                ]),
        ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('invoice.invoice_number')
                    ->label('Invoice')
                    ->searchable()
                    ->sortable(),
                TextColumn::make('invoice.customer.name')
                    ->label('Customer')
                    ->searchable(),
                TextColumn::make('amount')
                    ->label('Amount')
                    ->formatStateUsing(fn ($state) => 'BWP '.number_format($state / 100, 2))
                    ->sortable()
                    ->numeric(),
                TextColumn::make('payment_method')
                    ->badge()
                    ->colors([
                        'gray' => PaymentMethod::Cash->value,
                        'info' => PaymentMethod::Bank->value,
                        'warning' => PaymentMethod::Cheque->value,
                        'success' => PaymentMethod::Mobile->value,
                    ])
                    ->sortable(),
                TextColumn::make('reference')
                    ->searchable(),
                TextColumn::make('recorded_by.name')
                    ->label('Recorded By')
                    ->searchable(),
                TextColumn::make('payment_date')
                    ->dateTime()
                    ->sortable(),
                TextColumn::make('created_at')
                    ->dateTime()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
            ])
            ->filters([
                SelectFilter::make('payment_method')
                    ->options(PaymentMethod::class),
                Filter::make('payment_date')
                    ->form([
                        DatePicker::make('from'),
                        DatePicker::make('to'),
                    ])
                    ->query(function ($query, array $data) {
                        return $query
                            ->when($data['from'] ?? null, fn ($q) => $q->whereDate('payment_date', '>=', $data['from']))
                            ->when($data['to'] ?? null, fn ($q) => $q->whereDate('payment_date', '<=', $data['to']));
                    }),
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
            'index' => ListPayments::route('/'),
            'create' => CreatePayment::route('/create'),
            'edit' => EditPayment::route('/{record}/edit'),
        ];
    }
}
