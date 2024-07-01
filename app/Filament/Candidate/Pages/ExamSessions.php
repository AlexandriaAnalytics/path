<?php

namespace App\Filament\Candidate\Pages;

use App\Models\Activity;
use App\Models\Candidate;
use App\Models\candidateAnswer;
use App\Models\CandidateExam;
use App\Models\Exam;
use App\Models\MultipleChoice;
use App\Models\OpenAnswer;
use App\Models\Performance;
use App\Models\CandidateRecord;
use App\Models\Level;
use App\Models\StatusActivity;
use App\Models\TrueFalse;
use Barryvdh\DomPDF\Facade\Pdf;
use Filament\Forms\Components\CheckboxList;
use Filament\Forms\Components\Hidden;
use Filament\Forms\Components\Radio;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\ToggleButtons;
use Filament\Forms\Components\ViewField;
use Filament\Forms\Components\Wizard;
use Filament\Forms\Components\Wizard\Step;
use Filament\Forms\Concerns\InteractsWithForms;
use Filament\Forms\Contracts\HasForms;
use Filament\Forms\Form;
use Filament\Forms\Get;
use Filament\Forms\Set;
use Filament\Pages\Page;
use Filament\Support\Colors\Color;
use Filament\Support\Enums\MaxWidth;
use Filament\Tables\Actions\Action;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Concerns\InteractsWithTable;
use Filament\Tables\Contracts\HasTable;
use Filament\Tables\Table;
use FilamentTiptapEditor\TiptapEditor;
use Illuminate\Support\Facades\File;
use Illuminate\Support\HtmlString;
use Filament\Forms\Components\Actions\Action as WizardAction;

class ExamSessions extends Page implements HasForms, HasTable
{

    use InteractsWithTable;
    use InteractsWithForms;

    public $candidate, $exam;

    public function __construct()
    {
        $this->candidate = Candidate::find(session('candidate')->id);
        $this->exam = Exam::find(CandidateExam::where('candidate_id', $this->candidate->id)->first()->exam_id);
    }

    protected static ?string $navigationIcon = 'heroicon-o-academic-cap';
    protected static string $view = 'filament.candidate.pages.examSessions';

    public static function canAccess(): bool
    {
        return isset(session('candidate')->id);
    }

    public function mount()
    {
        abort_unless(static::canAccess(), 403);
    }

