<?php

namespace App\Filament\Admin\Resources\ActivityResource\Pages;

use App\Filament\Admin\Resources\ActivityResource;
use App\Models\Activity;
use App\Models\CandidateActivity;
use App\Models\MultipleChoice;
use App\Models\OpenAnswer;
use App\Models\Question;
use App\Models\TrueFalse;
use App\Models\TypeOfTraining;
use Filament\Actions;
use Filament\Resources\Pages\EditRecord;

class EditActivity extends EditRecord
{
    protected static string $resource = ActivityResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\DeleteAction::make(),
            Actions\ForceDeleteAction::make(),
            Actions\RestoreAction::make(),
        ];
    }

    protected function mutateFormDataBeforeSave(array $data): array
    {
        $activity = CandidateActivity::where('section_id', $data['section_id'])->first();
        $activity->stages = $data['stages'];
        $activity->comment_at_the_end = $data['comment_at_the_end'];
        $activity->section_id = $data['section_id'];
        $activity->save();
        return $data;
    }

    protected function mutateFormDataBeforeFill(array $data): array
    {
        if (TypeOfTraining::find($data['type_of_training_id'])->name == 'Candidate') {
            $data['stages'] = CandidateActivity::where('section_id', $data['section_id'])->first()->stages;
        } else {
            $questions = Question::where('activity_id', $this->record->id)->get();

            $data['sections'] = $questions->map(function ($question) {
                $questionData = $question->toArray();
                $questionData['questions'] = [];
                foreach ($question['question_type'] as $index => $type) {
                    $questionEdit = [];
                    if ($type == 'True or false' || $type == 'True or false with justification') {
                        $trueFalse = TrueFalse::find($question['question_ids'][$index]);
                        if ($trueFalse) {
                            $questionEdit['question_type'] = $type;
                            $questionEdit['question'] = $trueFalse->question;
                            $questionEdit['true'] = $trueFalse->true;
                            $questionEdit['comments_true'] = $trueFalse->comments[0] ?? null;
                            $questionEdit['comments_false'] = $trueFalse->comments[1] ?? null;
                        }
                    }

                    if ($type == 'Multiple choice with one answer' || $type == 'Multiple choice with many answers') {
                        $multipleChoice = MultipleChoice::find($question['question_ids'][$index]);
                        if ($multipleChoice) {
                            $questionEdit['question_type'] = $type;
                            $questionEdit['question'] = $multipleChoice->question;
                            $questionEdit['multiplechoice'] = collect($multipleChoice->answers)->map(function ($answer, $index) use ($multipleChoice) {
                                return [
                                    'answer' => $answer,
                                    'correct' => $multipleChoice->correct[$index] ?? false,
                                    'comments' => $multipleChoice->comments[$index] ?? null,
                                    'correct_in_pdf' => $multipleChoice->correct_in_pdf[$index] ?? false
                                ];
                            })->toArray();
                        }
                    }

                    if ($type == 'Open answer') {
                        $openAnswer = OpenAnswer::find($question['question_ids'][$index]);
                        $questionEdit['question'] = $openAnswer->question;
                    }

                    $questionData['questions'][] = $questionEdit;
                }
                return $questionData;
            })->toArray();
        }
        return $data;
    }

    protected function afterSave(): void
    {
        $activityId = $this->record->id;
        $sections = $this->data['sections'];
        Question::where('activity_id', $activityId)->delete();
        foreach ($sections as $question) {
            $newQuestion = new Question();
            $newQuestion->title = $question['title'];
            $newQuestion->description = $question['description'];
            $newQuestion->url = $question['url'];
            $newQuestion->multimedia = reset($question['multimedia']);
            $newQuestion->text = $question['text'] ? json_encode($question['text']) : null;
            $newQuestion->text_after_answer = $question['text_after_answer'] ? json_encode($question['text_after_answer']) : null;
            $newQuestion->evaluation = $question['evaluation'];
            $newQuestion->activity_id = $activityId;

            $question_type = [];
            $question_ids = [];

            foreach ($question['questions'] as $question) {
                $question_type[] = $question['question_type'];
                if ($question['question_type'] == 'True or false' || $question['question_type'] == 'True or false with justification') {
                    $trueFalse = new TrueFalse();
                    $trueFalse->true = $question['true'];
                    $comments = $question['comments_true'] ? array($question['comments_true'], $question['comments_false']) : null;
                    $trueFalse->comments = $comments;
                    $trueFalse->question = $question['question'];
                    $trueFalse->save();
                    $question_ids[] = $trueFalse->id;
                }

                if ($question['question_type'] == 'Multiple choice with one answer' || $question['question_type'] == 'Multiple choice with many answers') {
                    $newMultiplechoice = new MultipleChoice();
                    $answersArray = [];
                    $correctsArray = [];
                    $commentsArray = [];
                    $correctsInPdfArray = [];
                    foreach ($question['multiplechoice'] as $multiplechoice) {
                        $answersArray[] = $multiplechoice['answer'];
                        $correctsArray[] = $multiplechoice['correct'];
                        $commentsArray[] = $multiplechoice['comments'];
                        $correctsInPdfArray[] = $multiplechoice['correct_in_pdf'] ?? null;
                    }
                    $newMultiplechoice->answers = $answersArray;
                    $newMultiplechoice->correct = $correctsArray;
                    $newMultiplechoice->comments = $commentsArray;
                    $newMultiplechoice->correct_in_pdf = $correctsInPdfArray;
                    $newMultiplechoice->question = $question['question'];
                    $newMultiplechoice->save();
                    $question_ids[] = $newMultiplechoice->id;
                }

                if ($question['question_type'] == 'Open answer') {
                    $openAnswer = new OpenAnswer();
                    $openAnswer->question = $question['question'];
                    $openAnswer->save();
                    $question_ids[] = $openAnswer->id;
                }
            }
            $newQuestion->question_type = $question_type;
            $newQuestion->question_ids = $question_ids;
            $newQuestion->save();
        }
    }
}
