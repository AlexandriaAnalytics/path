<?php

namespace App\Filament\Admin\Resources\ExamPackageResource\Pages;

use App\Filament\Admin\Resources\ExamPackageResource;
use Filament\Actions;
use Filament\Resources\Pages\EditRecord;

class EditExamPackage extends EditRecord
{
    protected static string $resource = ExamPackageResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\DeleteAction::make(),
        ];
    }
}
