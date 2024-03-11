<?php

namespace App\Filament\Management\Resources\PaymentResource\Pages;

use App\Filament\Management\Resources\PaymentResource;
use App\Models\Candidate;
use Filament\Actions;
use Filament\Resources\Pages\EditRecord;

class EditPayment extends EditRecord
{
    protected static string $resource = PaymentResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\DeleteAction::make()
                ->hidden(
                    fn (Candidate $candidate) =>
                    $candidate->status === 'paid'
                        || $candidate->status === 'paying'
                        || $candidate->status === 'processing payment'
                ),
        ];
    }
}
