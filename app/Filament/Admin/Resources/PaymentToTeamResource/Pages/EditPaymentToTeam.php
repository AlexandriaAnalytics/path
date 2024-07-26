<?php

namespace App\Filament\Admin\Resources\PaymentToTeamResource\Pages;

use App\Filament\Admin\Resources\PaymentToTeamResource;
use Filament\Actions;
use Filament\Resources\Pages\EditRecord;

class EditPaymentToTeam extends EditRecord
{
    protected static string $resource = PaymentToTeamResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\DeleteAction::make(),
        ];
    }
}
