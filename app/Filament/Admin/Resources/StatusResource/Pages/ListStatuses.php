<?php

namespace App\Filament\Admin\Resources\StatusResource\Pages;

use App\Filament\Admin\Resources\StatusResource;
use Filament\Actions;
use Filament\Resources\Pages\ListRecords;
use Filament\Support\Colors\Color;

class ListStatuses extends ListRecords
{
    protected static string $resource = StatusResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\CreateAction::make()->label('New payment status')->color(Color::hex('#0086b3')),
        ];
    }
}
