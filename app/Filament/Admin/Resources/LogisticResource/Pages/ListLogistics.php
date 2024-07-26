<?php

namespace App\Filament\Admin\Resources\LogisticResource\Pages;

use App\Filament\Admin\Resources\LogisticResource;
use Filament\Actions;
use Filament\Resources\Pages\ListRecords;

class ListLogistics extends ListRecords
{
    protected static string $resource = LogisticResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\CreateAction::make(),
        ];
    }
}
