<?php

namespace App\Filament\Admin\Resources\CandidateResource\Pages;

use App\Enums\CustomPricing;
use App\Filament\Admin\Resources\CandidateResource;
use App\Models\CustomLevelPrice;
use App\Models\LevelCountry;
use App\Models\LevelCountryModule;
use App\Models\Module;
use Filament\Resources\Pages\CreateRecord;
use Illuminate\Contracts\Support\Htmlable;
use Illuminate\Database\Eloquent\Builder;

class CreateCandidate extends CreateRecord
{
    protected static string $resource = CandidateResource::class;


    public function getTitle(): string | Htmlable
    {
        return __('Create candidate');
    }

    protected function afterCreate(): void
    {
        /** @var \App\Models\Candidate $candidate */
        $candidate = $this->record;
        $billed_concepts = $candidate->billed_concepts;
        $missingModules = Module::all()->diff($candidate->modules);
        $instituteCustomPrice = CustomLevelPrice::query()
            ->whereHas('institute', fn ($query) => $query->where('id', $candidate->student->institute_id))
            ->whereHas('levelCountry', fn ($query) => $query
                ->where('level_id', $candidate->level_id)
                ->where('country_id', $candidate->student->country_id))
            ->first();

        if ($missingModules->isEmpty()) {
            // If the student has all the modules, apply the complete price
            // that may be different from the sum of the individual modules prices

            $examPrice = $candidate
                ->level
                ->countries
                ->firstWhere('id', $candidate->student->region->id)
                ->pivot
                ->price_all_modules;

            $billed_concepts->push([
                'concept' => 'Complete price',
                'currency' => $candidate
                    ->level
                    ->countries
                    ->firstWhere('id', $candidate->student->region->id)
                    ->monetary_unit,
                'amount' => $examPrice,
            ]);
        } else {
            // If the student does not have all the modules, apply the sum of the individual
            // modules prices
            $billed_modules = $candidate
                ->modules()
                ->with([
                    "levelCountries" => fn ($query) => $query
                        ->where("country_id", $candidate->student->country_id)
                        ->where("level_id", $candidate->level_id)
                ])
                ->get();

            $billed_modules->each(function ($module) use ($billed_concepts, $candidate) {
                $billed_concepts->push([
                    'concept' => "Module - {$module->name}",
                    'currency' => $candidate
                        ->level
                        ->countries
                        ->firstWhere('id', $candidate->student->region->id)
                        ->monetary_unit,
                    'amount' => LevelCountryModule::query()
                        ->whereHas('levelCountry', fn (Builder $query) => $query
                            ->where('country_id', $candidate->student->country_id)
                            ->where('level_id', $candidate->level_id))
                        ->where('module_id', $module->id)
                        ->first()
                        ->price,
                ]);
            });
        }

        // If the institute has a right-to-exam fee, apply it
        $countryPrice = $candidate
            ->level
            ->countries
            ->firstWhere('id', $candidate->student->region->id)
            ->pivot;

        $concept = 'Exam right';
        $examRightPrice = $countryPrice->price_exam_right;

        if ($missingModules->isEmpty()) {
            $concept = 'Exam right (all modules)';
            $examRightPrice = $countryPrice->price_exam_right_all_modules;
        }

        if ($instituteCustomPrice?->type === CustomPricing::Percentage) {
            if ($missingModules->isEmpty()) {
                $examRightPrice *= $instituteCustomPrice->exam_registration_fee / 100;
            } else {
                $examRightPrice *= $instituteCustomPrice->module_registration_fee / 100;
            }
        } elseif ($instituteCustomPrice?->type === CustomPricing::Fixed) {
            if ($missingModules->isEmpty()) {
                $examRightPrice = $instituteCustomPrice->exam_registration_fee;
            } else {
                $examRightPrice = $instituteCustomPrice->module_registration_fee;
            }
        }

        $billed_concepts->push([
            'concept' => $concept,
            'currency' => $candidate
                ->level
                ->countries
                ->firstWhere('id', $candidate->student->region->id)
                ->monetary_unit,
            'amount' => $examRightPrice,
        ]);

        // If the student has a discount, apply it
        $discount = $candidate->granted_discount;

        if ($discount > 0) {
            $billed_concepts->push([
                'concept' => 'Discount',
                'currency' => $candidate
                    ->level
                    ->countries
                    ->firstWhere('id', $candidate->student->region->id)
                    ->monetary_unit,
                'amount' => -$billed_concepts->sum('amount') * ($discount / 100),
            ]);
        }

        $candidate->update([
            'billed_concepts' => $billed_concepts,
        ]);
    }

    protected function getRedirectUrl(): string
    {
        return $this->getResource()::getUrl('index');
    }
}
