<?php

namespace App\Filament\Admin\Resources\UserResource\Pages;

use App\Filament\Admin\Resources\UserResource;
use Filament\Actions;
use Filament\Resources\Pages\CreateRecord;
use Illuminate\Contracts\Support\Htmlable;

class CreateUser extends CreateRecord
{
    protected static string $resource = UserResource::class;

    public function getTitle(): string | Htmlable
    {
        return __('Create user');
    }

    protected function getRedirectUrl(): string
    {
        return $this->getResource()::getUrl('view', [$this->record]);
    }
}
