<?php

namespace App\Filament\Admin\Resources\PaymentValidationResource\Pages;

use App\Filament\Admin\Resources\PaymentValidationResource;
use App\Filament\Admin\Resources\PaymentValidationResource\Widgets\PaymentValidationWidgets;
use Filament\Actions;
use Filament\Resources\Pages\ListRecords;

class ListPaymentValidations extends ListRecords
{
    protected static string $resource = PaymentValidationResource::class;

    protected function getHeaderWidgets(): array
    {
        return [
            PaymentValidationWidgets::class,
        ];
    }

    protected function getHeaderActions(): array
    {
        return [
            //Actions\CreateAction::make(),
        ];
    }
}
