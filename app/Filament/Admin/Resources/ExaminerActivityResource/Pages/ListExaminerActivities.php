<?php

namespace App\Filament\Admin\Resources\ExaminerActivityResource\Pages;

use App\Filament\Admin\Resources\ExaminerActivityResource;
use Filament\Actions;
use Filament\Resources\Pages\ListRecords;

class ListExaminerActivities extends ListRecords
{
    protected static string $resource = ExaminerActivityResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\CreateAction::make()
                ->label('New examiner activity'),
        ];
    }
}
