<?php

namespace App\Filament\Admin\Resources\StatusActivityResource\Pages;

use App\Filament\Admin\Resources\StatusActivityResource;
use Filament\Actions;
use Filament\Resources\Pages\ListRecords;

class ListStatusActivities extends ListRecords
{
    protected static string $resource = StatusActivityResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\CreateAction::make(),
        ];
    }
}
