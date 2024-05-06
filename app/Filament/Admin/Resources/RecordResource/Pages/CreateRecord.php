<?php

namespace App\Filament\Admin\Resources\RecordResource\Pages;

use App\Filament\Admin\Resources\RecordResource;
use Filament\Actions;
use Filament\Resources\Pages\CreateRecord as BaseCreateRecord;
use Illuminate\Contracts\Support\Htmlable;

class CreateRecord extends BaseCreateRecord
{
    protected static string $resource = RecordResource::class;

    public function getTitle(): string | Htmlable
    {
        return __('Create record');
    }

    protected function getRedirectUrl(): string
    {
        return $this->getResource()::getUrl('index');
    }
}
