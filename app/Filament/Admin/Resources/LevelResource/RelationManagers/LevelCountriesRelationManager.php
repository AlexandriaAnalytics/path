<?php

namespace App\Filament\Admin\Resources\LevelResource\RelationManagers;

use App\Enums\ModuleType;
use App\Models\Module;
use Filament\Forms;
use Filament\Forms\Components\Fieldset;
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

    protected static ?string $title = 'Exam fees per country';

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
                    ->unique(
                        modifyRuleUsing: function (Unique $rule) {
                            return $rule->where('level_id', $this->getOwnerRecord()->getKey());
                        },
                        ignoreRecord: true,
                    )
                    ->placeholder('Select a country'),
                Fieldset::make('Pricing')
                    ->columns(3)
                    ->schema([
                        TextInput::make('price_all_modules')
                            ->label('Full exam fee')
                            ->required()
                            ->numeric()
                            ->minValue(0)
                            ->default(0),
                        TextInput::make('price_exam_right_all_modules')
                            ->label('Full exam registration fee')
                            ->helperText('When all modules are taken')
                            ->required()
                            ->numeric()
                            ->minValue(0)
                            ->default(0),
                        TextInput::make('price_exam_right')
                            ->label('Module registration fee')
                            ->helperText('When not all modules are taken')
                            ->required()
                            ->numeric()
                            ->minValue(0)
                            ->default(0),
                    ]),
                Repeater::make('levelCountryModules')
                    ->label('Modules')
                    ->columnSpanFull()
                    ->columns(3)
                    ->relationship()
                    ->schema([
                        Select::make('module_id')
                            ->label('Module')
                            ->relationship('module', 'name')
                            ->required()
                            ->native(false)
                            ->placeholder('Select a module')
                            ->fixIndistinctState(),
                        Select::make('module_type')
                            ->label('Type')
                            ->options(ModuleType::class)
                            ->enum(ModuleType::class)
                            ->required()
                            ->native(false)
                            ->placeholder('Select a type'),
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
                Tables\Actions\CreateAction::make()
                    ->label('New exam fees'),
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
