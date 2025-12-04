<?php

namespace App\Filament\Resources;

use App\Filament\Resources\InvoiceResource\Pages;
use BackedEnum;
use Domains\Finance\Enums\InvoiceStatus;
use Domains\Finance\Models\Invoice;
use Filament\Actions;
use Filament\Forms;
use Filament\Resources\Resource;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;
use Filament\Tables;
use Filament\Tables\Table;
use UnitEnum;

class InvoiceResource extends Resource
{
    protected static ?string $model = Invoice::class;

    protected static string|BackedEnum|null $navigationIcon = 'heroicon-o-document';

    protected static string|UnitEnum|null $navigationGroup = 'Finance';

    protected static ?int $navigationSort = 1;

    protected static ?string $recordTitleAttribute = 'invoice_number';

    public static function form(Schema $form): Schema
    {
        return $form->schema([
            Section::make('Invoice Information')
                ->description('Define invoice details and customer information')
                ->schema([
                    Forms\Components\TextInput::make('invoice_number')
                        ->required()
                        ->unique()
                        ->maxLength(50)
                        ->helperText('Unique invoice identifier')
                        ->autofocus(),
                    Forms\Components\Select::make('customer_id')
                        ->relationship('customer', 'name')
                        ->required()
                        ->searchable(),
                    Forms\Components\Select::make('status')
                        ->options(InvoiceStatus::class)
                        ->required()
                        ->default(InvoiceStatus::Draft),
                    Forms\Components\DatePicker::make('due_date')
                        ->helperText('Payment due date'),
                ])->columns(2),

            Section::make('Invoice Items')
                ->description('Add line items for this invoice')
                ->schema([
                    Forms\Components\Repeater::make('lineItems')
                        ->relationship('lineItems')
                        ->schema([
                            Forms\Components\Select::make('product_id')
                                ->relationship('product', 'name')
                                ->searchable()
                                ->helperText('Optional - link to product'),
                            Forms\Components\TextInput::make('description')
                                ->required()
                                ->maxLength(255)
                                ->helperText('Item description'),
                            Forms\Components\TextInput::make('quantity')
                                ->required()
                                ->numeric()
                                ->default(1)
                                ->minValue(0.01)
                                ->step(0.01),
                            Forms\Components\TextInput::make('unit_price')
                                ->required()
                                ->numeric()
                                ->step(0.01)
                                ->helperText('Price per unit'),
                        ])
                        ->columns(2)
                        ->collapsible()
                        ->collapsed(false)
                        ->minItems(1)
                        ->addActionLabel('Add item'),
                ])->collapsed(false),

            Section::make('Totals & Notes')
                ->schema([
                    Forms\Components\TextInput::make('subtotal')
                        ->numeric()
                        ->readOnly()
                        ->helperText('Auto-calculated from line items'),
                    Forms\Components\TextInput::make('tax_amount')
                        ->numeric()
                        ->default(0)
                        ->helperText('Tax or additional charges'),
                    Forms\Components\TextInput::make('total_amount')
                        ->numeric()
                        ->readOnly()
                        ->helperText('Subtotal + Tax'),
                    Forms\Components\Textarea::make('description')
                        ->maxLength(500)
                        ->helperText('General invoice description'),
                    Forms\Components\Textarea::make('notes')
                        ->maxLength(1000)
                        ->helperText('Additional notes or terms'),
                ])->columns(2),
        ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('invoice_number')
                    ->searchable()
                    ->sortable(),
                Tables\Columns\TextColumn::make('customer.name')
                    ->label('Customer')
                    ->searchable(),
                Tables\Columns\TextColumn::make('status')
                    ->badge()
                    ->colors([
                        'gray' => InvoiceStatus::Draft->value,
                        'info' => InvoiceStatus::Sent->value,
                        'success' => InvoiceStatus::Paid->value,
                        'danger' => InvoiceStatus::Overdue->value,
                        'warning' => InvoiceStatus::Cancelled->value,
                    ])
                    ->sortable(),
                Tables\Columns\TextColumn::make('total_amount')
                    ->label('Total')
                    ->formatStateUsing(fn ($state) => 'BWP '.number_format($state / 100, 2))
                    ->sortable()
                    ->numeric(),
                Tables\Columns\TextColumn::make('due_date')
                    ->dateTime()
                    ->sortable(),
                Tables\Columns\TextColumn::make('created_at')
                    ->dateTime()
                    ->sortable(),
            ])
            ->filters([
                Tables\Filters\SelectFilter::make('status')
                    ->options(InvoiceStatus::class),
                Tables\Filters\Filter::make('due_date')
                    ->form([
                        Forms\Components\DatePicker::make('from'),
                        Forms\Components\DatePicker::make('to'),
                    ])
                    ->query(function ($query, array $data) {
                        return $query
                            ->when($data['from'] ?? null, fn ($q) => $q->whereDate('due_date', '>=', $data['from']))
                            ->when($data['to'] ?? null, fn ($q) => $q->whereDate('due_date', '<=', $data['to']));
                    }),
            ])
            ->recordActions([
                Actions\ViewAction::make(),
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
            'index' => Pages\ListInvoices::route('/'),
            'create' => Pages\CreateInvoice::route('/create'),
            'edit' => Pages\EditInvoice::route('/{record}/edit'),
            'view' => Pages\ViewInvoice::route('/{record}'),
        ];
    }
}
