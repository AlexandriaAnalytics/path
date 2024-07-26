<?php

namespace App\Filament\Admin\Resources\PaymentToTeamResource\Pages;

use App\Filament\Admin\Resources\PaymentToTeamResource;
use Filament\Actions;
use Filament\Resources\Pages\ListRecords;

class ListPaymentToTeams extends ListRecords
{
    protected static string $resource = PaymentToTeamResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\CreateAction::make(),
        ];
    }
}
