<?php

namespace App\Filament\Resources\CandidateResource\Pages;

use App\Filament\Resources\CandidateResource;
use App\Models\Period;
use Filament\Actions;
use Filament\Notifications\Notification;
use Filament\Resources\Pages\CreateRecord;

class CreateCandidate extends CreateRecord
{
    protected static string $resource = CandidateResource::class;

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
}
