<?php

namespace App\Filament\Admin\Resources\ConceptPaymentResource\Pages;

use App\Filament\Admin\Resources\ConceptPaymentResource;
use Filament\Actions;
use Filament\Resources\Pages\EditRecord;

class EditConceptPayment extends EditRecord
{
    protected static string $resource = ConceptPaymentResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\DeleteAction::make(),
        ];
    }
}
