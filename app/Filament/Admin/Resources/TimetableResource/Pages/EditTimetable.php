<?php

namespace App\Filament\Admin\Resources\TimetableResource\Pages;

use App\Filament\Admin\Resources\TimetableResource;
use Filament\Actions;
use Filament\Resources\Pages\EditRecord;

class EditTimetable extends EditRecord
{
    protected static string $resource = TimetableResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\DeleteAction::make(),
        ];
    }
}
