<?php

namespace App\Filament\Resources\BatchResource\RelationManagers;

use Filament\Actions;
use Filament\Forms;
use Filament\Resources\RelationManagers\RelationManager;
use Filament\Schemas\Schema;
use Filament\Tables;
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
                Tables\Columns\TextColumn::make('expense_date')
                    ->date()
                    ->sortable(),
                Tables\Columns\TextColumn::make('category')
                    ->badge()
                    ->sortable(),
                Tables\Columns\TextColumn::make('description')
                    ->searchable()
                    ->limit(50),
                Tables\Columns\TextColumn::make('amount_cents')
                    ->money('BWP', divideBy: 100)
                    ->label('Amount')
                    ->sortable(),
                Tables\Columns\TextColumn::make('supplier.name')
                    ->searchable()
                    ->toggleable(isToggledHiddenByDefault: true),
            ])
            ->defaultSort('expense_date', 'desc')
            ->filters([
                Tables\Filters\SelectFilter::make('category'),
            ])
            ->recordActions([
                Actions\ViewAction::make(),
                Actions\EditAction::make(),
                Actions\DeleteAction::make(),
            ])
            ->toolbarActions([
                Actions\CreateAction::make()
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
                Forms\Components\DatePicker::make('expense_date')
                    ->required()
                    ->default(now())
                    ->native(false),
                Forms\Components\Select::make('category')
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
                Forms\Components\TextInput::make('description')
                    ->required()
                    ->maxLength(255),
                Forms\Components\TextInput::make('amount_cents')
                    ->required()
                    ->numeric()
                    ->prefix('P')
                    ->label('Amount (BWP)')
                    ->helperText('Amount will be stored in cents'),
                Forms\Components\Select::make('supplier_id')
                    ->relationship('supplier', 'name')
                    ->searchable()
                    ->preload(),
                Forms\Components\Textarea::make('notes')
                    ->maxLength(500)
                    ->columnSpanFull(),
            ])
            ->columns(2);
    }
}
