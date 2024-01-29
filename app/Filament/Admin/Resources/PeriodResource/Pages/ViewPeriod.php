<?php

namespace App\Filament\Admin\Resources\PeriodResource\Pages;

use App\Filament\Admin\Resources\PeriodResource;
use Filament\Actions;
use Filament\Resources\Pages\ViewRecord;

class ViewPeriod extends ViewRecord
{
    protected static string $resource = PeriodResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\EditAction::make(),
        ];
    }
}
