<?php

namespace App\Filament\Admin\Resources;

use App\Filament\Admin\Resources\ScheduleSettingsResource\Pages;
use App\Filament\Admin\Resources\ScheduleSettingsResource\RelationManagers;
use App\Models\Exam;
use App\Models\Level;
use App\Models\Module;
use App\Models\ScheduleSetting;
use Filament\Forms;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Form;
use Filament\Forms\Get;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\SoftDeletingScope;

class ScheduleSettingsResource extends Resource
{
    protected static ?string $model = ScheduleSetting::class;

    protected static ?string $modelLabel = 'Schedule settings';

    protected static bool $hasTitleCaseModelLabel = false;

    protected static ?string $navigationIcon = 'heroicon-o-calendar-days';

    protected static ?string $navigationGroup = 'Schedule';

    protected static ?int $navigationSort = 1;

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Select::make('level_id')
                    ->label('Level')
                    ->required()
                    ->live()
                    ->options(Level::all()->pluck('name', 'id'))
                    ->searchable(),
                Select::make('module_id')
                    ->label('Module')
                    ->required()
                    ->live()
                    ->options(
                        function (Get $get) {
                            return Module::query()
                                ->whereHas(
                                    'levels',
                                    fn (Builder $query) => $query->where('levels.id', $get('level_id')),
                                )
                                ->pluck('name', 'id');
                        }
                    ),
                TextInput::make('duration')
                    ->required()
                    ->helperText(function (Get $get) {
                        if (!$get('module_id')) {
                            return '';
                        } else if (Module::find($get('module_id'))->name == 'Speaking') {
                            return 'Minutes per candidate';
                        } else {
                            return 'Minutes per group';
                        }
                    })
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('level.name'),
                TextColumn::make('module.name'),
                TextColumn::make('duration')
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
            'index' => Pages\ListScheduleSettings::route('/'),
            'create' => Pages\CreateScheduleSettings::route('/create'),
            'edit' => Pages\EditScheduleSettings::route('/{record}/edit'),
        ];
    }
}
