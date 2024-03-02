<?php

namespace App\Filament\Admin\Resources\InstituteTypeResource\Pages;

use App\Filament\Admin\Resources\InstituteTypeResource;
use Filament\Actions;
use Filament\Resources\Pages\ListRecords;

class ListInstituteTypes extends ListRecords
{
    protected static string $resource = InstituteTypeResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\CreateAction::make()
                ->label('New membership'),
        ];
    }
}
