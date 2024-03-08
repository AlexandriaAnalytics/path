<?php

namespace App\Filament\Admin\Resources\CandidateResource\Pages;

use App\Filament\Admin\Resources\CandidateResource;
use App\Services\CandidateService;
use Filament\Resources\Pages\CreateRecord;
use Illuminate\Contracts\Support\Htmlable;

class CreateCandidate extends CreateRecord
{
    protected static string $resource = CandidateResource::class;


    public function getTitle(): string | Htmlable
    {
        return __('Create candidate');
    }

    protected function afterCreate(): void
    {
        CandidateService::createConcepts($this->record);
    }

    protected function getRedirectUrl(): string
    {
        return $this->getResource()::getUrl('index');
    }
}
