<?php

namespace App\Filament\Admin\Resources\CustomLevelPriceResource\Pages;

use App\Filament\Admin\Resources\CustomLevelPriceResource;
use App\Models\Candidate;
use App\Models\Concept;
use App\Models\CustomLevelPrice;
use App\Models\CustomModulePrice;
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

    protected function beforeCreate()
    {
        foreach ($this->data['institute'] as $institute) {
            $levelCountryIds = LevelCountry::where('level_id', $this->data['level_id'])
                ->where('country_id', $this->data['country_id'])
                ->pluck('id');

            $customLevelPrices = CustomLevelPrice::where('institute_id', $institute)
                ->whereIn('level_country_id', $levelCountryIds)
                ->get();

            CustomModulePrice::whereIn('custom_level_price_id', $customLevelPrices->pluck('id'))->delete();

            foreach ($customLevelPrices as $customLevelPrice) {
                $customLevelPrice->delete();
            };

            $customLevelPrice = new CustomLevelPrice();
            $customLevelPrice->institute_id = $institute;
            $customLevelPrice->level_country_id = $this->data['level_country_id'];
            $customLevelPrice->full_exam_fee = $this->data['full_exam_fee'];
            $customLevelPrice->full_exam_registration_fee = $this->data['full_exam_registration_fee'];
            $customLevelPrice->module_registration_fee = $this->data['module_registration_fee'];
            $customLevelPrice->type = $this->data['type'];
            $customLevelPrice->save();

            if ($this->data['custom_module_prices'] != []) {
                foreach ($this->data['custom_module_prices'] as $module) {
                    $customModulePrice = new CustomModulePrice();
                    $customModulePrice->custom_level_price_id = $customLevelPrice->id;
                    $customModulePrice->module_id = $module['module_id'];
                    $customModulePrice->price = $module['price'];
                    $customModulePrice->save();
                }
            }
        }
        $this->halt();
    }

    protected function afterCreate(): void
    {
        $candidates = Candidate::all();
        foreach ($candidates as $candidate) {
            if ($candidate->paymentStatus == 'unpaid') {
                Concept::where('candidate_id', $candidate->id)->delete();
                CandidateService::createConcepts($candidate);
            }
        }
    }
}
