<?php

namespace App\Filament\Admin\Resources\LevelResource\Pages;

use App\Filament\Admin\Resources\LevelResource;
use Filament\Actions;
use Filament\Resources\Pages\CreateRecord;
use Illuminate\Contracts\Support\Htmlable;

class CreateLevel extends CreateRecord
{
    protected static string $resource = LevelResource::class;

    public function getTitle(): string | Htmlable
    {
        return __('Create exam');
    }

    protected function getRedirectUrl(): string
    {
        return $this->getResource()::getUrl('index');
    }
}
