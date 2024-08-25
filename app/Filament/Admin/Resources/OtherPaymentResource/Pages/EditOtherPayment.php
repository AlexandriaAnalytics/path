<?php

namespace App\Filament\Admin\Resources\OtherPaymentResource\Pages;

use App\Filament\Admin\Resources\OtherPaymentResource;
use Filament\Actions;
use Filament\Resources\Pages\EditRecord;

class EditOtherPayment extends EditRecord
{
    protected static string $resource = OtherPaymentResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\DeleteAction::make(),
        ];
    }
}
