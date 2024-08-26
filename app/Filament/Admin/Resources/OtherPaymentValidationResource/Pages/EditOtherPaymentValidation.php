<?php

namespace App\Filament\Admin\Resources\OtherPaymentValidationResource\Pages;

use App\Filament\Admin\Resources\OtherPaymentValidationResource;
use Filament\Actions;
use Filament\Resources\Pages\EditRecord;

class EditOtherPaymentValidation extends EditRecord
{
    protected static string $resource = OtherPaymentValidationResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\DeleteAction::make(),
        ];
    }
}
