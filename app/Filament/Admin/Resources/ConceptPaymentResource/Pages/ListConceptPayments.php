<?php

namespace App\Filament\Admin\Resources\ConceptPaymentResource\Pages;

use App\Filament\Admin\Resources\ConceptPaymentResource;
use Filament\Actions;
use Filament\Resources\Pages\ListRecords;

class ListConceptPayments extends ListRecords
{
    protected static string $resource = ConceptPaymentResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\CreateAction::make(),
        ];
    }
}
