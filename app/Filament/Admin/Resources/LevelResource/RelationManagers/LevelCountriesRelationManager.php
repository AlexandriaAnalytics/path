<?php

namespace App\Filament\Admin\Resources\LevelResource\RelationManagers;

use Filament\Forms;
use Filament\Forms\Components\Repeater;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Form;
use Filament\Resources\RelationManagers\RelationManager;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Validation\Rules\Unique;

class LevelCountriesRelationManager extends RelationManager
{
    protected static string $relationship = 'levelCountries';

    public function form(Form $form): Form
    {
        return $form
            ->schema([
                Select::make('country_id')
                    ->label('Country')
                    ->relationship('country', 'name')
                    ->required()
                    ->native(false)
                    ->columnSpanFull()
                    ->unique(modifyRuleUsing: function (Unique $rule) {
                        return $rule->where('level_id', $this->getOwnerRecord()->getKey());
                    })
                    ->placeholder('Select a country'),
                TextInput::make('price_discounted')
                    ->required()
                    ->numeric()
                    ->minValue(0)
                    ->default(0),
                TextInput::make('price_right_exam')
                    ->required()
                    ->numeric()
                    ->minValue(0)
                    ->default(0),
                Repeater::make('levelCountryModules')
                    ->label('Modules')
                    ->columnSpanFull()
                    ->columns(2)
                    ->relationship()
                    ->schema([
                        Select::make('module_id')
                            ->label('Module')
                            ->relationship('module', 'name')
                            ->required()
                            ->native(false)
                            ->placeholder('Select a module')
                            ->fixIndistinctState(),
                        TextInput::make('price')
                            ->label('Price')
                            ->required()
                            ->numeric()
                            ->minValue(0)
                            ->default(0),
                    ]),
            ]);
    }

    public function table(Table $table): Table
    {
        return $table
            ->recordTitleAttribute('country.name')
            ->columns([
                Tables\Columns\TextColumn::make('country.name'),
            ])
            ->filters([
                //
            ])
            ->headerActions([
                Tables\Actions\CreateAction::make(),
            ])
            ->actions([
                Tables\Actions\ViewAction::make(),
                Tables\Actions\EditAction::make(),
                Tables\Actions\DeleteAction::make(),
            ])
            ->bulkActions([
                Tables\Actions\BulkActionGroup::make([
                    Tables\Actions\DeleteBulkAction::make(),
                ]),
            ]);
    }
}
