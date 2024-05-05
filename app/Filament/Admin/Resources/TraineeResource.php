<?php

namespace App\Filament\Admin\Resources;

use App\Filament\Admin\Resources\TraineeResource\Pages;
use App\Filament\Admin\Resources\TraineeResource\RelationManagers;
use App\Models\Country;
use App\Models\Level;
use App\Models\Trainee;
use App\Models\TypeOfTraining;
use Filament\Forms;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Wizard;
use Filament\Forms\Components\Wizard\Step;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\SoftDeletingScope;
use Ysfkaya\FilamentPhoneInput\Forms\PhoneInput;

class TraineeResource extends Resource
{
    protected static ?string $model = Trainee::class;

    protected static ?string $navigationIcon = 'heroicon-o-rectangle-stack';

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Wizard::make([
                    Step::make('Personal information')
                        ->schema([
                            TextInput::make('full_name')
                                ->required(),
                            PhoneInput::make('phone')
                                ->required(),
                            TextInput::make('email')
                                ->email()
                                ->required(),
                            Select::make('type_of_training_id')
                                ->label('Type of training')
                                ->options(TypeOfTraining::all()->pluck('name', 'id'))
                                ->required(),
                            Select::make('country_id')
                                ->label('Country')
                                ->options(Country::all()->pluck('name', 'id'))
                                ->required(),
                            TextInput::make('province_or_state')
                                ->required(),
                            TextInput::make('city')
                                ->required(),
                            TextInput::make('postcode')
                                ->required(),
                            TextInput::make('street_name')
                                ->required(),
                            TextInput::make('street_number')
                                ->numeric()
                                ->required(),
                            TextInput::make('files')
                                ->required()
                        ]),
                    Step::make('Sections')
                        ->schema([
                            Select::make('sections')
                                ->options(Level::all()->pluck('name', 'id'))
                                ->multiple()
                        ])
                ])->columnSpanFull()
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('full_name')
                    ->sortable()
                    ->searchable()
                    ->toggleable(isToggledHiddenByDefault: false),
                TextColumn::make('phone')
                    ->sortable()
                    ->searchable()
                    ->toggleable(isToggledHiddenByDefault: false),
                TextColumn::make('email')
                    ->sortable()
                    ->searchable()
                    ->toggleable(isToggledHiddenByDefault: false),
                TextColumn::make('country.name')
                    ->sortable()
                    ->searchable()
                    ->toggleable(isToggledHiddenByDefault: false),
                TextColumn::make('province_or_state')
                    ->sortable()
                    ->searchable()
                    ->toggleable(isToggledHiddenByDefault: false),
                TextColumn::make('city')
                    ->sortable()
                    ->searchable()
                    ->toggleable(isToggledHiddenByDefault: false),
                TextColumn::make('postcode')
                    ->sortable()
                    ->searchable()
                    ->toggleable(isToggledHiddenByDefault: false),
                TextColumn::make('street_name')
                    ->sortable()
                    ->searchable()
                    ->toggleable(isToggledHiddenByDefault: false),
                TextColumn::make('street_number')
                    ->sortable()
                    ->searchable()
                    ->toggleable(isToggledHiddenByDefault: false),
                TextColumn::make('sections')
                    ->sortable()
                    ->searchable()
                    ->formatStateUsing(function ($state) {
                        $sections = [];
                        foreach (explode(", ", $state) as $section) {
                            array_push($sections, Level::whereId($section)->pluck('name')->first());
                        }
                        return implode(", ", $sections);
                    })
                    ->badge()
                    ->toggleable(isToggledHiddenByDefault: false),
                TextColumn::make('files')
                    ->sortable()
                    ->searchable()
                    ->url(fn (Trainee $record) => $record->files, shouldOpenInNewTab: true)
                    ->toggleable(isToggledHiddenByDefault: false),
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
            'index' => Pages\ListTrainees::route('/'),
            'create' => Pages\CreateTrainee::route('/create'),
            'edit' => Pages\EditTrainee::route('/{record}/edit'),
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
