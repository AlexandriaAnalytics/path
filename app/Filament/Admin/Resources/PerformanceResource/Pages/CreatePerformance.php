<?php

namespace App\Filament\Admin\Resources\PerformanceResource\Pages;

use App\Filament\Admin\Resources\PerformanceResource;
use Filament\Actions;
use Filament\Resources\Pages\CreateRecord;

class CreatePerformance extends CreateRecord
{
    protected static string $resource = PerformanceResource::class;

    protected function getRedirectUrl(): string
    {
        return $this->getResource()::getUrl('index');
    }
}
