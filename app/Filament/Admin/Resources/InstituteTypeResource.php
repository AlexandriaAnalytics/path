<?php

namespace App\Filament\Admin\Resources;

use App\Filament\Admin\Resources\InstituteTypeResource\Pages;
use App\Filament\Admin\Resources\InstituteTypeResource\RelationManagers;
use App\Models\InstituteType;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\SoftDeletingScope;

class InstituteTypeResource extends Resource
{
    protected static ?string $model = InstituteType::class;

    protected static ?string $navigationGroup = 'Settings';

    protected static ?string $modelLabel = 'Membership';

    protected static bool $hasTitleCaseModelLabel = false;

    protected static ?int $navigationSort = 1;

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\TextInput::make('name')
                    ->required()
                    ->maxLength(255),
                Forms\Components\TextInput::make('files_url')
                    ->maxLength(255)
                    ->url()
                    ->label('Files URL'),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('name')
                    ->searchable()
                    ->sortable(),
                Tables\Columns\TextColumn::make('files_url')
                    ->label('Files URL')
                    ->wrap()
                    ->placeholder('(no url)')
                    ->url(fn ($record) => $record->files_url, shouldOpenInNewTab: true)
                    ->toggleable(),
            ])
            ->filters([
                //
            ])
            ->actions([
                Tables\Actions\EditAction::make(),
            ])
            ->bulkActions([
                Tables\Actions\BulkActionGroup::make([
                    Tables\Actions\DeleteBulkAction::make()->deselectRecordsAfterCompletion(),
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
            'index' => Pages\ListInstituteTypes::route('/'),
            'create' => Pages\CreateInstituteType::route('/create'),
            'edit' => Pages\EditInstituteType::route('/{record}/edit'),
        ];
    }
}
