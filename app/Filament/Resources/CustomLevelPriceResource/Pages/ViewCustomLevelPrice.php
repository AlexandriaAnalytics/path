<?php

namespace App\Filament\Resources\CustomLevelPriceResource\Pages;

use App\Filament\Resources\CustomLevelPriceResource;
use Filament\Actions;
use Filament\Infolists\Components\TextEntry;
use Filament\Infolists\Infolist;
use Filament\Resources\Pages\ViewRecord;

class ViewCustomLevelPrice extends ViewRecord
{
    protected static string $resource = CustomLevelPriceResource::class;

    public function infolist(Infolist $infolist): Infolist
    {
        return $infolist
            ->schema([
                TextEntry::make('levelCountry.level.name')
                    ->label('Level'),
                TextEntry::make('levelCountry.country.name')
                    ->label('Country'),
                TextEntry::make('price_all_modules')
                    ->label('Price All Modules'),
                TextEntry::make('price_exam_right_all_modules')
                    ->label('Price Exam Right All Modules'),
            ]);
    }

    protected function getHeaderActions(): array
    {
        return [
            Actions\DeleteAction::make(),
            Actions\EditAction::make(),
        ];
    }
}
