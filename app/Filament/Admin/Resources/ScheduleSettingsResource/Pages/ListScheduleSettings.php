<?php

namespace App\Filament\Admin\Resources\ScheduleSettingsResource\Pages;

use App\Filament\Admin\Resources\ScheduleSettingsResource;
use Filament\Actions;
use Filament\Resources\Pages\ListRecords;

class ListScheduleSettings extends ListRecords
{
    protected static string $resource = ScheduleSettingsResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\CreateAction::make()->label('New schedule setting'),
        ];
    }
}
