<?php

namespace App\Filament\Admin\Resources;

use App\Enums\ActivityType;
use App\Filament\Admin\Resources\ActivityResource\Pages;
use App\Filament\Admin\Resources\ActivityResource\RelationManagers;
use App\Models\Activity;
use App\Models\Performance;
use App\Models\Section;
use App\Models\TypeOfTraining;
use Filament\Forms;
use Filament\Forms\Components\Builder as ComponentsBuilder;
use Filament\Forms\Components\Builder\Block;
use Filament\Forms\Components\Checkbox;
use Filament\Forms\Components\FileUpload;
use Filament\Forms\Components\Grid;
use Filament\Forms\Components\Radio;
use Filament\Forms\Components\Repeater;
use Filament\Forms\Components\RichEditor;
use Filament\Forms\Components\Section as ComponentsSection;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Toggle;
use Filament\Forms\Form;
use Filament\Forms\Get;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Grouping\Group;
use Filament\Tables\Table;
use FilamentTiptapEditor\TiptapEditor;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\SoftDeletingScope;

class ActivityResource extends Resource
{
    protected static ?string $model = Activity::class;

    protected static ?string $modelLabel = 'Section';

    protected static bool $hasTitleCaseModelLabel = false;

    protected static ?string $navigationIcon = 'heroicon-o-rectangle-stack';

    protected static ?string $navigationGroup = 'Training';

    protected static ?int $navigationSort = 2;

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Select::make('section_id')
                    ->label('Section')
                    ->required()
                    ->options(Section::all()->pluck('name', 'id')),
                Select::make('type_of_training_id')
                    ->label('Type of training')
                    ->required()
                    ->reactive()
                    ->options(TypeOfTraining::all()->pluck('name', 'id')),
                //stages de candidates
                Repeater::make('stages')
                    ->live()
                    ->visible(fn (Get $get) => $get('type_of_training_id') && TypeOfTraining::find($get('type_of_training_id'))->name == 'Candidate')
                    ->schema([
                        ComponentsBuilder::make('content')
                            ->blocks([
                                Block::make('title')
                                    ->schema([
                                        TextInput::make('content')
                                            ->label('Title')
                                            ->required()
                                    ]),
                                Block::make('text')
                                    ->schema([
                                        TiptapEditor::make('content')
                                            ->label('Text')
                                    ]),
                                Block::make('url')
                                    ->schema([
                                        TextInput::make('content')
                                            ->label('Url')
                                    ]),

                                Block::make('multimedia')
                                    ->schema([
                                        FileUpload::make('content')
                                            ->label('Multimedia')
                                    ]),
                                Block::make('audio')
                                    ->schema([
                                        FileUpload::make('content')
                                            ->label('Audio')
                                            ->acceptedFileTypes(['audio/*', 'video/*']),
                                        TextInput::make('replay')
                                            ->label('Number of times it can be played')
                                            ->numeric()
                                    ]),
                                Block::make('questions')
                                    ->schema([
                                        Repeater::make('content')
                                            ->schema([
                                                Select::make('question_type')
                                                    ->live()
                                                    ->options(ActivityType::class),
                                                TextInput::make('question')
                                                    ->required(),
                                                Grid::make()
                                                    ->schema([
                                                        Checkbox::make('true'),
                                                        Select::make('comments_true')
                                                            ->hiddenLabel()
                                                            ->options(Performance::all()->pluck('answer', 'id')),
                                                    ])
                                                    ->columns(2)
                                                    ->visible(fn (Get $get) => ($get('question_type') == 'True or false') || ($get('question_type') == 'True or false with justification')),
                                                Grid::make()
                                                    ->schema([
                                                        Checkbox::make('false'),
                                                        Select::make('comments_false')
                                                            ->hiddenLabel()
                                                            ->options(Performance::all()->pluck('answer', 'id')),
                                                    ])
                                                    ->columns(2)
                                                    ->visible(fn (Get $get) => ($get('question_type') == 'True or false') || ($get('question_type') == 'True or false with justification')),
                                                Repeater::make('multiplechoice')
                                                    ->schema([
                                                        Grid::make()
                                                            ->schema([
                                                                TextInput::make('answer'),
                                                                Checkbox::make('correct')
                                                                    ->inline(false),
                                                                Select::make('comments')
                                                                    ->options(Performance::all()->pluck('answer', 'id'))
                                                            ])
                                                            ->columns(3)
                                                    ])
                                                    ->visible(fn (Get $get) => ($get('question_type') == 'Multiple choice with one answer')),
                                                Repeater::make('multiplechoice')
                                                    ->schema([
                                                        Grid::make()
                                                            ->schema([
                                                                TextInput::make('answer'),
                                                                Checkbox::make('correct')
                                                                    ->inline(false),
                                                                Checkbox::make('correct_in_pdf')
                                                                    ->inline(false),
                                                                Select::make('comments')
                                                                    ->options(Performance::all()->pluck('answer', 'id'))
                                                            ])
                                                            ->columns(4)
                                                    ])
                                                    ->visible(fn (Get $get) => ($get('question_type') == 'Multiple choice with many answers'))
                                            ]),
                                    ])
                            ])
                    ])
                    ->columnSpanFull(),

