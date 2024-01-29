<?php

namespace App\Filament\Admin\Resources\PeriodResource\Pages;

use App\Filament\Admin\Resources\PeriodResource;
use Filament\Actions;
use Filament\Resources\Pages\ListRecords;

class ListPeriods extends ListRecords
{
    protected static string $resource = PeriodResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\CreateAction::make(),
        ];
    }
}
