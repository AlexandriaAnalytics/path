<?php

namespace App\Filament\Trainee\Resources;

use App\Filament\Trainee\Resources\FilesResource\Pages;
use App\Filament\Trainee\Resources\FilesResource\RelationManagers;
use App\Models\Files;
use App\Models\Trainee;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\SoftDeletingScope;

class FilesResource extends Resource
{
    protected static ?string $model = Trainee::class;

    protected static ?string $navigationLabel = 'File';

    protected static ?string $modelLabel = 'File';

    protected static ?string $pluralModelLabel = 'Files';

    protected static ?string $navigationIcon = 'heroicon-o-rectangle-stack';

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                //
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                //
            ])
            ->filters([
                //
            ])
            ->actions([
                Tables\Actions\EditAction::make(),
            ])
            ->bulkActions([
                Tables\Actions\BulkActionGroup::make([
                    Tables\Actions\DeleteBulkAction::make(),
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
            'index' => Pages\ListFiles::route('/'),
            'create' => Pages\CreateFiles::route('/create'),
            'edit' => Pages\EditFiles::route('/{record}/edit'),
        ];
    }
}
