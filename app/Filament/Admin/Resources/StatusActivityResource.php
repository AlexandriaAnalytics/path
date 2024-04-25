<?php

namespace App\Filament\Admin\Resources;

use App\Filament\Admin\Resources\StatusActivityResource\Pages;
use App\Filament\Admin\Resources\StatusActivityResource\RelationManagers;
use App\Models\StatusActivity;
use Filament\Forms;
use Filament\Forms\Components\ColorPicker;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\SoftDeletingScope;

class StatusActivityResource extends Resource
{
    protected static ?string $model = StatusActivity::class;

    //protected static ?string $navigationIcon = 'heroicon-o-rectangle-stack';

    protected static ?string $navigationGroup = 'Settings';

    protected static ?string $navigationLabel = 'Status';

    protected static ?string $pluralModelLabel = 'Status';

    protected static bool $hasTitleCaseModelLabel = false;

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                TextInput::make('name')
                    ->required(),
                ColorPicker::make('color')
                    ->required(),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                //
            ])
            ->filters([
                Tables\Filters\TrashedFilter::make(),
            ])
            ->actions([
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
            //
        ];
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListStatusActivities::route('/'),
            'create' => Pages\CreateStatusActivity::route('/create'),
            'edit' => Pages\EditStatusActivity::route('/{record}/edit'),
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
