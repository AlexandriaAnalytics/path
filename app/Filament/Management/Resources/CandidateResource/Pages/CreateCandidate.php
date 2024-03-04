<?php

namespace App\Filament\Management\Resources\CandidateResource\Pages;

use App\Filament\Management\Resources\CandidateResource;
use App\Models\Candidate;
use App\Models\CustomLevelPrice;
use App\Models\LevelCountryModule;
use App\Models\Module;
use App\Models\Period;
use Filament\Actions;
use Filament\Notifications\Notification;
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

    protected function mutateFormDataBeforeCreate(array $data): array
    {
        $data['student_id'] = null;

        return $data;
    }

    protected function beforeCreate(): void
    {
        $students = $this->data['student_id'];
        $levelId = $this->data['level_id'];
        $typeOfCertificate = $this->data['type_of_certificate'];
        $modules = $this->data['modules'];

        $this->data['student_id'] = null;

        foreach ($students as $studentId) {
            $candidate = new Candidate();
            $candidate->student_id = $studentId;
            $candidate->level_id = $levelId;
            $candidate->type_of_certificate = $typeOfCertificate;
            $candidate->save();
            $candidate->modules()->attach($modules);
        }

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
        $instituteExtraPrice = CustomLevelPrice::query()
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

            // Or, if the institute has a custom price for the level, apply it
            if ($instituteExtraPrice?->extra_price_all_modules) {
                $examPrice += $instituteExtraPrice->extra_price_all_modules;
            } else if ($instituteExtraPrice?->percentage_extra_price_all_modules) {
                $examPrice *= 1 + $instituteExtraPrice->percentage_extra_price_all_modules / 100;
            }

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

        if ($instituteExtraPrice?->extra_price_exam_right) {
            $countryPrice->price_exam_right += $instituteExtraPrice->extra_price_exam_right;
        } else if ($instituteExtraPrice?->percentage_extra_price_exam_right) {
            $countryPrice->price_exam_right *= 1 + $instituteExtraPrice->percentage_extra_price_exam_right / 100;
        }

        if ($instituteExtraPrice?->extra_price_exam_right_all_modules) {
            $countryPrice->price_exam_right_all_modules += $instituteExtraPrice->extra_price_exam_right_all_modules;
        } else if ($instituteExtraPrice?->percentage_extra_price_exam_right_all_modules) {
            $countryPrice->price_exam_right_all_modules *= 1 + $instituteExtraPrice->percentage_extra_price_exam_right_all_modules / 100;
        }

        if ($missingModules->isEmpty()) {
            $billed_concepts->push([
                'concept' => 'Exam Right (all modules)',
                'currency' => $candidate
                    ->level
                    ->countries
                    ->firstWhere('id', $candidate->student->region->id)
                    ->monetary_unit,
                'amount' => $countryPrice->price_exam_right_all_modules,
            ]);
        } else {
            $billed_concepts->push([
                'concept' => 'Exam Right',
                'currency' => $candidate
                    ->level
                    ->countries
                    ->firstWhere('id', $candidate->student->region->id)
                    ->monetary_unit,
                'amount' => $countryPrice->price_exam_right,
            ]);
        }

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
