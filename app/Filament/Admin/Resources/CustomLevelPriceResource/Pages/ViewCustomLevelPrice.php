<?php

namespace App\Filament\Admin\Resources\CustomLevelPriceResource\Pages;

use App\Filament\Admin\Resources\CustomLevelPriceResource;
use App\Models\Country;
use App\Models\CustomLevelPrice;
use Filament\Actions;
use Filament\Forms\Get;
use Filament\Infolists\Components\Fieldset;
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
                Fieldset::make('Exam Right')
                    ->visible(fn (CustomLevelPrice $record) => isset($record->extra_price_all_modules))
                    ->columns(3)
                    ->schema([
                        TextEntry::make('extra_price_all_modules')
                            ->label('Complete Exam Price (extra fee)')
                            ->suffix(' ARS'),
                        TextEntry::make('extra_price_exam_right')
                            ->label('Incomplete Exam Right (extra fee)')
                            ->suffix(' ARS'),
                        TextEntry::make('extra_price_exam_right_all_modules')
                            ->label('Complete Exam Right (extra fee)')
                            ->suffix(' ARS'),
                    ]),
                Fieldset::make('Exam Right')
                    ->visible(fn (CustomLevelPrice $record) => isset($record->percentage_extra_price_all_modules))
                    ->columns(3)
                    ->schema([
                        TextEntry::make('percentage_extra_price_all_modules')
                            ->label('Complete Exam Price (extra fee)')
                            ->suffix('%'),
                        TextEntry::make('percentage_extra_price_exam_right')
                            ->label('Incomplete Exam Right (extra fee)')
                            ->suffix('%'),
                        TextEntry::make('percentage_extra_price_exam_right_all_modules')
                            ->label('Complete Exam Right (extra fee)')
                            ->suffix('%'),
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
