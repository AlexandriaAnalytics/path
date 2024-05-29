<?php

namespace App\Filament\Admin\Resources\ActivityResource\Pages;

use App\Filament\Admin\Resources\ActivityResource;
use App\Models\MultipleChoice;
use App\Models\Question;
use App\Models\TrueFalse;
use Filament\Actions;
use Filament\Resources\Pages\CreateRecord;
use Illuminate\Contracts\Support\Htmlable;

class CreateActivity extends CreateRecord
{
    protected static string $resource = ActivityResource::class;

    public function getTitle(): string | Htmlable
    {
        return __('Create section');
    }

    protected function getRedirectUrl(): string
    {
        return $this->getResource()::getUrl('index');
    }

    protected function afterCreate(): void
    {
        $activityId = $this->record->id;
        $questions = $this->data['questions'];
        foreach ($questions as $question) {
            $newQuestion = new Question();
            $newQuestion->question = $question['question'];
            $newQuestion->title = $question['title'];
            $newQuestion->description = $question['description'];
            $newQuestion->url = $question['url'];
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
