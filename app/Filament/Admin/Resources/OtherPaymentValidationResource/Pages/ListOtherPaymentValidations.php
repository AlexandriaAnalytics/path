<?php

namespace App\Filament\Admin\Resources\OtherPaymentValidationResource\Pages;

use App\Filament\Admin\Resources\OtherPaymentValidationResource;
use Filament\Actions;
use Filament\Resources\Pages\ListRecords;

class ListOtherPaymentValidations extends ListRecords
{
    protected static string $resource = OtherPaymentValidationResource::class;

    protected function getHeaderActions(): array
    {
        return [
            //Actions\CreateAction::make(),
        ];
    }
}
