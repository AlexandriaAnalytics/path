<?php

namespace App\Filament\Admin\Resources\InstituteResource\Pages;

use App\Filament\Admin\Resources\InstituteResource;
use Filament\Actions;
use Filament\Resources\Pages\ListRecords;

class ListInstitutes extends ListRecords
{
    protected static string $resource = InstituteResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\CreateAction::make(),
        ];
    }
}
