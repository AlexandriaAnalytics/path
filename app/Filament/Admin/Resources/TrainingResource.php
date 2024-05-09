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
use Filament\Forms\Components\Actions\Action;
use Filament\Forms\Components\Checkbox;
use Filament\Forms\Components\FileUpload;
use Filament\Forms\Components\Grid;
use Filament\Forms\Components\Repeater;
use Filament\Forms\Components\Section;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Form;
use Filament\Forms\Get;
use Filament\Forms\Set;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\SelectFilter;
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
                    ->unique()
                    ->columnSpanFull(),

                Textarea::make('description')
                    ->rows(10)
                    ->cols(20)
                    ->columnSpanFull(),

                Select::make('section_id')
                    ->label('Section')
                    ->required()
                    ->options(ModelsSection::all()->pluck('name', 'id')),

                Select::make('question_type')
                    ->label('Question type')
                    ->required()
                    ->options(TypeQuestion::class),

                Select::make('activity_type')
                    ->label('Activity')
                    ->required()
                    ->options(ActivityType::class)
                    ->reactive(),

                //True or false
                Section::make('True Or False')
                    ->relationship('trueOrFalse')
                    ->schema([
                        Textarea::make('question')
                            ->required(),

                        Grid::make()
                            ->schema([
                                Checkbox::make('true'),
                                Checkbox::make('false')
                            ])->columns(2)
                    ])->hidden(function (callable $get) {
                        if ($get('activity_type') == 'true or false') {
                            return false;
                        }
                        return true;
                    }),
                //True or false
                Section::make('True Or False')
                    ->relationship('activityTrueOrFalseJustify')
                    ->schema([
                        Textarea::make('question')
                            ->required()
                            ->cols(20),
                        Grid::make()
                            ->schema([
                                Checkbox::make('true'),
                                Checkbox::make('false')
                            ])->columns(2),
                        Textarea::make('justify')
                            ->rows(5)
                            ->cols(20),

                    ])->hidden(function (callable $get) {
                        if ($get('activity_type') == 'true or false justify') {
                            return false;
                        }
                        return true;
                    }),

                //MultipleChoice
                Section::make('Multiple choice')
                    ->live()
                    ->relationship('ativityMultipleChoice')
                    ->schema([
                        Textarea::make('question')
                            ->required()
                            ->cols(20),
                        Repeater::make('answers')
                            ->relationship('multipleChoiceAnswer')
                            ->schema([
                                Checkbox::make('check'),
                                TextInput::make('answer')
                            ])
                    ])->hidden(function (callable $get) {
                        if ($get('activity_type') == 'multiple choice') {
                            return false;
                        }
                        return true;
                    }),

                //Multiple choice multiple answers
                Section::make('Multiple choice multiple answers')
                    ->live()
                    ->relationship('ativityMultipleChoice')
                    ->schema([
                        Textarea::make('question')
                            ->required()
                            ->cols(20),
                        Repeater::make('answers')
                            ->relationship('multipleChoiceAnswer')
                            ->schema([
                                Checkbox::make('check'),
                                TextInput::make('answer')
                            ])/* 
                            ->afterStateUpdated(function (Get $get, Set $set) {
                                dd($get('MultipleChoice'));
                            }) */
                    ])->hidden(function (callable $get) {
                        if ($get('activity_type') == 'multiple choice multiple answers') {
                            return false;
                        }
                        return true;
                    }),
                //Question answer
                Section::make('Question answer')
                    ->relationship('questionAnswer')
                    ->schema([
                        Textarea::make('question')
                            ->required()
                            ->cols(20),

                        Textarea::make('answer')
                            ->required()
                            ->rows(10)
                            ->cols(20),

                    ])
                    ->hidden(function (callable $get) {
                        if ($get('activity_type') == 'question answer') {
                            return false;
                        }
                        return true;
                    }),
                //Media
                Section::make('Multimedia')
                    ->relationship('multimedia')
                    ->schema([
                        FileUpload::make('file')
                            ->required()
                            ->directory('activity_multimedia')
                            ->enableOpen()
                            ->columnSpanFull(),
                    ])
                    ->hidden(function (callable $get) {
                        if ($get('activity_type') == 'multimedia') {
                            return false;
                        }
                        return true;
                    })

            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('name')
                    ->sortable()
                    ->searchable()
                    ->toggleable(isToggledHiddenByDefault: false),
                TextColumn::make('description')
                    ->sortable()
                    ->searchable()
                    ->toggleable(isToggledHiddenByDefault: false)
                    ->default('-'),
                TextColumn::make('question_type')
                    ->sortable()
                    ->searchable()
                    ->toggleable(isToggledHiddenByDefault: false),
                TextColumn::make('activity_type')
                    ->sortable()
                    ->searchable()
                    ->toggleable(isToggledHiddenByDefault: false),
            ])
            ->filters([
                SelectFilter::make('question_type')
                    ->options(TypeQuestion::class),
                SelectFilter::make('activity_type')
                    ->options(ActivityType::class)
            ])
            ->actions([
                Tables\Actions\EditAction::make(),
            ])
            ->bulkActions([
                Tables\Actions\BulkActionGroup::make([
                    Tables\Actions\DeleteBulkAction::make(),
                ]),
            ])
            ->defaultSort('created_at', 'desc');
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
}
