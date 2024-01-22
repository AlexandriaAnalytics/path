<?php

namespace App\Filament\Admin\Resources\InstituteTypeResource\Pages;

use App\Filament\Admin\Resources\InstituteTypeResource;
use Filament\Actions;
use Filament\Resources\Pages\EditRecord;

class EditInstituteType extends EditRecord
{
    protected static string $resource = InstituteTypeResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\DeleteAction::make(),
        ];
    }
}
