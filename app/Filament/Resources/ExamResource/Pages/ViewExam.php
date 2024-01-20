<?php

namespace App\Filament\Resources\ExamResource\Pages;

use App\Filament\Resources\ExamResource;
use Filament\Actions\Action;
use Filament\Resources\Pages\ViewRecord;
use Illuminate\Database\Eloquent\Builder;
use App\Models\Exam;
use App\Models\Student;

class ViewExam extends ViewRecord
{
    protected static string $resource = ExamResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Action::make('Assign Students')
                ->modalHeading('Assign Students to Exam') // Optional modal heading
                ->icon('heroicon-o-user-group'), // Optional icon
        ];
    }
}
