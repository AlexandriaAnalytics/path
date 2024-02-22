<?php

namespace App\Filament\Resources\CandidateResource\Pages;

use App\Filament\Resources\CandidateResource;
use App\Models\Module;
use App\Models\Period;
use Filament\Actions;
use Filament\Notifications\Notification;
use Filament\Resources\Pages\CreateRecord;

class CreateCandidate extends CreateRecord
{
    protected static string $resource = CandidateResource::class;

    protected function beforeCreate(): void
    {
        if (Period::active()->doesntExist()) {
            Notification::make()
                ->warning()
                ->title('There are no active registration periods')
                ->body('Please contact your administrator.')
                ->persistent()
                ->send();

            $this->halt();
        }
    }

    protected function afterCreate(): void
    {
        /** @var \App\Models\Candidate $candidate */
        $candidate = $this->record;
        $billed_concepts = $candidate->billed_concepts;
        $missingModules = Module::all()->diff($candidate->modules);

        if ($missingModules->isEmpty()) {
            // If the student has all the modules, apply the complete price
            // that may be different from the sum of the individual modules prices
            $billed_concepts->push([
                'concept' => 'Complete price',
                'currency' => $candidate
                    ->level
                    ->countries
                    ->firstWhere('id', $candidate->student->region->id)
                    ->monetary_unit,
                'amount' => $candidate
                    ->level
                    ->countries
                    ->firstWhere('id', $candidate->student->region->id)
                    ->pivot
                    ->price_all_modules,
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
                    'currency' => $module
                        ->levelCountries
                        ->first()
                        ->country
                        ->monetary_unit,
                    'amount' => $module
                        ->levelCountries
                        ->first()
                        ->price,
                ]);
            });
        }

        // If the institute has an additional price for the level, apply it
        $instituteFee = $candidate
            ->level
            ->countries
            ->firstWhere('id', $candidate->student->region->id)
            ->pivot
            ->institute_diferencial_aditional_price;

        if ($instituteFee > 0) {
            $billed_concepts->push([
                'concept' => 'Service fee',
                'currency' => $candidate
                    ->level
                    ->countries
                    ->firstWhere('id', $candidate->student->region->id)
                    ->monetary_unit,
                'amount' => $instituteFee,
            ]);
        }

        // If the institute has a right-to-exam fee, apply it
        $countryPrice = $candidate
            ->level
            ->countries
            ->firstWhere('id', $candidate->student->region->id)
            ->pivot;

        if ($missingModules->isEmpty()) {
            $billed_concepts->push([
                'concept' => 'Right to exam (all modules)',
                'currency' => $candidate
                    ->level
                    ->countries
                    ->firstWhere('id', $candidate->student->region->id)
                    ->monetary_unit,
                'amount' => $countryPrice->price_exam_right_all_modules,
            ]);
        } else {
            $billed_concepts->push([
                'concept' => 'Right to exam',
                'currency' => $candidate
                    ->level
                    ->countries
                    ->firstWhere('id', $candidate->student->region->id)
                    ->monetary_unit,
                'amount' => $countryPrice->price_exam_right,
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
