<?php

namespace App\Filament\Admin\Resources\ScheduleSettingsResource\Pages;

use App\Filament\Admin\Resources\ScheduleSettingsResource;
use Filament\Actions;
use Filament\Resources\Pages\EditRecord;

class EditScheduleSettings extends EditRecord
{
    protected static string $resource = ScheduleSettingsResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\DeleteAction::make(),
        ];
    }
}
