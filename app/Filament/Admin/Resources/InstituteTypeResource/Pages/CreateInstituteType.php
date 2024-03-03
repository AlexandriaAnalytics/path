<?php

namespace App\Filament\Admin\Resources\InstituteTypeResource\Pages;

use App\Filament\Admin\Resources\InstituteTypeResource;
use Filament\Actions;
use Filament\Resources\Pages\CreateRecord;
use Illuminate\Contracts\Support\Htmlable;

class CreateInstituteType extends CreateRecord
{
    protected static string $resource = InstituteTypeResource::class;

    public function getTitle(): string | Htmlable
    {
        return __('Create membership');
    }

    protected function getRedirectUrl(): string
    {
        return $this->getResource()::getUrl('index');
    }
}
