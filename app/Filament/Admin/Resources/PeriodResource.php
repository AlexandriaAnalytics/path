<?php

namespace App\Filament\Admin\Resources;

use App\Filament\Admin\Resources\PeriodResource\Pages;
use App\Filament\Admin\Resources\PeriodResource\RelationManagers;
use App\Models\Period;
use Closure;
use Filament\Forms;
use Filament\Forms\Components\Fieldset;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\SoftDeletingScope;
use Illuminate\Validation\Rule;
use Illuminate\Validation\Rules\Unique;

class PeriodResource extends Resource
{
    protected static ?string $model = Period::class;

    protected static ?string $navigationGroup = 'Settings';

    protected static bool $hasTitleCaseModelLabel = false;

    protected static ?string $navigationLabel = 'Registration periods';

    protected static ?string $heading = 'Periods in which institutes can register students for exams';

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Fieldset::make('Period Information')
                    ->schema([
                        Forms\Components\TextInput::make('name')
                            ->required()
                            ->maxLength(255),
                        Forms\Components\TextInput::make('description')
                            ->maxLength(255),
                        Forms\Components\DateTimePicker::make('starts_at')
                            ->native(false)
                            ->closeOnDateSelection()
                            ->minutesStep(5)
                            ->seconds(false)
                            ->required()
                            // https://filamentphp.com/docs/3.x/forms/validation#custom-rules
                            ->rules([
                                function () {
                                    return function (string $attribute, $value, Closure $fail) {
                                        if ($period = Period::query()
                                            ->where('starts_at', '<=', $value)
                                            ->where('ends_at', '>=', $value)
                                            ->first()
                                        ) {
                                            $fail("The period overlaps with the period '{$period->name}'");
                                        }
                                    };
                                },
                            ]),
                        Forms\Components\DateTimePicker::make('ends_at')
                            ->native(false)
                            ->closeOnDateSelection()
                            ->minutesStep(5)
                            ->seconds(false)
                            ->required()
                            ->after('starts_at')
                            ->rules([
                                function () {
                                    return function (string $attribute, $value, Closure $fail) {
                                        if ($period = Period::query()
                                            ->where('starts_at', '<=', $value)
                                            ->where('ends_at', '>=', $value)
                                            ->first()
                                        ) {
                                            $fail("The period overlaps with the period '{$period->name}'");
                                        }
                                    };
                                },
                            ])
                    ]),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('name')
                    ->searchable(),
                Tables\Columns\TextColumn::make('starts_at')
                    ->dateTime()
                    ->sortable(),
                Tables\Columns\TextColumn::make('ends_at')
                    ->dateTime()
                    ->sortable(),
            ])
            ->filters([
                Tables\Filters\TrashedFilter::make(),
            ])
            ->actions([
                Tables\Actions\ViewAction::make(),
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
            'index' => Pages\ListPeriods::route('/'),
            'create' => Pages\CreatePeriod::route('/create'),
            'view' => Pages\ViewPeriod::route('/{record}'),
            'edit' => Pages\EditPeriod::route('/{record}/edit'),
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