    public function table(Table $table): Table
    {
        return $table
            ->query(function () {
                return CandidateRecord::where('candidate_id', $this->candidate->id);
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
                Action::make('Join Zoom meeting')
                    ->visible(function (CandidateRecord $record) {
                        $currentDate = date('Y-m-d H:i:s');
                        $scheduledDate = CandidateExam::where('candidate_id', $record->candidate_id)->first()->exam->scheduled_date->modify('+3 hours');
                        $duration = CandidateExam::where('candidate_id', $record->candidate_id)->first()->exam->duration;
                        return $currentDate >= $scheduledDate && $currentDate <= $scheduledDate->modify('+' . $duration . ' minutes');
                    })
                    ->label('Join Zoom meeting')
                    ->icon('heroicon-o-chat-bubble-bottom-center-text')
                    ->url(function ($record) {
                        $record->attendance = 'Present';
                        $record->save();
                        return CandidateExam::where('candidate_id', $record->candidate_id)->first()->exam->location;
                    })
                    ->openUrlInNewTab(),
                Action::make('access')
                    ->visible(function (CandidateRecord $record) {
                        $currentDate = date('Y-m-d H:i:s');
                        $scheduledDate = CandidateExam::where('candidate_id', $record->candidate_id)->first()->exam->scheduled_date->modify('+3 hours');
                        $duration = CandidateExam::where('candidate_id', $record->candidate_id)->first()->exam->duration;
                        return /* $currentDate >= $scheduledDate && $currentDate <= $scheduledDate->modify('+' . $duration . ' minutes') && */ $record->can_access == 'can';
                    })
                    ->label('Access')
                    ->icon('heroicon-m-pencil-square')
                    ->color('warning')
                    /* ->visible(function (CandidateRecord $record) {
                    return Activity::where('section_id', $record->section_id)->where('typeOfT', $record->trainee->typeOfTraining->id)->whereNull('deleted_at')->first();
                }) */
                    ->modalSubmitAction(function (CandidateRecord $record) {
                        return $record->result != null ? false : null;
                    })
                    ->form(function (CandidateRecord $record) {
                        $activity = Activity::where('section_id', $record->section_id)->where('type_of_training_id', $record->type_of_training_id)->whereNull('deleted_at')->first();
                        $steps = [];
                        if ($activity) {
                            $questions = $activity->questions;
                            foreach ($questions as $index => $question) {
                                $schema = [];

                                if ($question->description) {
                                    $schema[] = TiptapEditor::make('description' . $index)
                                        ->hiddenLabel()
                                        ->default($question->description)
                                        ->disableBubbleMenus()
                                        ->disabled();
                                }

                                if ($question->url) {
                                    $schema[] = ViewField::make('field' . $index)
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
                                $schema[] = Hidden::make('visible_text_' . $index)
                                    ->default(false);

                                if ($question->text) {
                                    $schema[] = TiptapEditor::make('text' . $index)
                                        ->hiddenLabel()
                                        ->default($question->text)
                                        ->disableBubbleMenus()
                                        ->disabled()
                                        ->live()
                                        ->hidden(function ($get) use ($index, $question) {
                                            if ($question->title === 'Practice stage' || $question->title === 'Marking stage') {
                                                return true;
                                            } else {
                                                return false;
                                            }
                                        });
                                }

                                foreach ($question->question_type as $indice => $type) {
                                    if ($type == 'True or false') {
                                        $schema[] =
                                            TextInput::make('question' . '-' . $index . '-' . $indice)
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
                                            ->default(function (CandidateRecord $record) {
                                                $candidateAnswer = candidateAnswer::where('candidate_id', $record->candidate_id)
                                                    ->where('question_type', 'True or false')
                                                    ->first();

                                                if ($candidateAnswer) {
                                                    return $candidateAnswer->selected_option;
                                                }

                                                return null;
                                            })
                                            ->columns(3);
                                    }

                                    if ($type == 'True or false with justification') {
                                        $schema[] =
                                            TextInput::make('question' . '-' . $index . '-' . $indice)
                                            ->readOnly()
                                            ->hiddenLabel()
                                            ->default(TrueFalse::find($question->question_ids[$indice])->question);
                                        $schema[] = Radio::make('true_or_false_justify' . '-' . $index . '-' . $indice)
                                            ->hiddenLabel()
                                            ->options([
                                                1 => 'True',
                                                0 => 'False'
                                            ])
                                            ->default(function (CandidateRecord $record) {
                                                $candidateAnswer = candidateAnswer::where('candidate_id', $record->candidate_id)
                                                    ->where('question_type', 'True or false with justification')
                                                    ->first();
                                                if ($candidateAnswer) {
                                                    return $candidateAnswer->selected_option;
                                                }

                                                return null;
                                            })
                                            ->columns(3);
                                        $schema[] = TextInput::make('justify' . $index)
                                            ->label('Justify the answer')->default(function (CandidateRecord $record) {
                                                $candidateAnswer = candidateAnswer::where('candidate_id', $record->candidate_id)
                                                    ->where('question_type', 'True or false with justification')
                                                    ->first();
                                                if ($candidateAnswer) {
                                                    return $candidateAnswer->answer_text;
                                                }

                                                return null;
                                            });
                                    }

                                    if ($type == 'Multiple choice with one answer') {
                                        $schema[] =
                                            TextInput::make('question' . '-' . $index . '-' . $indice)
                                            ->readOnly()
                                            ->hiddenLabel()
                                            ->default(MultipleChoice::find($question->question_ids[$indice])->question);
                                        $schema[] =
                                            Radio::make('multiplechoice_one_answer' . '-' . $index . '-' . $indice)
                                            ->hiddenLabel()
                                            ->options(MultipleChoice::find($question->question_ids[$indice])->answers)
                                            ->default(function (CandidateRecord $record) {
                                                $candidateAnswer = candidateAnswer::where('candidate_id', $record->candidate_id)
                                                    ->where('question_type', 'Multiple choice with one answer')
                                                    ->first();
                                                if ($candidateAnswer) {
                                                    return $candidateAnswer->selected_option;
                                                }

                                                return null;
                                            });
                                    }

                                    if ($type == 'Multiple choice with many answers') {
                                        $schema[] =
                                            TextInput::make('question' . '-' . $index . '-' . $indice)
                                            ->readOnly()
                                            ->hiddenLabel()
                                            ->default(MultipleChoice::find($question->question_ids[$indice])->question);
                                        $schema[] =
                                            CheckboxList::make('multiplechoice_many_answers' . '-' . $index . '-' . $indice)
                                            ->hiddenLabel()
                                            ->live()
                                            ->reactive()
                                            ->options(MultipleChoice::find($question->question_ids[$indice])->answers)
                                            ->default(function (CandidateRecord $record) {
                                                $candidateAnswer = candidateAnswer::where('candidate_id', $record->candidate_id)
                                                    ->where('question_type', 'Multiple choice with many answers')
                                                    ->first();

                                                if ($candidateAnswer) {
                                                    $selectedOptions = explode(',', $candidateAnswer->selected_option);
                                                    return array_map('intval', $selectedOptions);
                                                }

                                                return [];
                                            });
                                            /* ->afterStateUpdated(fn (Get $get, Set $set) => $set('performance' . '-' . $index . '-' . $indice, Performance::find(MultipleChoice::find($question->question_ids[$indice])->comments[$get('multiplechoice_many_answers' . '-' . $index . '-' . $indice)[0]])->answer)) */;
                                        /* $schema[] = TiptapEditor::make('performance' . '-' . $index . '-' . $indice)
                                            ->label('Performance')
                                            ->hidden(function ($get) use ($index, $question) {
                                                if ($question->title === 'Practice stage' || $question->title === 'Marking stage') {
                                                    return !$get('visible_text_' . $index);
                                                } else {
                                                    return true;
                                                }
                                            }); */
                                    }

                                    if ($type == 'Open answer') {
                                        $schema[] =
                                            TextInput::make('question' . '-' . $index . '-' . $indice)
                                            ->readOnly()
                                            ->hiddenLabel()
                                            ->default(OpenAnswer::find($question->question_ids[$indice])->question);
                                        $schema[] =
                                            Textarea::make('open_answer' . '-' . $index . '-' . $indice)
                                            ->hiddenLabel()
                                            ->default(function (CandidateRecord $record) {
                                                $candidateAnswer = candidateAnswer::where('candidate_id', $record->candidate_id)
                                                    ->where('question_type', 'Open answer')
                                                    ->first();
                                                if ($candidateAnswer) {
                                                    return $candidateAnswer->answer_text;
                                                }

                                                return null;
                                            });
                                    }
                                }

                                $schema[] = ToggleButtons::make('button' . $index)
                                    ->live()
                                    ->hiddenLabel()
                                    ->afterStateUpdated(function (Set $set, string $state, CandidateRecord $record, Get $get) use ($index, $question) {
                                        if ($state === 'submit') {
                                            $set('visible_text_' . $index, true);

                                            $activity = Activity::where('section_id', $record->section_id)->where('type_of_training_id', $record->type_of_training_id)->first();
                                            $questions = $activity->questions;


                                            foreach ($question['question_type'] as $indice => $type) {

                                                if ($type == 'True or false') {
                                                    $answer = new candidateAnswer();
                                                    $answer->question_type = $type;
                                                    $answer->candidate_id = $record->candidate->id;
                                                    $answer->question_id = $question['question_ids'][$indice];
                                                    $answer->selected_option = $get('true_or_false' . '-' . $index . '-' . $indice);
                                                    $answer->save();
                                                }

                                                if ($type == 'True or false with justification') {
                                                    $answer = new candidateAnswer();
                                                    $answer->question_type = $type;
                                                    $answer->candidate_id = $record->candidate->id;
                                                    $answer->question_id = $question['question_ids'][$indice];
                                                    $answer->selected_option = $get('true_or_false_justify' . '-' . $index . '-' . $indice);
                                                    $answer->answer_text = $get('justify' . $index);
                                                    $answer->save();
                                                }

                                                if ($type == 'Multiple choice with one answer') {
                                                    $answer = new candidateAnswer();
                                                    $answer->question_type = $type;
                                                    $answer->candidate_id = $record->candidate->id;
                                                    $answer->question_id = $question['question_ids'][$indice];
                                                    $answer->selected_option = $get('multiplechoice_one_answer' . '-' . $index . '-' . $indice);
                                                    $answer->save();
                                                }

                                                if ($type == 'Multiple choice with many answers') {
                                                    //dd($data['multiplechoice_many_answers' . '-' . $index . '-' . $indice]);
                                                    $answer = new candidateAnswer();
                                                    $answer->question_type = $type;
                                                    $answer->candidate_id = $record->candidate->id;
                                                    $answer->question_id = $question['question_ids'][$indice];
                                                    $answer->selected_option = implode(',', $get('multiplechoice_many_answers' . '-' . $index . '-' . $indice));
                                                    $answer->save();
                                                }

                                                if ($type == 'Open answer') {
                                                    $answer = new candidateAnswer();
                                                    $answer->question_type = $type;
                                                    $answer->candidate_id = $record->candidate->id;
                                                    $answer->question_id = $question['question_ids'][$indice];
                                                    $answer->answer_text = $get('open_answer' . '-' . $index . '-' . $indice);
                                                    $answer->save();
                                                }
                                            }
                                        }
                                    })
                                    ->options(['submit' => 'Submit task']);

                                if ($question->text_after_answer) {
                                    $schema[] = TiptapEditor::make('textfinal' . $index)
                                        ->hiddenLabel()
                                        ->default($question->text_after_answer)
                                        ->disableBubbleMenus()
                                        ->disabled()
                                        ->live()
                                        ->hidden(function ($get) use ($index, $question) {
                                            if ($question->title === 'Practice stage' || $question->title === 'Marking stage') {
                                                return !$get('visible_text_' . $index);
                                            } else {
                                                return true;
                                            }
                                        });
                                }

                                $schema[] = TiptapEditor::make('final' . $index)
                                    ->hiddenLabel()
                                    ->default($activity->comment_at_the_end)
                                    ->disableBubbleMenus()
                                    ->disabled()
                                    ->live()
                                    ->hidden(function ($get) use ($index, $questions) {
                                        if ($index + 1 == count($questions)) {
                                            return !$get('visible_text_' . $index);
                                        } else {
                                            return true;
                                        }
                                    });

                                $steps[] = Step::make($question->title)
                                    ->schema($schema);
                            }
                        }
                        return [
                            Wizard::make($steps)
                                ->nextAction(
                                    function (WizardAction $action, CandidateRecord $record) {
                                        if ($record->can_access == 'cant') {
                                            redirect()->route('candidate.logout');
                                        }
                                        return $action->label('Next stage');
                                    },
                                )
                                ->previousAction(
                                    fn (WizardAction $action) => $action->label('Previous stage'),
                                )
                                ->submitAction(
                                    new HtmlString('<button>Close</button>')
                                )
                                ->columnSpanFull()
                        ];
                    })
                    ->modalSubmitAction(false)
                    ->modalCancelAction(false)
                    ->action(function (array $data, CandidateRecord $record) {
                        if (!$record->result) {
                            $activity = Activity::where('section_id', $record->section_id)->where('type_of_training_id', $record->type_of_training_id)->first();
                            $questions = $activity->questions;

                            $correct = true;
                            foreach ($questions as $index => $question) {
                                foreach ($question['question_type'] as $indice => $type) {
                                    if ($question->evaluation) {
                                        candidateAnswer::where('question_id', $question['question_ids'][$indice])->where('candidate_id', $question['question_ids'][$indice])->delete();
                                    }

                                    if ($type == 'True or false') {
                                        $answer = new candidateAnswer();
                                        $answer->question_type = $type;
                                        $answer->candidate_id = $record->candidate->id;
                                        $answer->question_id = $question['question_ids'][$indice];
                                        $answer->selected_option = $data['true_or_false' . '-' . $index . '-' . $indice];
                                        $answer->save();
                                        if (TrueFalse::find($answer->question_id)->true != $answer->selected_option && $question->evaluation) {
                                            $correct = false;
                                        }
                                    }

                                    if ($type == 'True or false with justification') {
                                        $answer = new candidateAnswer();
                                        $answer->question_type = $type;
                                        $answer->candidate_id = $record->candidate->id;
                                        $answer->question_id = $question['question_ids'][$indice];
                                        $answer->selected_option = $data['true_or_false_justify' . '-' . $index . '-' . $indice];
                                        $answer->answer_text = $data['justify' . $index];
                                        $answer->save();

                                        if (TrueFalse::find($answer->question_id)->true != $answer->selected_option && $question->evaluation) {
                                            $correct = false;
                                        }
                                    }

                                    if ($type == 'Multiple choice with one answer') {
                                        $answer = new candidateAnswer();
                                        $answer->question_type = $type;
                                        $answer->candidate_id = $record->candidate->id;
                                        $answer->question_id = $question['question_ids'][$indice];
                                        $answer->selected_option = $data['multiplechoice_one_answer' . '-' . $index . '-' . $indice];
                                        $answer->save();

                                        if (MultipleChoice::find($answer->question_id)->correct[$answer->selected_option] != 'false' && $question->evaluation) {
                                            $correct = false;
                                        }
                                    }

                                    if ($type == 'Multiple choice with many answers') {
                                        //dd($data['multiplechoice_many_answers' . '-' . $index . '-' . $indice]);
                                        $answer = new candidateAnswer();
                                        $answer->question_type = $type;
                                        $answer->candidate_id = $record->candidate->id;
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
                                        $answer = new candidateAnswer();
                                        $answer->question_type = $type;
                                        $answer->candidate_id = $record->candidate->id;
                                        $answer->question_id = $question['question_ids'][$indice];
                                        $answer->answer_text = $data['open_answer' . '-' . $index . '-' . $indice];
                                        $answer->save();
                                    }
                                }
                            }


                            $record->result = $correct ? 'Certified' : 'To be reviewed';

                            $record->save();
                        }
                    })
                    ->modalWidth(MaxWidth::SevenExtraLarge)


            ]);
    }
}
