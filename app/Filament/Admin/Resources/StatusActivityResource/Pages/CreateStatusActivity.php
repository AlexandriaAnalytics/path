<?php

namespace App\Filament\Admin\Resources\StatusActivityResource\Pages;

use App\Filament\Admin\Resources\StatusActivityResource;
use Filament\Actions;
use Filament\Resources\Pages\CreateRecord;
use Illuminate\Contracts\Support\Htmlable;

class CreateStatusActivity extends CreateRecord
{
    protected static string $resource = StatusActivityResource::class;

    public function getTitle(): string | Htmlable
    {
        return __('Create status');
    }

    protected function getRedirectUrl(): string
    {
        return $this->getResource()::getUrl('index');
    }
}
