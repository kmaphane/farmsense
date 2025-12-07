<?php

namespace App\Filament\Resources;

use App\Filament\Resources\InvoiceResource\Pages\CreateInvoice;
use App\Filament\Resources\InvoiceResource\Pages\EditInvoice;
use App\Filament\Resources\InvoiceResource\Pages\ListInvoices;
use App\Filament\Resources\InvoiceResource\Pages\ViewInvoice;
use BackedEnum;
use Domains\Finance\Enums\InvoiceStatus;
use Domains\Finance\Models\Invoice;
use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteAction;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Actions\ViewAction;
use Filament\Forms\Components\DatePicker;
use Filament\Forms\Components\Repeater;
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
                    TextInput::make('invoice_number')
                        ->required()
                        ->unique()
                        ->maxLength(50)
                        ->helperText('Unique invoice identifier')
                        ->autofocus(),
                    Select::make('customer_id')
                        ->relationship('customer', 'name')
                        ->required()
                        ->searchable(),
                    Select::make('status')
                        ->options(InvoiceStatus::class)
                        ->required()
                        ->default(InvoiceStatus::Draft),
                    DatePicker::make('due_date')
                        ->helperText('Payment due date'),
                ])->columns(2),

            Section::make('Invoice Items')
                ->description('Add line items for this invoice')
                ->schema([
                    Repeater::make('lineItems')
                        ->relationship('lineItems')
                        ->schema([
                            Select::make('product_id')
                                ->relationship('product', 'name')
                                ->searchable()
                                ->helperText('Optional - link to product'),
                            TextInput::make('description')
                                ->required()
                                ->maxLength(255)
                                ->helperText('Item description'),
                            TextInput::make('quantity')
                                ->required()
                                ->numeric()
                                ->default(1)
                                ->minValue(0.01)
                                ->step(0.01),
                            TextInput::make('unit_price')
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
                    TextInput::make('subtotal')
                        ->numeric()
                        ->readOnly()
                        ->helperText('Auto-calculated from line items'),
                    TextInput::make('tax_amount')
                        ->numeric()
                        ->default(0)
                        ->helperText('Tax or additional charges'),
                    TextInput::make('total_amount')
                        ->numeric()
                        ->readOnly()
                        ->helperText('Subtotal + Tax'),
                    Textarea::make('description')
                        ->maxLength(500)
                        ->helperText('General invoice description'),
                    Textarea::make('notes')
                        ->maxLength(1000)
                        ->helperText('Additional notes or terms'),
                ])->columns(2),
        ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('invoice_number')
                    ->searchable()
                    ->sortable(),
                TextColumn::make('customer.name')
                    ->label('Customer')
                    ->searchable(),
                TextColumn::make('status')
                    ->badge()
                    ->colors([
                        'gray' => InvoiceStatus::Draft->value,
                        'info' => InvoiceStatus::Sent->value,
                        'success' => InvoiceStatus::Paid->value,
                        'danger' => InvoiceStatus::Overdue->value,
                        'warning' => InvoiceStatus::Cancelled->value,
                    ])
                    ->sortable(),
                TextColumn::make('total_amount')
                    ->label('Total')
                    ->formatStateUsing(fn ($state) => 'BWP '.number_format($state / 100, 2))
                    ->sortable()
                    ->numeric(),
                TextColumn::make('due_date')
                    ->dateTime()
                    ->sortable(),
                TextColumn::make('created_at')
                    ->dateTime()
                    ->sortable(),
            ])
            ->filters([
                SelectFilter::make('status')
                    ->options(InvoiceStatus::class),
                Filter::make('due_date')
                    ->form([
                        DatePicker::make('from'),
                        DatePicker::make('to'),
                    ])
                    ->query(function ($query, array $data) {
                        return $query
                            ->when($data['from'] ?? null, fn ($q) => $q->whereDate('due_date', '>=', $data['from']))
                            ->when($data['to'] ?? null, fn ($q) => $q->whereDate('due_date', '<=', $data['to']));
                    }),
            ])
            ->recordActions([
                ViewAction::make(),
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
            'index' => ListInvoices::route('/'),
            'create' => CreateInvoice::route('/create'),
            'edit' => EditInvoice::route('/{record}/edit'),
            'view' => ViewInvoice::route('/{record}'),
        ];
    }
}
