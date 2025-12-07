<?php

namespace App\Filament\Resources;

use App\Filament\Resources\UserResource\Pages\CreateUser;
use App\Filament\Resources\UserResource\Pages\EditUser;
use App\Filament\Resources\UserResource\Pages\ListUsers;
use BackedEnum;
use Domains\Auth\Models\User;
use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteAction;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Forms\Components\DateTimePicker;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Resources\Resource;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\Filter;
use Filament\Tables\Table;
use UnitEnum;

class UserResource extends Resource
{
    protected static ?string $model = User::class;

    protected static bool $isScopedToTenant = false;

    protected static string|BackedEnum|null $navigationIcon = 'heroicon-o-user';

    protected static ?int $navigationSort = 1;

    protected static string|UnitEnum|null $navigationGroup = 'Administration';

    protected static ?string $recordTitleAttribute = 'name';

    public static function form(Schema $form): Schema
    {
        return $form
            ->schema([
                Section::make('User Account')
                    ->description('Create or edit user account')
                    ->schema([
                        TextInput::make('name')
                            ->required()
                            ->maxLength(255)
                            ->autofocus(),
                        TextInput::make('email')
                            ->email()
                            ->required()
                            ->unique(ignoreRecord: true)
                            ->maxLength(255),
                        TextInput::make('password')
                            ->password()
                            ->revealable()
                            ->required(fn ($record) => $record === null)
                            ->hidden(fn ($record) => $record !== null)
                            ->minLength(8),
                    ])->columns(2),

                Section::make('Team Assignment')
                    ->description('Assign user to teams with specific roles')
                    ->schema([
                        Select::make('current_team_id')
                            ->relationship('currentTeam', 'name')
                            ->helperText('Primary team for this user')
                            ->searchable(),
                    ]),

                Section::make('System Settings')
                    ->schema([
                        DateTimePicker::make('email_verified_at')
                            ->label('Email Verified At')
                            ->helperText('Leave empty if not verified'),
                    ]),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('name')
                    ->searchable()
                    ->sortable(),
                TextColumn::make('email')
                    ->searchable()
                    ->icon('heroicon-m-envelope'),
                TextColumn::make('currentTeam.name')
                    ->label('Current Team')
                    ->searchable()
                    ->sortable(),
                TextColumn::make('email_verified_at')
                    ->label('Email Verified At')
                    ->dateTime()
                    ->sortable(),
                TextColumn::make('created_at')
                    ->dateTime()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
            ])
            ->filters([
                Filter::make('email_verified')
                    ->label('Email Verified')
                    ->toggle()
                    ->query(fn ($query, $value) => $value ? $query->whereNotNull('email_verified_at') : $query->whereNull('email_verified_at')),
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
            'index' => ListUsers::route('/'),
            'create' => CreateUser::route('/create'),
            'edit' => EditUser::route('/{record}/edit'),
        ];
    }
}
