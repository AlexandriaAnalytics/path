<?php

namespace App\Filament\Admin\Resources\InstitutePaymentResource\Pages;

use App\Filament\Admin\Resources\InstitutePaymentResource;
use Filament\Actions;
use Filament\Resources\Pages\ListRecords;

class ListInstitutePayments extends ListRecords
{
    protected static string $resource = InstitutePaymentResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\CreateAction::make(),
        ];
    }
}
