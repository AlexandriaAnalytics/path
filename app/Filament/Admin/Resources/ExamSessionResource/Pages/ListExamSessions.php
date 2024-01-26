<?php

namespace App\Filament\Admin\Resources\ExamSessionResource\Pages;

use App\Filament\Admin\Resources\ExamSessionResource;
use Filament\Actions;
use Filament\Resources\Pages\ListRecords;

class ListExamSessions extends ListRecords
{
    protected static string $resource = ExamSessionResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\CreateAction::make(),
        ];
    }
}
