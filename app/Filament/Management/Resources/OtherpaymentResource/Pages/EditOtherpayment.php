<?php

namespace App\Filament\Management\Resources\OtherpaymentResource\Pages;

use App\Filament\Management\Resources\OtherpaymentResource;
use Filament\Actions;
use Filament\Resources\Pages\EditRecord;

class EditOtherpayment extends EditRecord
{
    protected static string $resource = OtherpaymentResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\DeleteAction::make(),
        ];
    }
}
