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

    protected static ?string $navigationGroup = 'Exam Management';

    protected static ?string $navigationParentItem = 'Exams';

    protected static ?string $navigationIcon = 'heroicon-o-rectangle-stack';

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\Repeater::make('levelCountries')
                    ->relationship()
                    ->schema([
                        Forms\Components\Select::make('country_id')
                            ->relationship('country', 'name')
                            ->disabled(),
                        Forms\Components\TextInput::make('price_discounted')
                            ->label('Completed price')
                            ->numeric()
                            ->prefix(function($record){
                                return $record->country->monetary_prefix;
                            })
                    ])
                    ->addable(false)
                    ->deletable(false)
                    ->grid(2)->columns(2),
                Forms\Components\TextInput::make('name'),
                Forms\Components\MarkdownEditor::make('description')->label('Description')->columns(1),
                Forms\Components\TextInput::make('tier'),
            ])->columns(2);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('name')->sortable(),
                Tables\Columns\TextColumn::make('description'),
                Tables\Columns\TextColumn::make('tier')->sortable(),
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
