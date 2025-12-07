<?php

namespace App\Filament\Resources;

use App\Filament\Resources\ExpenseResource\Pages\CreateExpense;
use App\Filament\Resources\ExpenseResource\Pages\EditExpense;
use App\Filament\Resources\ExpenseResource\Pages\ListExpenses;
use BackedEnum;
use Domains\Finance\Models\Expense;
use Domains\Shared\Enums\ExpenseCategory;
use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteAction;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Forms\Components\DatePicker;
use Filament\Forms\Components\FileUpload;
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

class ExpenseResource extends Resource
{
    protected static ?string $model = Expense::class;

    protected static string|BackedEnum|null $navigationIcon = 'heroicon-o-receipt-percent';

    protected static ?int $navigationSort = 1;

    protected static string|UnitEnum|null $navigationGroup = 'Finance';

    protected static ?string $recordTitleAttribute = 'description';

    public static function form(Schema $form): Schema
    {
        return $form
            ->schema([
                Section::make('Expense Details')
                    ->description('Record a new expense transaction')
                    ->schema([
                        TextInput::make('description')
                            ->required()
                            ->maxLength(255)
                            ->autofocus()
                            ->placeholder('e.g., Feed delivery from ABC Supplies'),
                        TextInput::make('amount')
                            ->required()
                            ->numeric()
                            ->minValue(0)
                            ->inputMode('decimal')
                            ->helperText('Amount in currency units (will be converted to cents internally)'),
                        Select::make('currency')
                            ->options(['BWP' => 'BWP (Pula)', 'USD' => 'USD (Dollar)'])
                            ->default('BWP')
                            ->required(),
                        Select::make('category')
                            ->options(ExpenseCategory::class)
                            ->required()
                            ->searchable(),
                    ])->columns(2),

                Section::make('Allocation (Optional)')
                    ->description('Allocate expense to a specific batch or leave blank for general farm expenses')
                    ->schema([
                        TextInput::make('allocatable_type')
                            ->disabled()
                            ->helperText('Set automatically when allocating to a batch'),
                        TextInput::make('allocatable_id')
                            ->numeric()
                            ->helperText('Batch ID - Phase 3: UI will provide batch selector'),
                    ])->columns(2),

                Section::make('Receipt & OCR')
                    ->description('OCR functionality in Phase 2')
                    ->schema([
                        FileUpload::make('receipt_path')
                            ->directory('expenses/receipts')
                            ->acceptedFileTypes(['image/jpeg', 'image/png', 'image/webp'])
                            ->maxSize(5120)
                            ->helperText('Max 5MB. OCR extraction in Phase 2.'),
                    ]),

                Section::make('Notes')
                    ->schema([
                        Textarea::make('notes')
                            ->maxLength(500),
                    ]),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('description')
                    ->searchable()
                    ->sortable(),
                TextColumn::make('amount')
                    ->label('Amount')
                    ->formatStateUsing(fn ($state) => number_format($state / 100, 2).' BWP')
                    ->numeric()
                    ->sortable(),
                TextColumn::make('category')
                    ->badge()
                    ->colors([
                        'primary' => ExpenseCategory::Feed->value,
                        'warning' => ExpenseCategory::Labor->value,
                        'secondary' => ExpenseCategory::Utilities->value,
                        'success' => ExpenseCategory::Equipment->value,
                    ]),
                TextColumn::make('allocatable_type')
                    ->label('Allocated To')
                    ->formatStateUsing(fn ($state) => $state ? str_replace('Domains\\Broiler\\Models\\', '', $state) : 'General')
                    ->badge()
                    ->color('gray')
                    ->toggleable(),
                TextColumn::make('created_at')
                    ->dateTime()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
            ])
            ->filters([
                SelectFilter::make('category')
                    ->options(ExpenseCategory::class),
                Filter::make('created_at')
                    ->form([
                        DatePicker::make('created_from'),
                        DatePicker::make('created_until'),
                    ])
                    ->query(function ($query, array $data): void {
                        $query
                            ->when(
                                $data['created_from'],
                                fn ($q) => $q->whereDate('created_at', '>=', $data['created_from']),
                            )
                            ->when(
                                $data['created_until'],
                                fn ($q) => $q->whereDate('created_at', '<=', $data['created_until']),
                            );
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
        return [
            //
        ];
    }

    public static function getPages(): array
    {
        return [
            'index' => ListExpenses::route('/'),
            'create' => CreateExpense::route('/create'),
            'edit' => EditExpense::route('/{record}/edit'),
        ];
    }
}
