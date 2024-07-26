<?php

namespace App\Filament\Admin\Resources\ExamPackageResource\Pages;

use App\Filament\Admin\Resources\ExamPackageResource;
use Filament\Actions;
use Filament\Resources\Pages\ListRecords;

class ListExamPackages extends ListRecords
{
    protected static string $resource = ExamPackageResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\CreateAction::make(),
        ];
    }
}
