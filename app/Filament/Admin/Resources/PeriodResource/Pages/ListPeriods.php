<?php

namespace App\Filament\Admin\Resources\PeriodResource\Pages;

use App\Filament\Admin\Resources\PeriodResource;
use Filament\Actions;
use Filament\Resources\Pages\ListRecords;
use Filament\Support\Colors\Color;

class ListPeriods extends ListRecords
{
    protected static string $resource = PeriodResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\CreateAction::make()->color(Color::hex('#0086b3')),
        ];
    }
}
