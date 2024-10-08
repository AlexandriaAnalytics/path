<?php

namespace App\Filament\Admin\Resources;

use App\Filament\Admin\Resources\UserResource\Pages;
use App\Filament\Admin\Resources\UserResource\RelationManagers;
use App\Filament\Exports\UserExporter;
use App\Models\User;
use Filament\Forms;
use Filament\Forms\Components\Fieldset;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Columns\ColumnGroup;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\SoftDeletingScope;
use Illuminate\Support\Facades\Hash;
use STS\FilamentImpersonate\Tables\Actions\Impersonate;

class UserResource extends Resource
{
    protected static ?string $model = User::class;

    protected static ?string $navigationLabel = 'Users';

    protected static ?string $navigationGroup = 'User management';

    protected static ?string $navigationIcon = 'heroicon-o-user-group';

    protected static ?string $modelLabel = 'user';

    protected static ?string $pluralModelLabel = 'users';

    public static function form(Form $form): Form
    {
        return $form
            ->columns(3)
            ->schema([
                Fieldset::make('User information')
                    ->columnSpan(2)
                    ->schema([
                        Forms\Components\TextInput::make('name')
                            ->required()
                            ->maxLength(255)
                            ->autofocus(),
                        Forms\Components\TextInput::make('email')
                            ->email()
                            ->required()
                            ->maxLength(255),
                        Forms\Components\TextInput::make('password')
                            ->password()
                            ->revealable()
                            ->maxLength(255)
                            ->dehydrateStateUsing(fn (string $state): string => Hash::make($state))
                            ->dehydrated(fn (?string $state): bool => filled($state))
                            ->required(fn (string $operation): bool => $operation === 'create')
                            ->hiddenOn(['view']),
                    ]),
                Fieldset::make('Access control')
                    ->columnSpan(1)
                    ->schema([
                        Forms\Components\Select::make('roles')
                            ->relationship(
                                name: 'roles',
                                titleAttribute: 'name',
                            )
                            ->multiple()
                            ->preload(true),
                    ]),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->query(function () {
                return User::orderByDesc('created_at');
            })
            ->columns([
                ColumnGroup::make('User information', [
                    Tables\Columns\TextColumn::make('name')
                        ->sortable()
                        ->searchable()
                        ->toggleable(isToggledHiddenByDefault: false),
                    Tables\Columns\TextColumn::make('email')
                        ->sortable()
                        ->searchable()
                        ->toggleable(isToggledHiddenByDefault: false),
                ]),
                ColumnGroup::make('Institutions', [
                    Tables\Columns\TextColumn::make('institutes_count')
                        ->label('Member or centre count')
                        ->counts('institutes')
                        ->alignEnd()
                        ->toggleable(isToggledHiddenByDefault: false),
                ]),
                Tables\Columns\TextColumn::make('created_at')->label('Created on')
                    ->dateTime()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: false),
                Tables\Columns\TextColumn::make('updated_at')->label('Updated on')
                    ->dateTime()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: false),
                Tables\Columns\TextColumn::make('deleted_at')->label('Deleted on')
                    ->dateTime()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: false),
            ])
            ->filters([
                Tables\Filters\TrashedFilter::make(),
            ])
            ->actions([
                Tables\Actions\ViewAction::make(),
                Tables\Actions\EditAction::make(),
                /*  Tables\Actions\Action::make('impersonate')
                    ->label('Impersonate')
                    ->icon('heroicon-o-finger-print')
                    ->requiresConfirmation()
                    ->modalHeading('Log in as this user?')
                    ->modalDescription('Are you sure you want to log in as this user? You will be logged out of your current session.')
                    ->modalSubmitActionLabel('Yes, I am sure')
                    ->action(function (User $user) {
                        // Save the current user's ID to the session so we can log them back in later.
                        session()->put('impersonator_id', auth()->id());

                        auth()->login($user);

                        return redirect()
                            ->to(route('filament.management.auth.login'));
                    }), */
                Impersonate::make()
                    ->label('Impersonate')
                    ->redirectTo(route('filament.management.auth.login')),
            ])
            ->bulkActions([
                Tables\Actions\BulkActionGroup::make([
                    Tables\Actions\DeleteBulkAction::make()->deselectRecordsAfterCompletion(),
                    Tables\Actions\ForceDeleteBulkAction::make()->deselectRecordsAfterCompletion(),
                    Tables\Actions\RestoreBulkAction::make()->deselectRecordsAfterCompletion(),
                    Tables\Actions\ExportBulkAction::make()
                        ->label('Export users')
                        ->exporter(UserExporter::class)->deselectRecordsAfterCompletion(),
                ]),
            ]);
    }

    public static function getRelations(): array
    {
        return [
            RelationManagers\InstitutesRelationManager::class,
        ];
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListUsers::route('/'),
            'create' => Pages\CreateUser::route('/create'),
            'view' => Pages\ViewUser::route('/{record}'),
            'edit' => Pages\EditUser::route('/{record}/edit'),
        ];
    }

    public static function getEloquentQuery(): Builder
    {
        return parent::getEloquentQuery()
            ->withoutGlobalScopes([
                SoftDeletingScope::class,
            ]);
    }
}
