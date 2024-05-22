<?php

namespace App\Filament\Admin\Resources\ActivityResource\Pages;

use App\Filament\Admin\Resources\ActivityResource;
use App\Models\MultipleChoice;
use App\Models\Question;
use App\Models\TrueFalse;
use Filament\Actions;
use Filament\Resources\Pages\CreateRecord;

class CreateActivity extends CreateRecord
{
    protected static string $resource = ActivityResource::class;

    protected function afterCreate(): void
    {
        $activityId = $this->record->id;
        $questions = $this->data['questions'];
        foreach ($questions as $question) {
            $newQuestion = new Question();
            $newQuestion->question = $question['question'];
            $newQuestion->description = $question['description'];
            $newQuestion->multimedia = reset($question['multimedia']);
            $newQuestion->question_type = $question['question_type'];
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
