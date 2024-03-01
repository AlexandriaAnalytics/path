<?php

namespace App\Filament\Management\Resources\ExamResource\Pages;

use App\Filament\Management\Resources\ExamResource;
use Filament\Resources\Pages\ViewRecord;

class ViewExam extends ViewRecord
{
    protected static string $resource = ExamResource::class;

    protected function getHeaderActions(): array
    {
        return [
            //
        ];
    }
}
