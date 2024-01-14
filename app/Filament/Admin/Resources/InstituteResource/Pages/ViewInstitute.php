<?php

namespace App\Filament\Admin\Resources\InstituteResource\Pages;

use App\Filament\Admin\Resources\InstituteResource;
use Filament\Actions;
use Filament\Resources\Pages\ViewRecord;

class ViewInstitute extends ViewRecord
{
    protected static string $resource = InstituteResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\EditAction::make(),
        ];
    }
}
