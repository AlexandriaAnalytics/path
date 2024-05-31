<?php

namespace App\Filament\Admin\Resources\ActivityResource\Pages;

use App\Filament\Admin\Resources\ActivityResource;
use App\Models\MultipleChoice;
use App\Models\OpenAnswer;
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
        $sections = $this->data['sections'];
        foreach ($sections as $question) {
            $newQuestion = new Question();
            $newQuestion->title = $question['title'];
            $newQuestion->description = $question['description'];
            $newQuestion->url = $question['url'];
            $newQuestion->multimedia = reset($question['multimedia']);
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
                    foreach ($question['multiplechoice'] as $multiplechoice) {
                        $answersArray[] = $multiplechoice['answer'];
                        $correctsArray[] = $multiplechoice['correct'];
                        $commentsArray[] = $multiplechoice['comments'];
                    }
                    $newMultiplechoice->answers = $answersArray;
                    $newMultiplechoice->correct = $correctsArray;
                    $newMultiplechoice->comments = $commentsArray;
                    $newMultiplechoice->question = $question['question'];
                    $newMultiplechoice->save();
                    $question_ids[] = $newMultiplechoice->id;
                }

                if($question['question_type'] == 'Open answer') {
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
