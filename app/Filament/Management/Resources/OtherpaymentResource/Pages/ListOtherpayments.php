<?php

namespace App\Filament\Management\Resources\OtherpaymentResource\Pages;

use App\Filament\Management\Resources\OtherpaymentResource;
use Filament\Actions;
use Filament\Resources\Pages\ListRecords;

class ListOtherpayments extends ListRecords
{
    protected static string $resource = OtherpaymentResource::class;

    protected function getHeaderActions(): array
    {
        return [
            // Actions\CreateAction::make(),
        ];
    }
}
