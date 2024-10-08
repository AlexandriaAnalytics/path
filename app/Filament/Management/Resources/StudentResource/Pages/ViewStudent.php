<?php

namespace App\Filament\Management\Resources\StudentResource\Pages;

use App\Filament\Management\Resources\StudentResource;
use App\Models\Candidate;
use App\Models\Student;
use Filament\Actions;
use Filament\Resources\Pages\ViewRecord;

class ViewStudent extends ViewRecord
{
    protected static string $resource = StudentResource::class;

    protected function getActions(): array
    {
        return [
            Actions\EditAction::make()->visible(function (Student $record) {
                return Candidate::query()
                    ->where('student_id', $record->id)
                    ->where(function ($query) {
                        $query->where('status', 'paid')
                            ->orWhere('status', 'paying');
                    })
                    ->doesntExist();
            }),
        ];
    }
}
