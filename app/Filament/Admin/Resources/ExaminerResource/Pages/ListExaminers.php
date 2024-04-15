<?php

namespace App\Filament\Admin\Resources\ExaminerResource\Pages;

use App\Filament\Admin\Resources\ExaminerResource;
use Filament\Actions;
use Filament\Resources\Pages\ListRecords;

class ListExaminers extends ListRecords
{
    protected static string $resource = ExaminerResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\CreateAction::make(),
        ];
    }
}