                //sections de trainees
                Repeater::make('sections')
                    ->live()
                    ->visible(fn (Get $get) => $get('type_of_training_id') && TypeOfTraining::find($get('type_of_training_id'))->name !== 'Candidate')
                    ->schema([
                        TextInput::make('title')
                            ->required(),
                        RichEditor::make('description'),
                        TextInput::make('url'),
                        FileUpload::make('multimedia'),
                        TiptapEditor::make('text'),
                        Toggle::make('evaluation')
                            ->label('Include in marking stage'),
                        Repeater::make('questions')
                            ->schema([
                                Select::make('question_type')
                                    ->live()
                                    ->options(ActivityType::class),
                                TextInput::make('question')
                                    ->required(),
                                Grid::make()
                                    ->schema([
                                        Checkbox::make('true'),
                                        Select::make('comments_true')
                                            ->hiddenLabel()
                                            ->options(Performance::all()->pluck('answer', 'id')),
                                    ])
                                    ->columns(2)
                                    ->visible(fn (Get $get) => ($get('question_type') == 'True or false') || ($get('question_type') == 'True or false with justification')),
                                Grid::make()
                                    ->schema([
                                        Checkbox::make('false'),
                                        Select::make('comments_false')
                                            ->hiddenLabel()
                                            ->options(Performance::all()->pluck('answer', 'id')),
                                    ])
                                    ->columns(2)
                                    ->visible(fn (Get $get) => ($get('question_type') == 'True or false') || ($get('question_type') == 'True or false with justification')),
                                Repeater::make('multiplechoice')
                                    ->schema([
                                        Grid::make()
                                            ->schema([
                                                TextInput::make('answer'),
                                                Checkbox::make('correct')
                                                    ->inline(false),
                                                Select::make('comments')
                                                    ->options(Performance::all()->pluck('answer', 'id'))
                                            ])
                                            ->columns(3)
                                    ])
                                    ->visible(fn (Get $get) => ($get('question_type') == 'Multiple choice with one answer')),
                                Repeater::make('multiplechoice')
                                    ->schema([
                                        Grid::make()
                                            ->schema([
                                                TextInput::make('answer'),
                                                Checkbox::make('correct')
                                                    ->inline(false),
                                                Checkbox::make('correct_in_pdf')
                                                    ->inline(false),
                                                Select::make('comments')
                                                    ->options(Performance::all()->pluck('answer', 'id'))
                                            ])
                                            ->columns(4)
                                    ])
                                    ->visible(fn (Get $get) => ($get('question_type') == 'Multiple choice with many answers'))
                            ]),
                        TiptapEditor::make('text_after_answer')

                    ])
                    ->columnSpanFull(),
                Textarea::make('comment_at_the_end')
                    ->helperText('The text will be displayed when the trainee submits the last activity.')
                    ->columnSpanFull()
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->groups([
                Group::make('section.name')
                    ->groupQueryUsing(fn (Builder $query) => $query->groupBy('section.name'))
                    ->collapsible(),
                Group::make('typeOfTraining.name')
                    ->groupQueryUsing(fn (Builder $query) => $query->groupBy('typeOfTraining.name'))
                    ->collapsible(),
            ])
            ->columns([
                TextColumn::make('section.name'),
                TextColumn::make('typeOfTraining.name')
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
            ])
            ->defaultSort('created_at', 'desc');;
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
            'index' => Pages\ListActivities::route('/'),
            'create' => Pages\CreateActivity::route('/create'),
            'edit' => Pages\EditActivity::route('/{record}/edit'),
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
