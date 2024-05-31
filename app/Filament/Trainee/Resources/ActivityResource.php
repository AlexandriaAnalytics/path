<?php

namespace App\Filament\Trainee\Resources;

use App\Filament\Trainee\Resources\ActivityResource\Pages;
use App\Filament\Trainee\Resources\ActivityResource\RelationManagers;
use App\Models\Activity;
use App\Models\Answer;
use App\Models\ExaminerActivity;
use App\Models\ExaminerQuestion;
use App\Models\MultipleChoice;
use App\Models\OpenAnswer;
use App\Models\Performance;
use App\Models\Record;
use App\Models\Section;
use App\Models\StatusActivity;
use App\Models\Trainee;
use App\Models\TrueFalse;
use Barryvdh\DomPDF\Facade\Pdf;
use Closure;
use Cmgmyr\PHPLOC\Log\Text;
use Filament\Forms;
use Filament\Forms\Components\CheckboxList;
use Filament\Forms\Components\FileUpload;
use Filament\Forms\Components\MarkdownEditor;
use Filament\Forms\Components\Radio;
use Filament\Forms\Components\Section as ComponentsSection;
use Filament\Forms\Components\Textarea as ComponentsTextarea;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\ViewField;
use Filament\Forms\Components\Wizard;
use Filament\Forms\Components\Wizard\Step;
use Filament\Forms\Form;
use Filament\Forms\Set;
use Filament\Infolists\Components\TextEntry;
use Filament\Resources\Resource;
use Filament\Support\Colors\Color;
use Filament\Support\Enums\MaxWidth;
use Filament\Tables;
use Filament\Tables\Actions\Action;
use Filament\Tables\Actions\ActionGroup;
use Filament\Tables\Columns\ColorColumn;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\SoftDeletingScope;
use Illuminate\Support\Facades\File;
use Mpdf\Tag\TextArea;
use Stripe\FundingInstructions;

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
                TextColumn::make('statusActivity.id')
                    ->label('Status section')
                    ->formatStateUsing(function ($state) {
                        return StatusActivity::find($state)->name;
                    })
                    ->badge()
                    ->color(function ($state) {
                        return Color::hex(StatusActivity::find($state)->color);
                    })
                    ->sortable()
                    ->searchable()
                    ->toggleable(isToggledHiddenByDefault: false),
                TextColumn::make('result')
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
                Action::make('download-pdf')
                    ->visible(function (Record $record) {
                        return $record->result == null ? false : true;
                    })
                    ->label('PDF')
                    ->icon('heroicon-o-document')
                    ->action(function (Record $record) {
                        //dd($record->trainee->answers[3]->question->multipleChoices[0]->answers);
                        try {
                            $filename = "{$record->trainee->user->name} {$record->trainee->user->surname} - {$record->section->name}.pdf";
                            $pdfPath = storage_path('app/temp_pdfs') . '/' . $filename;
                            // Ensure the temporary directory exists and has write permissions
                            if (!File::exists(storage_path('app/temp_pdfs'))) {
                                File::makeDirectory(storage_path('app/temp_pdfs'), 0755, true); // Create directory with appropriate permissions
                            }
                            Pdf::loadView('pdf.record', ['record' => $record])
                                ->save($pdfPath);
                            return response()->download($pdfPath, $filename, [
                                'Content-Type' => 'application/pdf',
                            ]);
                        } catch (\Exception $e) {
                            return response()->json(['error' => 'PDF generation or download failed'], 500);
                        }
                    }),
                Tables\Actions\Action::make('access')
                    ->label('Access')
                    ->icon('heroicon-m-pencil-square')
                    ->color('warning')
                    ->visible(function (Record $record) {
                        return Activity::where('section_id', $record->section_id)->where('type_of_training_id', $record->trainee->typeOfTraining->id)->whereNull('deleted_at')->first();
                    })
                    ->form(function (Record $record) {
                        $activity = Activity::where('section_id', $record->section_id)->where('type_of_training_id', $record->trainee->typeOfTraining->id)->whereNull('deleted_at')->first();
                        $steps = [];
                        if ($activity) {
                            $questions = $activity->questions;
                            foreach ($questions as $index => $question) {
                                $schema = [];
                                if (!$record->result || ($record->result && !$question->evaluation)) {
                                    if ($question->url) {
                                        $schema[] = ViewField::make('field')
                                            ->hiddenLabel()
                                            ->view('filament.iframes')
                                            ->viewData(['url' => $question->url]);
                                    }
                                    if ($question->multimedia) {
                                        $multimediaUrl = asset('storage/' . $question->multimedia);
                                        $schema[] = ViewField::make('field')
                                            ->hiddenLabel()
                                            ->view('filament.iframes')
                                            ->viewData(['url' => $multimediaUrl]);
                                        /* if (strpos($question->multimedia, 'mp4') !== false) {
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
                                        } */
                                    }

                                    if ($question->description) {
                                        $schema[] = MarkdownEditor::make('description' . $index)
                                            ->disabled()
                                            ->hiddenLabel()
                                            ->default($question->description);
                                    }

                                    foreach ($question->question_type as $indice => $type) {
                                        if ($type == 'True or false') {
                                            $schema[] =
                                                TextInput::make('question' . $indice)
                                                ->readOnly()
                                                ->hiddenLabel()
                                                ->default(TrueFalse::find($question->question_ids[$indice])->question);
                                            $schema[] =
                                                Radio::make('true_or_false' . '-' . $index . '-' . $indice)
                                                ->hiddenLabel()
                                                ->options([
                                                    1 => 'True',
                                                    0 => 'False'
                                                ])
                                                ->columns(3);
                                        }

                                        if ($type == 'True or false with justification') {
                                            $schema[] =
                                                TextInput::make('question' . $indice)
                                                ->readOnly()
                                                ->hiddenLabel()
                                                ->default(TrueFalse::find($question->question_ids[$indice])->question);
                                            $schema[] = Radio::make('true_or_false_justify' . '-' . $index . '-' . $indice)
                                                ->hiddenLabel()
                                                ->options([
                                                    1 => 'True',
                                                    0 => 'False'
                                                ])
                                                ->columns(3);
                                            $schema[] = TextInput::make('justify' . $index)
                                                ->label('Justify the answer');
                                        }

                                        if ($type == 'Multiple choice with one answer') {
                                            $schema[] =
                                                TextInput::make('question' . $indice)
                                                ->readOnly()
                                                ->hiddenLabel()
                                                ->default(MultipleChoice::find($question->question_ids[$indice])->question);
                                            $schema[] =
                                                Radio::make('multiplechoice_one_answer' . '-' . $index . '-' . $indice)
                                                ->hiddenLabel()
                                                ->options(MultipleChoice::find($question->question_ids[$indice])->answers);
                                        }

                                        if ($type == 'Multiple choice with many answers') {
                                            $schema[] =
                                                TextInput::make('question' . $indice)
                                                ->readOnly()
                                                ->hiddenLabel()
                                                ->default(MultipleChoice::find($question->question_ids[$indice])->question);
                                            $schema[] =
                                                CheckboxList::make('multiplechoice_many_answers' . '-' . $index . '-' . $indice)
                                                ->hiddenLabel()
                                                ->options(MultipleChoice::find($question->question_ids[$indice])->answers);
                                        }

                                        if ($type == 'Open answer') {
                                            $schema[] =
                                                TextInput::make('question' . $indice)
                                                ->readOnly()
                                                ->hiddenLabel()
                                                ->default(OpenAnswer::find($question->question_ids[$indice])->question);
                                            $schema[] =
                                                ComponentsTextarea::make('open_answer' . '-' . $index . '-' . $indice)
                                                ->hiddenLabel();
                                        }
                                    }
                                    $steps[] = Step::make($question->title)
                                        ->schema($schema);
                                }
                            }
                        }
                        return [
                            Wizard::make($steps)->columnSpanFull()->disabled(function (Record $record) {
                                return $record->result;
                            })
                        ];
                    })
                    ->action(function (array $data, Record $record) {
                        $activity = Activity::where('section_id', $record->section_id)->where('type_of_training_id', $record->trainee->typeOfTraining->id)->first();
                        $questions = $activity->questions;

                        $correct = true;
                        foreach ($questions as $index => $question) {
                            foreach ($question['question_type'] as $indice => $type) {
                                if ($type == 'True or false') {
                                    $answer = new Answer();
                                    $answer->question_type = $type;
                                    $answer->trainee_id = $record->trainee->id;
                                    $answer->question_id = $question['question_ids'][$indice];
                                    $answer->selected_option = $data['true_or_false' . '-' . $index . '-' . $indice];
                                    $answer->save();
                                    if (TrueFalse::find($answer->question_id)->true != $answer->selected_option && $question->evaluation) {
                                        $correct = false;
                                    }
                                }

                                if ($type == 'True or false with justification') {
                                    $answer = new Answer();
                                    $answer->question_type = $type;
                                    $answer->trainee_id = $record->trainee->id;
                                    $answer->question_id = $question['question_ids'][$indice];
                                    $answer->selected_option = $data['true_or_false_justify' . '-' . $index . '-' . $indice];
                                    $answer->answer_text = $data['justify' . $index];
                                    $answer->save();

                                    if (TrueFalse::find($answer->question_id)->true != $answer->selected_option && $question->evaluation) {
                                        $correct = false;
                                    }
                                }

                                if ($type == 'Multiple choice with one answer') {
                                    $answer = new Answer();
                                    $answer->question_type = $type;
                                    $answer->trainee_id = $record->trainee->id;
                                    $answer->question_id = $question['question_ids'][$indice];
                                    $answer->selected_option = $data['multiplechoice_one_answer' . '-' . $index . '-' . $indice];
                                    $answer->save();

                                    if (MultipleChoice::find($answer->question_id)->correct[$answer->selected_option] != 'false' && $question->evaluation) {
                                        $correct = false;
                                    }
                                }

                                if ($type == 'Multiple choice with many answers') {
                                    //dd($data['multiplechoice_many_answers' . '-' . $index . '-' . $indice]);
                                    $answer = new Answer();
                                    $answer->question_type = $type;
                                    $answer->trainee_id = $record->trainee->id;
                                    $answer->question_id = $question['question_ids'][$indice];
                                    $answer->selected_option = implode(',', $data['multiplechoice_many_answers' . '-' . $index . '-' . $indice]);
                                    $answer->save();
                                    foreach ($data['multiplechoice_many_answers' . '-' . $index . '-' . $indice] as $answer) {
                                        if (MultipleChoice::find($answer)->correct[$answer] != 'false' && $question->evaluation) {
                                            $correct = false;
                                        }
                                    }
                                }

                                if ($type == 'Open answer') {
                                    $answer = new Answer();
                                    $answer->question_type = $type;
                                    $answer->trainee_id = $record->trainee->id;
                                    $answer->question_id = $question['question_ids'][$indice];
                                    $answer->answer_text = $data['open_answer' . '-' . $index . '-' . $indice];
                                    $answer->save();
                                }
                            }
                        }


                        $record->result = $correct ? 'Certified' : 'To be reviewed';

                        $record->save();
                    })
                    ->modalWidth(MaxWidth::SevenExtraLarge)


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
            //'create' => Pages\CreateActivity::route('/create'),
            //'edit' => Pages\EditActivity::route('/{record}/edit'),
        ];
    }
}
