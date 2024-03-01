<?php

namespace App\Filament\Management\Resources\CustomLevelPriceResource\Pages;

use App\Filament\Management\Resources\CustomLevelPriceResource;
use Filament\Actions;
use Filament\Resources\Pages\ListRecords;

class ListCustomLevelPrices extends ListRecords
{
    protected static string $resource = CustomLevelPriceResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\CreateAction::make(),
        ];
    }
}
