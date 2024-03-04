<?php

namespace App\Filament\Admin\Resources;

use App\Filament\Admin\Resources\CustomLevelPriceResource\Pages;
use App\Filament\Admin\Resources\CustomLevelPriceResource\RelationManagers;
use App\Models\Country;
use App\Models\CustomLevelPrice;
use App\Models\Institute;
use App\Models\Level;
use App\Models\LevelCountry;
use Closure;
use Filament\Facades\Filament;
use Filament\Forms;
use Filament\Forms\Components\Fieldset;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Form;
use Filament\Forms\Get;
use Filament\Forms\Set;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\SoftDeletingScope;

class CustomLevelPriceResource extends Resource
{
    protected static ?string $model = CustomLevelPrice::class;

    protected static ?string $navigationIcon = 'heroicon-o-rectangle-stack';

    protected static ?string $navigationLabel = 'Custom exam fees';

    protected static ?string $modelLabel = 'custom exam fees';

    protected static ?string $pluralModelLabel = 'Custom exam fees';

    protected static bool $hasTitleCaseModelLabel = false;

    public static function form(Form $form): Form
    {
        return $form
            ->columns(3)
            ->schema([
                Forms\Components\Select::make('institute')
                    ->label('Member or centre')
                    ->helperText('The member or centre that this custom exam price is for')
                    ->relationship('institute', 'name')
                    ->required()
                    ->preload()
                    ->native(false)
                    ->searchable()
                    ->hiddenOn('edit'),
                Forms\Components\Select::make('level_id')
                    ->label('Exam')
                    ->options(Level::all()->pluck('name', 'id'))
                    ->required()
                    ->native(false)
                    ->afterStateUpdated(fn (Set $set) => $set('country_id', null))
                    ->hiddenOn('edit'),
                Forms\Components\Select::make('country_id')
                    ->label('Country')
                    ->options(fn (Get $get) => Level::find($get('level_id'))?->countries->pluck('name', 'id'))
                    ->required()
                    ->native(false)
                    ->live()
                    ->reactive()
                    ->rules([
                        fn (Get $get): Closure => function (string $attribute, $value, Closure $fail) use ($get) {
                            if (CustomLevelPrice::query()
                                ->where('institute_id', $get('institute'))
                                ->whereHas('levelCountry', fn (Builder $query) => $query
                                    ->where('level_id', $get('level_id'))
                                    ->where('country_id', $value))
                                ->exists()
                            ) {
                                $fail('The selected institute, level and country combination already exists.');
                            }
                        },
                    ])
                    ->afterStateUpdated(fn (Get $get, Set $set) => $set('level_country_id', LevelCountry::query()
                        ->where('level_id', $get('level_id'))
                        ->where('country_id', $get('country_id'))
                        ->first()?->id))
                    ->hiddenOn('edit'),
                Forms\Components\Hidden::make('level_country_id'),
                Fieldset::make('Exam Right')
                    ->visible(fn (Get $get) => LevelCountry::find($get('level_country_id'))?->country->monetary_unit == 'ARS')
                    ->columns(3)
                    ->schema([
                        TextInput::make('extra_price_all_modules')
                            ->label('Extra Complete Price')
                            ->suffix('ARS')
                            ->helperText('Extra price for exam with all modules')
                            ->required()
                            ->numeric()
                            ->minValue(0),
                        TextInput::make('extra_price_exam_right')
                            ->label('Extra Incomplete Exam Right')
                            ->suffix('ARS')
                            ->helperText('Extra price for exam without some modules')
                            ->numeric()
                            ->minValue(0),
                        TextInput::make('extra_price_exam_right_all_modules')
                            ->label('Extra Complete Exam Right')
                            ->suffix('ARS')
                            ->helperText('Extra price for exam with all modules')
                            ->numeric()
                            ->minValue(0),
                    ]),
                Fieldset::make('Exam Right')
                    ->visible(fn (Get $get) => LevelCountry::find($get('level_country_id'))?->country->monetary_unit != 'ARS')
                    ->columns(3)
                    ->schema([
                        TextInput::make('percentage_extra_price_all_modules')
                            ->label('Extra Complete Price')
                            ->helperText('Extra price for exam with all modules')
                            ->suffix('%')
                            ->required()
                            ->numeric()
                            ->minValue(0),
                        TextInput::make('percentage_extra_price_exam_right')
                            ->label('Extra Incomplete Exam Right')
                            ->suffix('%')
                            ->helperText('Extra price for exam without some modules')
                            ->numeric()
                            ->minValue(0),
                        TextInput::make('percentage_extra_price_exam_right_all_modules')
                            ->label('Extra Complete Exam Right')
                            ->suffix('%')
                            ->helperText('Extra price for exam with all modules')
                            ->numeric()
                            ->minValue(0),
                    ]),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('institute.name')
                    ->label('Member or centre')
                    ->sortable()
                    ->searchable(),
                Tables\Columns\TextColumn::make('levelCountry.level.name')
                    ->sortable(),
                Tables\Columns\TextColumn::make('levelCountry.country.name')
                    ->sortable(),
                Tables\Columns\TextColumn::make('created_at')
                    ->dateTime()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
                Tables\Columns\TextColumn::make('updated_at')
                    ->dateTime()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
                Tables\Columns\TextColumn::make('deleted_at')
                    ->dateTime()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
            ])
            ->filters([
                Tables\Filters\SelectFilter::make('institute_id')
                    ->label('Member or centre')
                    ->relationship('institute', 'name')
                    ->options(Institute::all()->pluck('name', 'id'))
                    ->preload()
                    ->searchable(),
                Tables\Filters\TrashedFilter::make(),
            ])
            ->actions([
                Tables\Actions\ViewAction::make(),
                // Tables\Actions\EditAction::make(),
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
            'index' => Pages\ListCustomLevelPrices::route('/'),
            'create' => Pages\CreateCustomLevelPrice::route('/create'),
            'view' => Pages\ViewCustomLevelPrice::route('/{record}'),
            // 'edit' => Pages\EditCustomLevelPrice::route('/{record}/edit'),
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
