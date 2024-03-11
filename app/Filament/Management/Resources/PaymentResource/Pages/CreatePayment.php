<?php

namespace App\Filament\Management\Resources\PaymentResource\Pages;

use App\Filament\Management\Resources\PaymentResource;
use App\Models\Candidate;
use Filament\Actions;
use Filament\Resources\Pages\CreateRecord;

class CreatePayment extends CreateRecord
{
    protected static string $resource = PaymentResource::class;

    protected function getRedirectUrl(): string
    {
        return $this->getResource()::getUrl('index');
    }

    protected function mutateFormDataBeforeCreate(array $data): array
    {
        Candidate::find($data['candidate_id'])
            ->update([
                'status' => 'processing payment',
            ]);

        return $data;
    }
}
