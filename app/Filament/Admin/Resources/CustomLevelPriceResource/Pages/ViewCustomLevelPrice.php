<?php

namespace App\Filament\Admin\Resources\CustomLevelPriceResource\Pages;

use App\Enums\CustomPricing;
use App\Filament\Admin\Resources\CustomLevelPriceResource;
use App\Models\Country;
use App\Models\CustomLevelPrice;
use Filament\Actions;
use Filament\Forms\Get;
use Filament\Infolists\Components\Fieldset;
use Filament\Infolists\Components\RepeatableEntry;
use Filament\Infolists\Components\TextEntry;
use Filament\Infolists\Infolist;
use Filament\Resources\Pages\ViewRecord;

class ViewCustomLevelPrice extends ViewRecord
{
    protected static string $resource = CustomLevelPriceResource::class;

    public function infolist(Infolist $infolist): Infolist
    {
        return $infolist
            ->columns(3)
            ->schema([
                TextEntry::make('institute.name')
                    ->label('Member or centre'),
                TextEntry::make('levelCountry.level.name')
                    ->label('Exam'),
                TextEntry::make('levelCountry.country.name')
                    ->label('Country'),
                Fieldset::make('Registration fees')
                    ->schema([
                        TextEntry::make('type')
                            ->label('Discount type'),
                        TextEntry::make('full_exam_fee')
                            ->label('Full exam fee')
                            ->suffix(fn (CustomLevelPrice $record) => $record->type === CustomPricing::Percentage ? '%' : null),
                        TextEntry::make('full_exam_registration_fee')
                            ->label('Full exam registration fee')
                            ->suffix(fn (CustomLevelPrice $record) => $record->type === CustomPricing::Percentage ? '%' : null),
                        TextEntry::make('module_registration_fee')
                            ->label('Module registration fee')
                            ->suffix(fn (CustomLevelPrice $record) => $record->type === CustomPricing::Percentage ? '%' : null),
                    ]),
                RepeatableEntry::make('customModulePrices')
                    ->label('Module prices')
                    ->columns(2)
                    ->schema([
                        TextEntry::make('module.name')
                            ->label('Module'),
                        TextEntry::make('price')
                            ->label('Price'),
                    ]),
            ]);
    }

    protected function getHeaderActions(): array
    {
        return [
            Actions\DeleteAction::make(),
            // Actions\EditAction::make(),
        ];
    }
}
