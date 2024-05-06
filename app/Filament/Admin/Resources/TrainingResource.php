<?php

namespace App\Filament\Admin\Resources;

use App\Enums\ActivityType;
use App\Enums\TypeQuestion;
use App\Filament\Admin\Resources\TrainingResource\Pages;
use App\Filament\Admin\Resources\TrainingResource\RelationManagers;
use App\Models\Level;
use App\Models\Section as ModelsSection;
use App\Models\Training;
use Closure;
use Filament\Forms;
use Filament\Forms\Components\Checkbox;
use Filament\Forms\Components\Grid;
use Filament\Forms\Components\Repeater;
use Filament\Forms\Components\Section;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\SoftDeletingScope;
use phpDocumentor\Reflection\Types\This;

class TrainingResource extends Resource
{
    protected static ?string $model = Training::class;

    // protected static ?string $navigationIcon = 'heroicon-o-rectangle-stack';
    protected static ?string $navigationGroup = 'Settings';
    protected static ?string $modelLabel = 'Training';
    protected static ?string $slug = 'training';

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                TextInput::make('name')
                    ->label('Name')
                    ->required()
                    ->columnSpanFull(),

                Textarea::make('description')
                    ->rows(10)
                    ->cols(20)
                    ->columnSpanFull(),

                Select::make('section_id')
                    ->label('Section')
                    ->options(ModelsSection::all()->pluck('name', 'id')),

                Select::make('question_type')
                    ->label('Question type')
                    ->options(TypeQuestion::class),

                Select::make('activity')
                    ->label('Activity')
                    ->options(ActivityType::class)
                    ->reactive(),

                //True or false
                // Section::make('True Or False')
                //     ->relationship('activityTrueOrFalse')
                //     ->schema([
                //         Textarea::make('question'),

                //         Grid::make()
                //             ->schema([
                //                 Checkbox::make('true'),
                //                 Checkbox::make('false')
                //             ])->columns(2)
                //     ])->hidden(function (callable $get) {
                //         if ($get('activity') == 'true_or_false') {
                //             return false;
                //         }
                //         return true;
                //     }),
            
                ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('name'),
                TextColumn::make('description'),
                TextColumn::make('question_type'),
                TextColumn::make('activity_type'),
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
            'index' => Pages\ListTrainings::route('/'),
            'create' => Pages\CreateTraining::route('/create'),
            'edit' => Pages\EditTraining::route('/{record}/edit'),
        ];
    }

    public static function getPluralLabel(): string
    {
        return ('Training');
    }

    public static function trueOrFalseForm(Form $form): Form
    {
        return $form
            ->schema([
                Textarea::make('question'),

                Checkbox::make('true'),

                Checkbox::make('false')
            ]);
    }
}
