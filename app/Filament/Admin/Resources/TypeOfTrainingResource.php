<?php

namespace App\Filament\Admin\Resources;

use App\Filament\Admin\Resources\TypeOfTrainingResource\Pages;
use App\Filament\Admin\Resources\TypeOfTrainingResource\RelationManagers;
use App\Models\TypeOfTraining;
use Filament\Forms;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Columns\TextInputColumn;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\SoftDeletingScope;

class TypeOfTrainingResource extends Resource
{
    protected static ?string $model = TypeOfTraining::class;

    //protected static ?string $navigationIcon = 'heroicon-o-rectangle-stack';

    protected static ?string $navigationGroup = 'Training settings';
    protected static ?int $navigationSort = 1;

    protected static ?string $navigationLabel = 'Type of training';

    protected static ?string $pluralModelLabel = 'Types of training';

    protected static bool $hasTitleCaseModelLabel = false;

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                TextInput::make('name')
                    ->required()
                    ->unique(ignoreRecord: true),
                TextInput::make('files')
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('name')
                    ->searchable()
                    ->sortable(),
                TextColumn::make('files')
                    ->label('Files URL')
                    ->url(fn (TypeOfTraining $record) => $record->files, shouldOpenInNewTab: true)
                    ->wrap()
                    ->placeholder('(no url)')
                    ->searchable()
                    ->sortable(),
                TextColumn::make('created_at')
                    ->label('Created on')
                    ->sortable(),
            ])
            ->filters([
                //
            ])
            ->actions([
                Tables\Actions\EditAction::make(),
                Tables\Actions\DeleteAction::make(),
            ])
            ->bulkActions([
                Tables\Actions\BulkActionGroup::make([
                    Tables\Actions\DeleteBulkAction::make()->deselectRecordsAfterCompletion(),
                ])
            ])
            ->defaultSort('created_at', 'desc');
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
            'index' => Pages\ListTypeOfTrainings::route('/'),
            'create' => Pages\CreateTypeOfTraining::route('/create'),
            'edit' => Pages\EditTypeOfTraining::route('/{record}/edit'),
        ];
    }
}
