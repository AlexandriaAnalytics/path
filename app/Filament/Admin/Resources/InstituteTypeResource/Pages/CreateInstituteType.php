<?php

namespace App\Filament\Admin\Resources\InstituteTypeResource\Pages;

use App\Filament\Admin\Resources\InstituteTypeResource;
use Filament\Actions;
use Filament\Resources\Pages\CreateRecord;

class CreateInstituteType extends CreateRecord
{
    protected static string $resource = InstituteTypeResource::class;

    protected function getRedirectUrl(): string
    {
        return $this->getResource()::getUrl('index');
    }
}
