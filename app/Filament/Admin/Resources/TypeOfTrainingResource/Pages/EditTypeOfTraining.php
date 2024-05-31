<?php

namespace App\Filament\Admin\Resources\TypeOfTrainingResource\Pages;

use App\Filament\Admin\Resources\TypeOfTrainingResource;
use Filament\Actions;
use Filament\Resources\Pages\EditRecord;

class EditTypeOfTraining extends EditRecord
{
    protected static string $resource = TypeOfTrainingResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\DeleteAction::make(),
        ];
    }
}
