<?php

namespace App\Filament\Resources;

use App\Filament\Resources\TeamResource\Pages;
use BackedEnum;
use Domains\Auth\Models\Team;
use Filament\Actions;
use Filament\Forms;
use Filament\Resources\Resource;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;
use Filament\Tables;
use Filament\Tables\Table;
use UnitEnum;

class TeamResource extends Resource
{
    protected static ?string $model = Team::class;

    protected static string|BackedEnum|null $navigationIcon = 'heroicon-o-building-office';

    protected static ?int $navigationSort = 2;

    protected static string|UnitEnum|null $navigationGroup = 'Administration';

    protected static ?string $recordTitleAttribute = 'name';

    public static function form(Schema $form): Schema
    {
        return $form
            ->schema([
                Section::make('Team Information')
                    ->description('Create or edit team (farm)')
                    ->schema([
                        Forms\Components\TextInput::make('name')
                            ->required()
                            ->maxLength(255)
                            ->autofocus()
                            ->placeholder('e.g., Kenna\'s Farm'),
                        Forms\Components\Select::make('owner_id')
                            ->relationship('owner', 'name')
                            ->required()
                            ->searchable()
                            ->helperText('User who owns/manages this team'),
                        Forms\Components\Select::make('subscription_plan')
                            ->options([
                                'Basic' => 'Basic',
                                'Pro' => 'Pro',
                                'Enterprise' => 'Enterprise',
                            ])
                            ->required()
                            ->default('Basic')
                            ->helperText('Plan tier determines feature access (Phase 2+)'),
                    ])->columns(1),

                Section::make('Team Members')
                    ->description('Manage users in this team and their roles')
                    ->schema([
                        Forms\Components\Placeholder::make('members_info')
                            ->content('Use the edit page to manage team members and assign roles. Phase 2: Build dedicated UI for role management.')
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
                Tables\Columns\TextColumn::make('owner.name')
                    ->label('Owner')
                    ->searchable()
                    ->sortable(),
                Tables\Columns\TextColumn::make('subscription_plan')
                    ->badge()
                    ->colors([
                        'gray' => 'Basic',
                        'primary' => 'Pro',
                        'success' => 'Enterprise',
                    ]),
                Tables\Columns\TextColumn::make('created_at')
                    ->dateTime()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
            ])
            ->filters([
                Tables\Filters\SelectFilter::make('subscription_plan')
                    ->options([
                        'Basic' => 'Basic',
                        'Pro' => 'Pro',
                        'Enterprise' => 'Enterprise',
                    ]),
            ])
            ->recordActions([
                Actions\EditAction::make(),
                Actions\DeleteAction::make()
                    ->requiresConfirmation()
                    ->modalHeading('Delete Team')
                    ->modalDescription('Are you sure you want to delete this team? All associated data will be lost.'),
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
            'index' => Pages\ListTeams::route('/'),
            'create' => Pages\CreateTeam::route('/create'),
            'edit' => Pages\EditTeam::route('/{record}/edit'),
        ];
    }
}
