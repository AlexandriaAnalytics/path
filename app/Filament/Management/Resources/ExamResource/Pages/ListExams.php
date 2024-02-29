<?php

namespace App\Filament\Management\Resources\ExamResource\Pages;

use App\Filament\Management\Resources\ExamResource;
use Filament\Actions;
use Filament\Resources\Pages\ListRecords;
use Illuminate\Database\Eloquent\Builder;
use App\Models\Exam;

class ListExams extends ListRecords
{
    protected static string $resource = ExamResource::class;

    protected function getHeaderActions(): array
    {
        return [];
    }

    protected function getTableQuery(): Builder
    {
        return Exam::query();
    }
}
