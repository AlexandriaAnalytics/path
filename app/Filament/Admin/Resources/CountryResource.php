<?php

namespace App\Filament\Admin\Resources;

use App\Filament\Admin\Resources\CountryResource\Pages;
use App\Filament\Admin\Resources\CountryResource\RelationManagers;
use App\Models\Country;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\SoftDeletingScope;

class CountryResource extends Resource
{
    protected static ?string $model = Country::class;
    protected static ?string $navigationGroup = 'Settings';
    //protected static ?string $navigationIcon = 'heroicon-o-rectangle-stack';
    protected static ?int $navigationSort = 5;

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\Select::make('name')
                    ->options(array_combine(\App\Enums\Country::values(), \App\Enums\Country::values()))
                    ->autofocus()
                    ->required()
                    ->searchable()
                    ->placeholder('Name')
                    ->enum(\App\Enums\Country::class),
                Forms\Components\Select::make('paymentMethods')
                    ->relationship('paymentMethods', 'name')
                    ->multiple()
                    ->preload()
                    ->searchable()
                    ->required()
                    ->placeholder('Payment Methods'),

                Forms\Components\Select::make('monetary_unit')
                    ->placeholder('Monetary Unit')
                    ->required()
                    ->searchable()
                    ->options([
                        'USD' => 'USD',
                        'ARS' => 'ARS',
                        'UYU' => 'UYU',
                        'EUR' => 'EUR',
                    ]),

                Forms\Components\Select::make('monetary_unit_symbol')
                    ->placeholder('Monetary unit symbol')
                    ->required()
                    ->options([
                        '$' => '$',
                        '€' => '€',
                        '₱' => '₱',
                        '£' => '£',
                    ]),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('name')
                    ->searchable()
                    ->sortable(),

                // show string from the model string method (monetaryString)
                TextColumn::make('monetary_unit')
                    //->money('EUR')    
                    ->searchable()
                    ->sortable(),

                TextColumn::make('paymentMethods.name')
                    ->searchable()
                    ->sortable()
                    ->badge()
                    ->label('Payment methods'),
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
            'index' => Pages\ListCountries::route('/'),
            'create' => Pages\CreateCountry::route('/create'),
            'edit' => Pages\EditCountry::route('/{record}/edit'),
        ];
    }
}
