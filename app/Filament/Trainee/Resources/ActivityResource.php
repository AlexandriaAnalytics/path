<?php

namespace App\Filament\Trainee\Resources;

use App\Filament\Trainee\Resources\ActivityResource\Pages;
use App\Filament\Trainee\Resources\ActivityResource\RelationManagers;
use App\Models\Activity;
use App\Models\ExaminerActivity;
use App\Models\ExaminerQuestion;
use App\Models\Performance;
use App\Models\Record;
use App\Models\Section;
use App\Models\Trainee;
use Closure;
use Filament\Forms;
use Filament\Forms\Components\CheckboxList;
use Filament\Forms\Components\FileUpload;
use Filament\Forms\Components\MarkdownEditor;
use Filament\Forms\Components\Radio;
use Filament\Forms\Components\Section as ComponentsSection;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Wizard;
use Filament\Forms\Components\Wizard\Step;
use Filament\Forms\Form;
use Filament\Forms\Set;
use Filament\Infolists\Components\TextEntry;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Columns\ColorColumn;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\SoftDeletingScope;

class ActivityResource extends Resource
{
    protected static ?string $model = Record::class;

    protected static ?string $navigationIcon = 'heroicon-o-rectangle-stack';

    protected static ?string $navigationLabel = 'Path Training Programme';

    protected static ?string $modelLabel = 'Path Training Programme';

    protected static ?string $pluralModelLabel = 'Path Training Programme';

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                //
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->query(function () {
                $trainee = Trainee::where('user_id', auth()->user()->id)->first();
                return Record::where('trainee_id', $trainee->id);
            })
            ->columns([
                TextColumn::make('section.name')
                    ->sortable()
                    ->searchable()
                    ->toggleable(isToggledHiddenByDefault: false),
                ColorColumn::make('statusActivity.color')
                    ->toggleable(isToggledHiddenByDefault: false),
                TextColumn::make('performance.answer')
                    ->sortable()
                    ->searchable()
                    ->default('-')
                    ->toggleable(isToggledHiddenByDefault: false),
                TextColumn::make('comments')
                    ->sortable()
                    ->searchable()
                    ->default('-')
                    ->toggleable(isToggledHiddenByDefault: false),
            ])
            ->filters([
                //
            ])
            ->actions([
                Tables\Actions\Action::make('solve')
                    ->icon('heroicon-m-pencil-square')
                    ->iconButton()
                    ->color('warning')
                    ->form(function (Record $record) {
                        $examinerActivity = ExaminerActivity::where('section_id', $record->section_id)->first();

                        if (!$examinerActivity || !is_array($examinerActivity->questions)) {
                            return [];
                        }


                        $steps = [];
                        foreach ($examinerActivity->questions as $index => $questionId) {
                            $schema = [];
                            $questionModel = ExaminerQuestion::find($questionId);

                            if ($questionModel) {
                                $schema = [
                                    TextInput::make('question' . $index)
                                        ->default($questionModel->question)
                                        ->disabled()
                                        ->hiddenLabel()
                                        ->columnSpanFull(),
                                    TextInput::make('description' . $index)
                                        ->default($questionModel->description)
                                        ->hidden(fn () => empty($questionModel->description))
                                        ->disabled()
                                        ->hiddenLabel()
                                        ->columnSpanFull()
                                ];

                                if ($questionModel->multimedia) {
                                    $multimediaUrl = asset('storage/' . $questionModel->multimedia);
                                    if (strpos($questionModel->multimedia, 'mp4') !== false) {
                                        $schema[] = MarkdownEditor::make('video' . $index)
                                            ->disabled()
                                            ->hiddenLabel()
                                            ->default('<video width="320" height="240" controls style="width: 100%;">
                                                          <source src="' . $multimediaUrl . '" type="video/mp4">
                                                          Your browser does not support the video tag.
                                                       </video>')
                                            ->columnSpanFull();
                                    } else {
                                        $schema[] = MarkdownEditor::make('image' . $index)
                                            ->disabled()
                                            ->hiddenLabel()
                                            ->default('<img src="' . $multimediaUrl . '" alt="Multimedia" style="max-width: 100%; height: auto;">')
                                            ->columnSpanFull();
                                    }
                                }


                                $schema[] = Radio::make('answers' . $index)
                                    ->options($questionModel->aswers)
                                    ->label('Answers')
                                    ->disabled(fn () => $index == 0)
                                    ->reactive()
                                    ->afterStateUpdated(function ($set, $state) use ($index, $questionModel) {
                                        $performanceId = $questionModel->performance[$state] ?? null;
                                        if ($performanceId) {
                                            $performance = Performance::find($performanceId);
                                            if ($performance) {
                                                $set('performance' . $index, $performance->answer);
                                            }
                                        }
                                    });

                                if ($index == 0) {
                                    $schema[] = CheckboxList::make('answers' . $index)
                                        ->label('Performance')
                                        ->options($questionModel->performance)
                                        ->disabled();
                                } else {
                                    $schema[] = TextInput::make('performance' . $index)
                                        ->disabled()
                                        ->label('Performance')
                                        ->hidden(fn () => $index == count($examinerActivity->questions) - 1)
                                        ->default('Choose an answer');
                                }
                            }
                            $steps[] = Step::make('Question ' . ($index + 1))
                                ->schema($schema)->columns(2);
                        }
                        return [
                            Wizard::make($steps)
                        ];
                    })
                    ->action(function (array $data, Record $record) {
                        $examinerActivity = ExaminerActivity::where('section_id', $record->section_id)->first();
                        $record->performance_id = ExaminerQuestion::find($examinerActivity->questions[count($examinerActivity->questions) - 1])->performance[$data['answers' . count($examinerActivity->questions) - 1]];
                        $record->save();
                    })
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
            'index' => Pages\ListActivities::route('/'),
            'create' => Pages\CreateActivity::route('/create'),
            'edit' => Pages\EditActivity::route('/{record}/edit'),
        ];
    }
}
