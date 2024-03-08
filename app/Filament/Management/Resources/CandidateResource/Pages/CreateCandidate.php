<?php

namespace App\Filament\Management\Resources\CandidateResource\Pages;

use App\Filament\Management\Resources\CandidateResource;
use App\Models\Period;
use App\Services\CandidateService;
use Filament\Notifications\Notification;
use Filament\Resources\Pages\CreateRecord;
use Illuminate\Contracts\Support\Htmlable;

class CreateCandidate extends CreateRecord
{
    protected static string $resource = CandidateResource::class;

    public function getTitle(): string | Htmlable
    {
        return __('Create candidate');
    }

    protected function beforeCreate(): void
    {

        if (Period::active()->doesntExist()) {
            Notification::make()
                ->warning()
                ->title('There are no active registration periods')
                ->body('Please contact your administrator.')
                ->persistent()
                ->send();

            $this->halt();
        }
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
