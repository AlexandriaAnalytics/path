<?php

namespace App\Filament\Admin\Resources\CustomLevelPriceResource\Pages;

use App\Filament\Admin\Resources\CustomLevelPriceResource;
use App\Models\Level;
use App\Models\LevelCountry;
use Filament\Actions;
use Filament\Resources\Pages\CreateRecord;

class CreateCustomLevelPrice extends CreateRecord
{
    protected static string $resource = CustomLevelPriceResource::class;

    protected function mutateFormDataBeforeCreate(array $data): array
    {
        $data['level_country_id'] = LevelCountry::query()
            ->where('level_id', $data['level_id'])
            ->where('country_id', $data['country_id'])
            ->firstOrFail()
            ->id;

        unset($data['level_id'], $data['country_id']);

        if (isset($data['percentage_extra_price_all_modules'])) {
            // If the percentage is set, we don't need the fixed price
            unset($data['extra_price_all_modules'], $data['extra_price_exam_right'], $data['extra_price_exam_right_all_modules']);
        } else {
            // If the fixed price is set, we don't need the percentage
            unset($data['percentage_extra_price_exam_right'], $data['percentage_extra_price_exam_right_all_modules']);
        }

        return $data;
    }

    protected function afterFill(): void
    {
        debug($this->data);
        $levelCountry = LevelCountry::find($this->data['level_country_id'] ?? null);

        if (!$levelCountry) {
            return;
        }

        $this->form
            ->fill([
                'level_id' => $levelCountry->level_id,
                'country_id' => $levelCountry->country_id,
            ]);
    }

    protected function getRedirectUrl(): string
    {
        return $this->getResource()::getUrl('view', ['record' => $this->record]);
    }
}
