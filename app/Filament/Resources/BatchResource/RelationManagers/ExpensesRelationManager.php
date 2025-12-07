<?php

namespace App\Filament\Resources\BatchResource\RelationManagers;

use Filament\Actions\CreateAction;
use Filament\Actions\DeleteAction;
use Filament\Actions\EditAction;
use Filament\Actions\ViewAction;
use Filament\Forms\Components\DatePicker;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Resources\RelationManagers\RelationManager;
use Filament\Schemas\Schema;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Table;
use Illuminate\Support\Facades\Auth;

class ExpensesRelationManager extends RelationManager
{
    protected static string $relationship = 'expenses';

    protected static ?string $title = 'Batch Expenses';

    protected static ?string $recordTitleAttribute = 'description';

    public function table(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('expense_date')
                    ->date()
                    ->sortable(),
                TextColumn::make('category')
                    ->badge()
                    ->sortable(),
                TextColumn::make('description')
                    ->searchable()
                    ->limit(50),
                TextColumn::make('amount_cents')
                    ->money('BWP', divideBy: 100)
                    ->label('Amount')
                    ->sortable(),
                TextColumn::make('supplier.name')
                    ->searchable()
                    ->toggleable(isToggledHiddenByDefault: true),
            ])
            ->defaultSort('expense_date', 'desc')
            ->filters([
                SelectFilter::make('category'),
            ])
            ->recordActions([
                ViewAction::make(),
                EditAction::make(),
                DeleteAction::make(),
            ])
            ->toolbarActions([
                CreateAction::make()
                    ->mutateDataUsing(function (array $data) {
                        $data['team_id'] = Auth::user()->current_team_id;
                        $data['allocatable_type'] = $this->getOwnerRecord()::class;
                        $data['allocatable_id'] = $this->getOwnerRecord()->id;

                        return $data;
                    }),
            ]);
    }

    public function form(Schema $form): Schema
    {
        return $form
            ->schema([
                DatePicker::make('expense_date')
                    ->required()
                    ->default(now())
                    ->native(false),
                Select::make('category')
                    ->required()
                    ->options([
                        'chicks' => 'Chicks',
                        'feed' => 'Feed',
                        'medication' => 'Medication',
                        'labor' => 'Labor',
                        'utilities' => 'Utilities',
                        'equipment' => 'Equipment',
                        'other' => 'Other',
                    ]),
                TextInput::make('description')
                    ->required()
                    ->maxLength(255),
                TextInput::make('amount_cents')
                    ->required()
                    ->numeric()
                    ->prefix('P')
                    ->label('Amount (BWP)')
                    ->helperText('Amount will be stored in cents'),
                Select::make('supplier_id')
                    ->relationship('supplier', 'name')
                    ->searchable()
                    ->preload(),
                Textarea::make('notes')
                    ->maxLength(500)
                    ->columnSpanFull(),
            ])
            ->columns(2);
    }
}
