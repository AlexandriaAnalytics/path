<?php

namespace App\Filament\Admin\Resources;

use App\Filament\Admin\Resources\LogisticResource\Pages;
use App\Filament\Admin\Resources\LogisticResource\RelationManagers;
use App\Models\Logistic;
use Filament\Forms;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\SoftDeletingScope;

class LogisticResource extends Resource
{
    protected static ?string $model = Logistic::class;

    protected static ?string $navigationGroup = 'Exam sessions settings';

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                TextInput::make('value')
                    ->required()
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('value')
                    ->sortable()
                    ->searchable()
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
            'index' => Pages\ListLogistics::route('/'),
            'create' => Pages\CreateLogistic::route('/create'),
            'edit' => Pages\EditLogistic::route('/{record}/edit'),
        ];
    }
}
