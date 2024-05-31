<?php

namespace App\Filament\Trainee\Resources\FilesResource\Pages;

use App\Filament\Trainee\Resources\FilesResource;
use Filament\Actions;
use Filament\Resources\Pages\EditRecord;

class EditFiles extends EditRecord
{
    protected static string $resource = FilesResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\DeleteAction::make(),
        ];
    }
}
