<?php

namespace App\Filament\Admin\Resources\CustomLevelPriceResource\Pages;

use App\Filament\Admin\Resources\CustomLevelPriceResource;
use App\Models\Candidate;
use App\Models\Concept;
use App\Services\CandidateService;
use Filament\Actions;
use Filament\Resources\Pages\EditRecord;

class EditCustomLevelPrice extends EditRecord
{
    protected static string $resource = CustomLevelPriceResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\ViewAction::make(),
            Actions\DeleteAction::make(),
            Actions\ForceDeleteAction::make(),
            Actions\RestoreAction::make(),
        ];
    }

    protected function getRedirectUrl(): string
    {
        return $this->getResource()::getUrl('view', ['record' => $this->record]);
    }

    protected function afterSave(): void
    {
        $candidates = Candidate::all();
        foreach ($candidates as $candidate) {
            if ($candidate->paymentStatus == 'unpaid') {
                Concept::where('candidate_id', $candidate->id)->delete();
                CandidateService::createConcepts($candidate);
            }
        }
    }
}
