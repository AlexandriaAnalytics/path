<?php

namespace App\Filament\Admin\Resources\TypeOfTrainingResource\Pages;

use App\Filament\Admin\Resources\TypeOfTrainingResource;
use Filament\Actions;
use Filament\Resources\Pages\CreateRecord;
use Illuminate\Contracts\Support\Htmlable;

class CreateTypeOfTraining extends CreateRecord
{
    protected static string $resource = TypeOfTrainingResource::class;

    public function getTitle(): string | Htmlable
    {
        return __('Create type of training');
    }

    protected function getRedirectUrl(): string
    {
        return $this->getResource()::getUrl('index');
    }
}
