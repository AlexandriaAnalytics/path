<?php

namespace App\Filament\Candidate\Pages;

use App\Models\Activity;
use App\Models\Candidate;
use App\Models\CandidateActivity;
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
use Mpdf\Tag\TextArea as TagTextArea;

class ExamSessions extends Page implements HasForms, HasTable
{

    use InteractsWithTable;
    use InteractsWithForms;

    public $candidate, $record;

    public function __construct()
    {
        $this->candidate = Candidate::find(session('candidate')->id);
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
                        $exam = CandidateExam::where('candidate_id', $record->candidate_id)->first();
                        if ($exam) {
                            $scheduledDate = $exam->exam->scheduled_date->modify('+3 hours');
                            $duration = $exam->exam->duration;
                            return $currentDate >= $scheduledDate && $currentDate <= $scheduledDate->modify('+' . $duration . ' minutes');
                        }
                        return false;
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
                    /* ->visible(function (CandidateRecord $record) {
                        $this->record = $record;
                        $currentDate = date('Y-m-d H:i:s');
                        $exam = CandidateExam::where('candidate_id', $record->candidate_id)->first();
                        if ($exam) {
                            $scheduledDate = CandidateExam::where('candidate_id', $record->candidate_id)->first()->exam->scheduled_date->modify('+3 hours');
                            $duration = CandidateExam::where('candidate_id', $record->candidate_id)->first()->exam->duration;
                            return $currentDate >= $scheduledDate && $currentDate <= $scheduledDate->modify('+' . $duration . ' minutes') && $record->can_access == 'can';
                        }
                        return false;
                    }) */
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
                        $activity = CandidateActivity::where('section_id', $record->section_id)->whereNull('deleted_at')->first();
                        $steps = [];
                        if ($activity) {
                            $stages = $activity->stages;
                            foreach ($stages as $stage) {
                                $schema = [];
                                $content = $stage['content'];
                                foreach ($content as $index => $activity) {

                                    if ($activity['type'] == 'url') {
                                        $schema[] = ViewField::make('field' . $index)
                                            ->hiddenLabel()
                                            ->view('filament.iframes')
                                            ->viewData(['url' => $activity['data']['content']]);
                                    }
                                    if ($activity['type'] == 'multimedia') {
                                        $multimediaUrl = asset('storage/' . $activity['data']['content']);
                                        $schema[] = ViewField::make('field')
                                            ->hiddenLabel()
                                            ->view('filament.iframes')
                                            ->viewData(['url' => $multimediaUrl]);
                                    }

                                    if ($activity['type'] == 'text') {
                                        $schema[] = TiptapEditor::make('text' . $index)
                                            ->hiddenLabel()
                                            ->default($activity['data']['content'])
                                            ->disableBubbleMenus()
                                            ->disabled()
                                            ->live();
                                    }

                                    $indice = 0;
                                    if ($activity['type'] == 'questions') {
                                        foreach ($activity['data']['content'] as $preg) {
                                            $type = $preg['question_type'];
                                            $question = $preg['question'];
                                            if ($type == 'True or false') {
                                                $indice++;
                                                $schema[] =
                                                    TextInput::make('question' . '-' . $index . '-' . $indice)
                                                    ->readOnly()
                                                    ->hiddenLabel()
                                                    ->default($question);
                                                $schema[] =
                                                    Radio::make('answer' . '-' . $index . '-' . $indice)
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
                                                $indice++;
                                                $schema[] =
                                                    TextInput::make('question' . '-' . $index . '-' . $indice)
                                                    ->readOnly()
                                                    ->hiddenLabel()
                                                    ->default($question);
                                                $schema[] = Radio::make('answer' . '-' . $index . '-' . $indice)
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
                                                $schema[] = TextInput::make('justify' . $index . '-' . $indice)
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
                                                $indice++;
                                                $schema[] =
                                                    TextInput::make('question' . '-' . $index . '-' . $indice)
                                                    ->readOnly()
                                                    ->hiddenLabel()
                                                    ->default($question);
                                                $schema[] =
                                                    Radio::make('answer' . '-' . $index . '-' . $indice)
                                                    ->hiddenLabel()
                                                    ->options(function () use ($activity) {
                                                        $answers = [];
                                                        foreach ($activity['data']['content'][0]['multiplechoice'] as $multiplechoice) {
                                                            $answers[] = $multiplechoice['answer'];
                                                        }
                                                        return $answers;
                                                    })
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
                                                $indice++;
                                                $schema[] =
                                                    TextInput::make('question' . '-' . $index . '-' . $indice)
                                                    ->readOnly()
                                                    ->hiddenLabel()
                                                    ->default($question);
                                                $schema[] =
                                                    CheckboxList::make('answer' . '-' . $index . '-' . $indice)
                                                    ->hiddenLabel()
                                                    ->live()
                                                    ->reactive()
                                                    ->options([])
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
                                            }

                                            if ($type == 'Open answer') {
                                                $indice++;
                                                $schema[] =
                                                    TextInput::make('question' . '-' . $index . '-' . $indice)
                                                    ->readOnly()
                                                    ->hiddenLabel()
                                                    ->default($question);
                                                $schema[] =
                                                    TextArea::make('answer' . '-' . $index . '-' . $indice)
                                                    ->hiddenLabel()
                                                    ->extraAttributes(['onpaste' => 'return false'])
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
                                            $schema[] = ToggleButtons::make('button' . $index)
                                                ->live()
                                                ->hiddenLabel()
                                                ->afterStateUpdated(function (Set $set, string $state, CandidateRecord $record, Get $get) use ($index, $question, $indice, $activity) {
                                                    $type = $activity['data']['content'][0]['question_type'];
                                                    if ($state === 'submit') {
                                                        //$set('visible_text_' . $index, true);

                                                        if ($type == 'True or false') {
                                                            $answer = new candidateAnswer();
                                                            $answer->question_type = $type;
                                                            $answer->candidate_id = $record->candidate->id;
                                                            $answer->question = $activity['data']['content'][0];
                                                            $answer->selected_option = $get('answer' . '-' . $index . '-' . $indice);
                                                            $answer->save();
                                                        }

                                                        if ($type == 'True or false with justification') {
                                                            $answer = new candidateAnswer();
                                                            $answer->question_type = $type;
                                                            $answer->candidate_id = $record->candidate->id;
                                                            $answer->question = $activity['data']['content'][0];
                                                            $answer->selected_option = $get('answer' . '-' . $index . '-' . $indice);
                                                            $answer->answer_text = $get('justify' . $index . '-' . $indice);
                                                            $answer->save();
                                                        }

                                                        if ($type == 'Multiple choice with one answer') {
                                                            $answer = new candidateAnswer();
                                                            $answer->question_type = $type;
                                                            $answer->candidate_id = $record->candidate->id;
                                                            $answer->question = $activity['data']['content'][0];
                                                            $answer->selected_option = $get('answer' . '-' . $index . '-' . $indice);
                                                            $answer->save();
                                                        }

                                                        if ($type == 'Multiple choice with many answers') {
                                                            //dd($data['multiplechoice_many_answers' . '-' . $index . '-' . $indice]);
                                                            $answer = new candidateAnswer();
                                                            $answer->question_type = $type;
                                                            $answer->candidate_id = $record->candidate->id;
                                                            $answer->question = $activity['data']['content'][0];
                                                            $answer->selected_option = implode(',', $get('answer' . '-' . $index . '-' . $indice));
                                                            $answer->save();
                                                        }

                                                        if ($type == 'Open answer') {
                                                            $answer = new candidateAnswer();
                                                            $answer->question_type = $type;
                                                            $answer->candidate_id = $record->candidate->id;
                                                            $answer->question = $activity['data']['content'][0];
                                                            $answer->answer_text = $get('answer' . '-' . $index . '-' . $indice);
                                                            $answer->save();
                                                        }
                                                    }
                                                })
                                                ->colors(['submit' => 'info'])
                                                ->options(['submit' => 'Submit task']);
                                        }
                                    }

                                    $title = '';
                                    foreach ($content as $item) {
                                        if ($item['type'] === 'title') {
                                            $title = $item['data']['content'];
                                            break;
                                        }
                                    }
                                }

                                $schema[] = ToggleButtons::make('button-help' . $index)
                                    ->live()
                                    ->hiddenLabel()
                                    ->afterStateUpdated(function (Set $set, string $state, CandidateRecord $record) {
                                        if ($state === 'submit') {
                                            $record->help = 'Pending';
                                            $record->save();
                                        }
                                    })
                                    ->options(['submit' => 'Ask for help'])
                                    ->icons(['submit' => 'heroicon-o-hand-raised'])
                                    ->default(function (CandidateRecord $record) {
                                        return $record->help == 'Pending' ? 'submit' : '';
                                    })
                                    ->extraAttributes(['id' => 'modal'])
                                    ->colors(['submit' => 'warning']);
                                $steps[] = Step::make($title)
                                    ->schema($schema);
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
                                        new HtmlString('<button style="background-color: #579ACB; color: #fff; padding: 12% 26%; border-radius: 5px; margin-right: 20px;">Close</button>')
                                    )
                                    ->columnSpanFull()
                            ];
                        }
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
                                        $answer->answer_text = $data['justify' . $index . '-' . $indice];
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
