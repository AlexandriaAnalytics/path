<?php

namespace App\Filament\Admin\Resources\InstitutePaymentResource\Pages;

use App\Filament\Admin\Resources\InstitutePaymentResource;
use Filament\Actions;
use Filament\Resources\Pages\EditRecord;

class EditInstitutePayment extends EditRecord
{
    protected static string $resource = InstitutePaymentResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\DeleteAction::make(),
        ];
    }
}
