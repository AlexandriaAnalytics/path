<?php

namespace App\Filament\Admin\Resources\ExamResource\Pages;

use App\Filament\Admin\Resources\ExamResource;
use Filament\Actions;
use Filament\Resources\Pages\ViewRecord;

class ViewExam extends ViewRecord
{
    protected static string $resource = ExamResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\EditAction::make(),
        ];
    }
}
