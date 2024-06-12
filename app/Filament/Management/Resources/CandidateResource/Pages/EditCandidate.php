<?php

namespace App\Filament\Management\Resources\CandidateResource\Pages;

use App\Filament\Management\Resources\CandidateResource;
use App\Models\Candidate;
use App\Models\Concept;
use App\Services\CandidateService;
use Filament\Actions;
use Filament\Resources\Pages\EditRecord;

class EditCandidate extends EditRecord
{
    protected static string $resource = CandidateResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\DeleteAction::make()
                ->hidden(
                    fn (Candidate $candidate) =>
                    $candidate->paymentStatus !== 'unpaid'
                ),
        ];
    }

    protected function afterSave(): void
    {
        Concept::where('candidate_id', $this->record->id)->delete();
        CandidateService::createConcepts($this->record);
    }
}
