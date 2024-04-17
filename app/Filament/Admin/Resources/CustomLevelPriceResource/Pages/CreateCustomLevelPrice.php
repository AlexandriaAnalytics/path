<?php

namespace App\Filament\Admin\Resources\CustomLevelPriceResource\Pages;

use App\Filament\Admin\Resources\CustomLevelPriceResource;
use App\Models\Candidate;
use App\Models\Level;
use App\Models\LevelCountry;
use App\Services\CandidateService;
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

        return $data;
    }

    protected function afterFill(): void
    {
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

    protected function afterCreate(): void
    {
        $candidates = Candidate::all();
        foreach ($candidates as $candidate) {
            if ($candidate->paymentStatus == 'unpaid') {
                CandidateService::createConcepts($candidate);
            }
        }
    }
}
