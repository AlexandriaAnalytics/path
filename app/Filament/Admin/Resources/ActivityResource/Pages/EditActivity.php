<?php

namespace App\Filament\Admin\Resources\ActivityResource\Pages;

use App\Filament\Admin\Resources\ActivityResource;
use App\Models\Activity;
use App\Models\MultipleChoice;
use App\Models\Question;
use App\Models\TrueFalse;
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

    protected function mutateFormDataBeforeFill(array $data): array
    {
        $questions = Question::where('activity_id', $this->record->id)->get();

        $data['questions'] = $questions->map(function ($question) {
            $questionData = $question->toArray();

            if ($question->question_type == 'True or false' || $question->question_type == 'True or false with justification') {
                $trueFalse = TrueFalse::where('question_id', $question->id)->first();
                if ($trueFalse) {
                    $questionData['true'] = $trueFalse->true;
                    $questionData['comments_true'] = $trueFalse->comments[0] ?? null;
                    $questionData['comments_false'] = $trueFalse->comments[1] ?? null;
                }
            }

            if ($question->question_type == 'Multiple choice with one answer' || $question->question_type == 'Multiple choice with many answers') {
                $multipleChoice = MultipleChoice::where('question_id', $question->id)->first();
                if ($multipleChoice) {
                    $questionData['multiplechoice'] = collect($multipleChoice->answers)->map(function ($answer, $index) use ($multipleChoice) {
                        return [
                            'answer' => $answer,
                            'correct' => $multipleChoice->correct[$index] ?? false,
                            'comments' => $multipleChoice->comments[$index] ?? null,
                        ];
                    })->toArray();
                }
            }

            return $questionData;
        })->toArray();

        return $data;
    }

    protected function afterSave(): void
    {
        $activityId = $this->record->id;
        $questions = $this->data['questions'];

        // Eliminar las preguntas existentes antes de guardar las nuevas.
        Question::where('activity_id', $activityId)->delete();

        foreach ($questions as $question) {
            $newQuestion = new Question();
            $newQuestion->question = $question['question'];
            $newQuestion->title = $question['title'];
            $newQuestion->description = $question['description'];
            $newQuestion->multimedia = reset($question['multimedia']);
            $newQuestion->question_type = $question['question_type'];
            $newQuestion->evaluation = $question['evaluation'];
            $newQuestion->activity_id = $activityId;
            $newQuestion->save();

            if ($newQuestion->question_type == 'True or false' || $newQuestion->question_type == 'True or false with justification') {
                $trueFalse = new TrueFalse();
                $trueFalse->question_id = $newQuestion->id;
                $trueFalse->true = $question['true'];
                $comments = $question['comments_true'] ? array($question['comments_true'], $question['comments_false']) : null;
                $trueFalse->comments = $comments;
                $trueFalse->save();
            }

            if ($newQuestion->question_type == 'Multiple choice with one answer' || $newQuestion->question_type == 'Multiple choice with many answers') {
                $newMultiplechoice = new MultipleChoice();
                $answersArray = [];
                $correctsArray = [];
                $commentsArray = [];
                foreach ($question['multiplechoice'] as $multiplechoice) {
                    $answersArray[] = $multiplechoice['answer'];
                    $correctsArray[] = $multiplechoice['correct'];
                    $commentsArray[] = $multiplechoice['comments'];
                }
                $newMultiplechoice->question_id = $newQuestion->id;
                $newMultiplechoice->answers = $answersArray;
                $newMultiplechoice->correct = $correctsArray;
                $newMultiplechoice->comments = $commentsArray;
                $newMultiplechoice->save();
            }
        }
    }
}
