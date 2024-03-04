<?php

namespace App\Filament\Admin\Resources;

use App\Filament\Admin\Resources\LevelResource\Pages;
use App\Models\CertificateType;
use App\Models\Level;
use App\Models\Modality;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use NunoMaduro\PhpInsights\Application\Console\Formatters\Multiple;

use function Laravel\Prompts\multiselect;

class LevelResource extends Resource
{
    protected static ?string $model = Level::class;

    protected static ?string $navigationGroup = 'Settings';

    protected static ?string $navigationLabel = 'Exams';

    protected static ?string $modelLabel = 'Exam';

    protected static ?string $pluralModelLabel = 'Exams';

    protected static ?int $navigationSort = 2;

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\TextInput::make('name')
                    ->label('Name')
                    ->required()
                    ->unique(Level::class, 'name', ignoreRecord: true)
                    ->maxLength(255),
                Forms\Components\TextInput::make('description')
                    ->label('Description')
                    ->maxLength(255),
                Forms\Components\TextInput::make('minimum_age')
                    ->label('Minimum age')
                    ->numeric()
                    ->minValue(0)
                    ->maxValue(100),
                Forms\Components\TextInput::make('maximum_age')
                    ->label('Maximum age')
                    ->numeric()
                    ->minValue(0)
                    ->maxValue(100)
                    ->gte('minimum_age'),
                Forms\Components\Select::make('modules')
                    ->relationship(titleAttribute: 'name')
                    ->multiple()
                    ->preload(),
                Forms\Components\Select::make('modalities')
                    ->relationship(titleAttribute: 'name')
                    ->preload()
                    ->required()
                    ->multiple(),


                Forms\Components\Select::make('certificateTypes')
                    ->relationship(titleAttribute: 'name')
                    ->preload()
                    ->multiple()
                    ->required(),


            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('name')->sortable(),
                Tables\Columns\TextColumn::make('modules.name')->badge(),
                Tables\Columns\TextColumn::make('modalities.name')->badge(),
                Tables\Columns\TextColumn::make('certificateTypes.name')->badge(),
                Tables\Columns\TextColumn::make('description'),
            ])
            ->filters([
                //
            ])
            ->actions([
                Tables\Actions\ViewAction::make(),
            ])
            ->bulkActions([
                //
            ]);
    }

    public static function getRelations(): array
    {
        return [
            LevelResource\RelationManagers\LevelCountriesRelationManager::class,
        ];
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListLevels::route('/'),
            'create' => Pages\CreateLevel::route('/create'),
            'view' => Pages\ViewLevel::route('/{record}'),
            'edit' => Pages\EditLevel::route('/{record}/edit'),
        ];
    }
}
