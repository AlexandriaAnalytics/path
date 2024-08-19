<?php

namespace App\Filament\Management\Resources\CandidatePaymentResource\Pages;

use App\Filament\Management\Resources\CandidatePaymentResource;
use App\Models\Candidate;
use Filament\Actions;
use Filament\Resources\Pages\CreateRecord;

class CreatePayment extends CreateRecord
{
    protected static string $resource = CandidatePaymentResource::class;

    protected function getRedirectUrl(): string
    {
        return $this->getResource()::getUrl('index');
    }

    protected function mutateFormDataBeforeCreate(array $data): array
    {
        $data['status'] = 'processing payment';

        foreach ($data['candidate_id'] as $candidateId) {
            Candidate::find($candidateId + 1)->update(['status' => 'processing payment']);
        }

        $data['candidate_id'] = null;
        return $data;
    }
}
