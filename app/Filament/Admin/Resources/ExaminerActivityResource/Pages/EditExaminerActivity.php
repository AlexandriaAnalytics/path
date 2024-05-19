<?php

namespace App\Filament\Admin\Resources\ExaminerActivityResource\Pages;

use App\Filament\Admin\Resources\ExaminerActivityResource;
use App\Models\ExaminerActivity;
use App\Models\ExaminerQuestion;
use Filament\Actions;
use Filament\Resources\Pages\EditRecord;

class EditExaminerActivity extends EditRecord
{
    protected static string $resource = ExaminerActivityResource::class;

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
        $data['questions'] = ExaminerActivity::find($this->record->id)->preguntas();
        foreach ($data['questions'] as $question) {
            $answersArray = [];
            foreach ($question['aswers'] as $index => $answer) {
                $answersArray[] = ['aswer' => $answer, 'performance' => $question['performance'][$index]];
                $question['aswers'] = $answersArray;
            }
        }

        return $data;
    }

    protected function mutateFormDataBeforeSave(array $data): array
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
