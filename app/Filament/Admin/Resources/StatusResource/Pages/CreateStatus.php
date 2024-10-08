<?php

namespace App\Filament\Admin\Resources\StatusResource\Pages;

use App\Filament\Admin\Resources\StatusResource;
use Filament\Actions;
use Filament\Resources\Pages\CreateRecord;

class CreateStatus extends CreateRecord
{
    protected static string $resource = StatusResource::class;
    protected static ?string $title = 'Create payment status';

    protected function getRedirectUrl(): string
    {
        return $this->getResource()::getUrl('index');
    }
}
