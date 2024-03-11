<?php

namespace App\Filament\Management\Resources\PaymentResource\Pages;

use App\Filament\Management\Resources\PaymentResource;
use Filament\Actions;
use Filament\Facades\Filament;
use Filament\Forms\Components\Hidden;
use Filament\Resources\Pages\ListRecords;

class ListPayments extends ListRecords
{
    protected static string $resource = PaymentResource::class;

    protected function getHeaderActions(): array
    {

        return !Filament::getTenant()->can_view_registration_fee 
            ||
            Filament::getTenant()->can_view_registration_fee && Filament::getTenant()->candidates->count() > 30 ? //TODO: sacar este numero magico
            [
                Actions\CreateAction::make()->hidden(fn()=> !Filament::getTenant()->internal_payment_administration),
            ]:
            [];
    }
}
