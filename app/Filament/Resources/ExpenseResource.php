<?php

namespace App\Filament\Resources;

use App\Filament\Resources\ExpenseResource\Pages;
use BackedEnum;
use Domains\Finance\Models\Expense;
use Domains\Shared\Enums\ExpenseCategory;
use Filament\Actions;
use Filament\Forms;
use Filament\Resources\Resource;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;
use Filament\Tables;
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
                        Forms\Components\TextInput::make('description')
                            ->required()
                            ->maxLength(255)
                            ->autofocus()
                            ->placeholder('e.g., Feed delivery from ABC Supplies'),
                        Forms\Components\TextInput::make('amount')
                            ->required()
                            ->numeric()
                            ->minValue(0)
                            ->inputMode('decimal')
                            ->helperText('Amount in currency units (will be converted to cents internally)'),
                        Forms\Components\Select::make('currency')
                            ->options(['BWP' => 'BWP (Pula)', 'USD' => 'USD (Dollar)'])
                            ->default('BWP')
                            ->required(),
                        Forms\Components\Select::make('category')
                            ->options(ExpenseCategory::class)
                            ->required()
                            ->searchable(),
                    ])->columns(2),

                Section::make('Allocation (Optional)')
                    ->description('Allocate expense to a specific batch or leave blank for general farm expenses')
                    ->schema([
                        Forms\Components\TextInput::make('allocatable_type')
                            ->disabled()
                            ->helperText('Set automatically when allocating to a batch'),
                        Forms\Components\TextInput::make('allocatable_id')
                            ->numeric()
                            ->helperText('Batch ID - Phase 3: UI will provide batch selector'),
                    ])->columns(2),

                Section::make('Receipt & OCR')
                    ->description('OCR functionality in Phase 2')
                    ->schema([
                        Forms\Components\FileUpload::make('receipt_path')
                            ->directory('expenses/receipts')
                            ->acceptedFileTypes(['image/jpeg', 'image/png', 'image/webp'])
                            ->maxSize(5120)
                            ->helperText('Max 5MB. OCR extraction in Phase 2.'),
                    ]),

                Section::make('Notes')
                    ->schema([
                        Forms\Components\Textarea::make('notes')
                            ->maxLength(500),
                    ]),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('description')
                    ->searchable()
                    ->sortable(),
                Tables\Columns\TextColumn::make('amount')
                    ->label('Amount')
                    ->formatStateUsing(fn($state) => number_format($state / 100, 2) . ' BWP')
                    ->numeric()
                    ->sortable(),
                Tables\Columns\TextColumn::make('category')
                    ->badge()
                    ->colors([
                        'primary' => ExpenseCategory::Feed->value,
                        'warning' => ExpenseCategory::Labor->value,
                        'secondary' => ExpenseCategory::Utilities->value,
                        'success' => ExpenseCategory::Equipment->value,
                    ]),
                Tables\Columns\TextColumn::make('allocatable_type')
                    ->label('Allocated To')
                    ->formatStateUsing(fn($state) => $state ? str_replace('Domains\\Broiler\\Models\\', '', $state) : 'General')
                    ->badge()
                    ->color('gray')
                    ->toggleable(),
                Tables\Columns\TextColumn::make('created_at')
                    ->dateTime()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
            ])
            ->filters([
                Tables\Filters\SelectFilter::make('category')
                    ->options(ExpenseCategory::class),
                Tables\Filters\Filter::make('created_at')
                    ->form([
                        Forms\Components\DatePicker::make('created_from'),
                        Forms\Components\DatePicker::make('created_until'),
                    ])
                    ->query(function ($query, array $data): void {
                        $query
                            ->when(
                                $data['created_from'],
                                fn($q) => $q->whereDate('created_at', '>=', $data['created_from']),
                            )
                            ->when(
                                $data['created_until'],
                                fn($q) => $q->whereDate('created_at', '<=', $data['created_until']),
                            );
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
        return [
            //
        ];
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListExpenses::route('/'),
            'create' => Pages\CreateExpense::route('/create'),
            'edit' => Pages\EditExpense::route('/{record}/edit'),
        ];
    }
}
