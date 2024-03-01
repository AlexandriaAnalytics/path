<?php

namespace App\Filament\Admin\Resources\ChangeResource\Pages;

use App\Filament\Admin\Resources\ChangeResource;
use Filament\Actions;
use Filament\Resources\Pages\CreateRecord;

class CreateChange extends CreateRecord
{
    protected static string $resource = ChangeResource::class;

    protected function getRedirectUrl(): string
    {
        return $this->getResource()::getUrl('index');
    }
}
