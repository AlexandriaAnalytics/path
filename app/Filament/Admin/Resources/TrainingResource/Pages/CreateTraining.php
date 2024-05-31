<?php

namespace App\Filament\Admin\Resources\TrainingResource\Pages;

use App\Filament\Admin\Resources\TrainingResource;
use Filament\Actions;
use Filament\Resources\Pages\CreateRecord;
use Illuminate\Contracts\Support\Htmlable;

class CreateTraining extends CreateRecord
{
    protected static string $resource = TrainingResource::class;

    public function getTitle(): string | Htmlable
    {
        return __('Create training');
    }

    protected function getRedirectUrl(): string
    {
        return $this->getResource()::getUrl('index');
    }
}
