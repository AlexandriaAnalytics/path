<?php

namespace App\Filament\Admin\Resources\ExaminerActivityResource\Pages;

use App\Filament\Admin\Resources\ExaminerActivityResource;
use App\Models\ExaminerActivity;
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
}
