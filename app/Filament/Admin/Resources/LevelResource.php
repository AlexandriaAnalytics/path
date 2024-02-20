<?php

namespace App\Filament\Admin\Resources;

use App\Filament\Admin\Resources\LevelResource\Pages;
use App\Filament\Admin\Resources\LevelResource\RelationManagers;
use App\Models\Level;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\SoftDeletingScope;

class LevelResource extends Resource
{
    protected static ?string $model = Level::class;

    protected static ?string $navigationGroup = 'Settings';

    protected static ?string $modelLabel = 'Level setting';

    //protected static ?string $navigationParentItem = 'Exams';

    //protected static ?string $navigationIcon = 'heroicon-o-rectangle-stack';

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\TextInput::make('name')
                    ->required(),
                Forms\Components\TextInput::make('description')->label('Description'),
                Forms\Components\Repeater::make('levelCountries')
                    ->relationship()
                    ->columns(3)
                    ->columnSpanFull()
                    ->schema([
                        Forms\Components\Select::make('country_id')
                            ->relationship('country', 'name')
                            ->native(false),
                        Forms\Components\TextInput::make('price_discounted')
                            ->label('Completed price')
                            ->numeric()
                            ->minValue(0),
                        Forms\Components\TextInput::make('price_right_exam')
                            ->label('Price right exam')
                            ->numeric()
                            ->minValue(0),
                    ])
                    ->minItems(1),
            ])->columns(2);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('name')->sortable(),
                Tables\Columns\TextColumn::make('description'),
                Tables\Columns\TextColumn::make('levelCountries')
                    ->badge()
                    ->formatStateUsing(function ($state) {
                        return $state->country->monetary_prefix . ' ' . $state->price_discounted;
                    })
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
            'index' => Pages\ListLevels::route('/'),
            'create' => Pages\CreateLevel::route('/create'),
            'edit' => Pages\EditLevel::route('/{record}/edit'),
        ];
    }
}
