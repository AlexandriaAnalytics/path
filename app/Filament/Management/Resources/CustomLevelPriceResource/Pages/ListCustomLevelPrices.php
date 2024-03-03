<?php

namespace App\Filament\Management\Resources\CustomLevelPriceResource\Pages;

use App\Filament\Management\Resources\CustomLevelPriceResource;
use Filament\Actions;
use Filament\Resources\Pages\ListRecords;
use Filament\Support\Colors\Color;

class ListCustomLevelPrices extends ListRecords
{
    protected static string $resource = CustomLevelPriceResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\CreateAction::make()->color(Color::hex('#0086b3')),
        ];
    }
}
