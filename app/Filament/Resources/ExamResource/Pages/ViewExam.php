<?php

namespace App\Filament\Resources\ExamResource\Pages;

use App\Filament\Resources\ExamResource;
use Filament\Actions\Action;
use Filament\Resources\Pages\ViewRecord;
use Illuminate\Database\Eloquent\Builder;
use App\Models\Exam;
use App\Models\Student;
use Illuminate\Support\Facades\DB;

class ViewExam extends ViewRecord
{
    protected static string $resource = ExamResource::class;

    protected function getHeaderActions(): array
    {
        $students = Student::all();
        $examId = $this->record->getKey();
        return [
            Action::make('assign_students')
                ->modalContent(view('students')->with(['students' => $students, 'exam' => $examId]))
                ->modalHeading('Assign Students to Exam')
                ->icon('heroicon-o-user-group')
                ->modalSubmitAction(false)
                ->modalCancelAction(false)
        ];
    }
}
