<?php

namespace App\Filament\Admin\Resources;

use App\Filament\Admin\Resources\InstituteResource\Pages;
use App\Filament\Admin\Resources\InstituteResource\RelationManagers;
use App\Models\Institute;
use App\Filament\admin\Resources\UserResource\Pages\ViewUser;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\RelationManagers\RelationGroup;
use Filament\Resources\Resource;
use Filament\Support\Markdown;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\SoftDeletingScope;

class InstituteResource extends Resource
{
    protected static ?string $model = Institute::class;

    protected static ?string $navigationGroup = 'Corporate';

    protected static ?string $navigationIcon = 'heroicon-o-building-library';

    public static function form(Form $form): Form
    {
        return $form
            ->columns(3)
            ->schema([
                Forms\Components\TextInput::make('name')
                    ->helperText('If omitted, the name will be generated from the first user added to the institute.')
                    ->maxLength(255),

                Forms\Components\Select::make('owner_id')
                    ->required()
                    ->label('owner')
                    ->relationship('owner', 'name')
                    ->placeholder('Select a user')
                    ->preload()
                    ->searchable()
                    ->createOptionForm([
                        Forms\Components\TextInput::make('name')
                            ->helperText('If omitted, the name will be generated from the first user added to the institute.')
                            ->maxLength(255),
                        Forms\Components\Select::make('type')
                            ->required()
                            ->options(\App\Enums\InstituteType::class)
                            ->enum(\App\Enums\InstituteType::class)
                            ->native(false),
                    ]),
                Forms\Components\TextInput::make('address')
                    ->required(),
                Forms\Components\TextInput::make('phone')
                    ->required(),
                Forms\Components\TextInput::make('email')
                    ->email()
                    ->required(),
                Forms\Components\Section::make('Administration')
                    ->collapsible()
                    ->collapsed()
                    ->columnSpan(1)
                    ->schema([
                        Forms\Components\TextInput::make('files_url')
                            ->type('url')
                            ->url()
                            ->helperText('URL to shared web folder, such as Dropbox, OneDrive, etc.'),
                        Forms\Components\Toggle::make('can_add_candidates')
                            ->default(true)
                            ->helperText('If enabled, the institute will be able to add candidates to exams.'),
                    ]),
                Forms\Components\Select::make('institute_type_id')
                    ->required()
                    ->label('type')
                    ->relationship('instituteType', 'name')
                    ->native(true),
                Forms\Components\TextInput::make('files_url')
                    ->type('url')
                    ->helperText('The URL to web folder like Dropbox, One, etc.'),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('name')
                    ->searchable()
                    ->sortable()
                    ->placeholder('(unnamed)')
                    ->toggleable(),
                Tables\Columns\TextColumn::make('owner.name')
                    ->url(fn (Institute $institute) => route('filament.admin.resources.users.view', $institute->owner->id))
                    ->placeholder('(no owner)')
                    ->toggleable(isToggledHiddenByDefault: true),
                Tables\Columns\TextColumn::make('address')
                    ->searchable()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
                Tables\Columns\TextColumn::make('email')
                    ->searchable()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
                Tables\Columns\TextColumn::make('phone')
                    ->searchable()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
                Tables\Columns\TextColumn::make('files_url')
                    ->url(fn ($record) => $record->files_url)
                    ->wrap()
                    ->placeholder('(no url)')
                    ->searchable()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
                Tables\Columns\TextColumn::make('instituteType.name')
                    ->badge()
                    ->sortable()
                    ->alignCenter()
                    ->toggleable(isToggledHiddenByDefault: true),
                Tables\Columns\TextColumn::make('created_at')
                    ->dateTime()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
                Tables\Columns\TextColumn::make('updated_at')
                    ->dateTime()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
                Tables\Columns\TextColumn::make('deleted_at')
                    ->dateTime()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
            ])
            ->filters([
                Tables\Filters\TrashedFilter::make()
                    ->native(false),
            ])
            ->actions([
                Tables\Actions\ViewAction::make(),
                Tables\Actions\EditAction::make(),
            ])
            ->bulkActions([
                Tables\Actions\BulkActionGroup::make([
                    Tables\Actions\DeleteBulkAction::make(),
                    Tables\Actions\ForceDeleteBulkAction::make(),
                    Tables\Actions\RestoreBulkAction::make(),
                ]),
            ]);
    }

    public static function getRelations(): array
    {
        return [
            RelationManagers\UsersRelationManager::class,
            RelationManagers\StudentsRelationManager::class,
        ];
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListInstitutes::route('/'),
            'create' => Pages\CreateInstitute::route('/create'),
            'view' => Pages\ViewInstitute::route('/{record}'),
            'edit' => Pages\EditInstitute::route('/{record}/edit'),
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
