<?php

namespace App\Filament\Admin\Resources\TrainingResource\Pages;

use App\Filament\Admin\Resources\TrainingResource;
use Filament\Actions;
use Filament\Resources\Pages\EditRecord;

class EditTraining extends EditRecord
{
    protected static string $resource = TrainingResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\DeleteAction::make(),
        ];
    }
}
