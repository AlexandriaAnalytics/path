<?php

namespace App\Filament\Management\Resources\FinancingResource\Pages;

use App\Filament\Management\Resources\FinancingResource;
use Filament\Actions;
use Filament\Resources\Pages\EditRecord;

class EditFinancing extends EditRecord
{
    protected static string $resource = FinancingResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\DeleteAction::make(),
        ];
    }
}
