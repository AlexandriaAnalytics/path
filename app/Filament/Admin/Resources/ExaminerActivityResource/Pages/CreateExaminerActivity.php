<?php

namespace App\Filament\Admin\Resources\ExaminerActivityResource\Pages;

use App\Filament\Admin\Resources\ExaminerActivityResource;
use App\Models\ExaminerQuestion;
use Filament\Actions;
use Filament\Resources\Pages\CreateRecord;
use Illuminate\Contracts\Support\Htmlable;

class CreateExaminerActivity extends CreateRecord
{
    protected static string $resource = ExaminerActivityResource::class;

    public function getTitle(): string | Htmlable
    {
        return __('Create examiner activity');
    }

    protected function getRedirectUrl(): string
    {
        return $this->getResource()::getUrl('index');
    }

    protected function mutateFormDataBeforeCreate(array $data): array
    {
        $questions = [];
        foreach ($data['questions'] as $question) {
            $examinerQuestion = new ExaminerQuestion();
            $examinerQuestion->question = $question['question'];
            $examinerQuestion->description = $question['description'] ? $question['description'] : null;
            $examinerQuestion->open_or_close = $question['open_or_close'];
            $aswers = [];
            $performances = [];
            foreach ($question['aswers'] as $answer) {
                $aswers[] = $answer['aswer'];
                $performances[] = $answer['performance'];
            }
            $examinerQuestion->aswers = $aswers;
            $examinerQuestion->performance = $performances;
            $examinerQuestion->multimedia = $question['multimedia'];
            $examinerQuestion->save();
            $questions[] = $examinerQuestion->id;
        }
        $data['questions'] = $questions;
        return $data;
    }
}
