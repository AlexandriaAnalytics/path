<?php

namespace App\Filament\Admin\Resources;

use App\Filament\Admin\Resources\InstituteResource\Pages;
use App\Filament\Admin\Resources\InstituteResource\RelationManagers;
use App\Models\Institute;
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

    protected static ?string $modelLabel = 'Members and Centers';

    protected static bool $hasTitleCaseModelLabel = false;

    protected static ?string $navigationGroup = 'Corporate';

    protected static ?string $navigationIcon = 'heroicon-o-building-library';

    protected static ?int $navigationSort = 1;

    public static function form(Form $form): Form
    {
        return $form
            ->columns(12)
            ->schema([
                Forms\Components\TextInput::make('name')
                    ->helperText('If omitted, the name will be generated from the first user added to the institute.')
                    ->label('Name of institution')
                    ->columnSpan(6)
                    ->maxLength(255),

                Forms\Components\Select::make('owner_id')
                    ->required()
                    ->label('Head')
                    ->columnSpan(6)
                    ->relationship('owner', 'name')
                    ->placeholder('Select a user')
                    ->preload()
                    ->searchable()
                    ->createOptionForm([
                        Forms\Components\TextInput::make('full name')
                            ->helperText('If omitted, the name will be generated from the first user added to the institute.')
                            ->maxLength(255),
                    ]),
                Forms\Components\TextInput::make('street_name')
                    ->columnSpan(4)
                    ->required(),
                Forms\Components\TextInput::make('number')
                    ->columnSpan(1)
                    ->required(),
                Forms\Components\TextInput::make('city')
                    ->columnSpan(2)
                    ->required(),
                Forms\Components\TextInput::make('province')
                    ->columnSpan(2)
                    ->required(),
                Forms\Components\TextInput::make('country')
                    ->columnSpan(2)
                    ->required(),
                Forms\Components\TextInput::make('post_code')
                    ->columnSpan(1)
                    ->required(),
                Forms\Components\TextInput::make('email')
                    ->email()
                    ->columnSpan(3)
                    ->required(),
                Forms\Components\TextInput::make('password')
                    ->password()
                    ->revealable()
                    ->columnSpan(3)
                    ->required(),
                Forms\Components\TextInput::make('country_code')
                    ->columnSpan(2)
                    ->required(),
                Forms\Components\TextInput::make('phone')
                    ->tel()
                    ->columnSpan(4)
                    ->required(),
                Forms\Components\Select::make('institute_type_id')
                    ->columnSpan(6)
                    ->required()
                    ->label('Type')
                    ->relationship('instituteType', 'name')
                    ->native(true),
                Forms\Components\TextInput::make('specific_files_url')
                    ->columnSpan(6)
                    ->type('url')
                    ->helperText('The URL to web folder like Dropbox, One, etc.'),
                Forms\Components\Section::make('Administration')
                    ->collapsible()
                    ->collapsed()
                    ->columnSpanFull()
                    ->schema([
                        Forms\Components\TextInput::make('general_files_url')
                            ->type('url')
                            ->url()
                            ->helperText('URL to shared web folder, such as Dropbox, OneDrive, etc.'),
                        Forms\Components\Toggle::make('can_add_candidates')
                            ->default(true)
                            ->helperText('If enabled, the institute will be able to add candidates to exams.'),
                    ]),
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
                    ->url(fn ($record) => 'mailto:' . $record->email, shouldOpenInNewTab: true)
                    ->searchable()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
                Tables\Columns\TextColumn::make('phone')
                    ->url(fn ($record) => 'https://api.whatsapp.com/send?phone=' . $record->phone, shouldOpenInNewTab: true)
                    ->searchable()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
                Tables\Columns\TextColumn::make('files_url')
                    ->url(fn ($record) => $record->files_url, shouldOpenInNewTab: true)
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
                Tables\Columns\TextColumn::make('created_at')->label('Created on')
                    ->dateTime()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
                Tables\Columns\TextColumn::make('updated_at')->label('Updated on')
                    ->dateTime()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
                Tables\Columns\TextColumn::make('deleted_at')->label('Deleted on')
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
