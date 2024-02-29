<?php

namespace App\Filament\Management\Resources\ExamResource\Pages;

use App\Filament\Management\Resources\ExamResource;
use Filament\Actions;
use Filament\Resources\Pages\CreateRecord;

class CreateExam extends CreateRecord
{
    protected static string $resource = ExamResource::class;

    protected function getRedirectUrl(): string
    {
        return $this->getResource()::getUrl('index');
    }
}
