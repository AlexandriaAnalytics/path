<?php

namespace App\Jobs;

use App\Models\Candidate;
use App\Models\Module;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;

class AddCandidateBillableConcepts implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    /**
     * Create a new job instance.
     */
    public function __construct(
        private Candidate $candidate,
    ) {
    }

    /**
     * Execute the job.
     */
    public function handle(): void
    {
        $candidate = $this->candidate;
        $billed_concepts = $candidate->billed_concepts;

        if (Module::all()->diff($candidate->modules)->isEmpty()) {
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
                    ->price_discounted,
            ]);
        } else {
            // If the student does not have all the modules, apply the sum of the individual
            // modules prices
            $billed_modules = $candidate
                ->level
                ->countries
                ->firstWhere('id', $candidate->student->region->id)
                ->modules
                ->intersect($candidate->modules);

            $billed_modules->each(function ($module) use ($billed_concepts, $candidate) {
                $billed_concepts->push([
                    'concept' => "Module - {$module->name}",
                    'currency' => $candidate
                        ->level
                        ->countries
                        ->firstWhere('id', $candidate->student->region->id)
                        ->monetary_unit,
                    'amount' => $module->pivot->price,
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
        $rightToExamFee = $candidate
            ->level
            ->countries
            ->firstWhere('id', $candidate->student->region->id)
            ->pivot
            ->institute_right_exam;

        if ($rightToExamFee > 0) {
            $billed_concepts->push([
                'concept' => 'Right to exam',
                'currency' => $candidate
                    ->level
                    ->countries
                    ->firstWhere('id', $candidate->student->region->id)
                    ->monetary_unit,
                'amount' => $rightToExamFee,
            ]);
        }

        $candidate->update([
            'billed_concepts' => $billed_concepts,
        ]);
    }
}
