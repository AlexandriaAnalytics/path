<?php

namespace App\Filament\Admin\Resources\PaymentValidationResource\Pages;

use App\Filament\Admin\Resources\PaymentValidationResource;
use Filament\Actions;
use Filament\Resources\Pages\EditRecord;

class EditPaymentValidation extends EditRecord
{
    protected static string $resource = PaymentValidationResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\DeleteAction::make(),
        ];
    }
}
