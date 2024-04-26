<?php

namespace App\Filament\Admin\Resources\CandidateResource\Pages;

use App\Filament\Admin\Resources\CandidateResource;
use App\Models\Candidate;
use App\Models\Concept;
use App\Models\Payment;
use App\Services\CandidateService;
use Carbon\Carbon;
use Filament\Actions;
use Filament\Resources\Pages\EditRecord;
use Filament\Support\Colors\Color;

class EditCandidate extends EditRecord
{
    protected static string $resource = CandidateResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\DeleteAction::make()->color(Color::hex('#c94f40')),
        ];
    }

    protected function afterSave(): void
    {
        dd($this->record);
        Concept::where('candidate_id', $this->record->id)->delete();
        CandidateService::createConcepts($this->record);
    }


    /* protected function beforeSave()
    {
        $candidate = Candidate::find($this->data['id']);
        if ($candidate->status == 'unpaid') {
            $payment_deadline = $candidate->exams->min('payment_deadline');
            $candidate->installments = round(now()->diffInMonths(Carbon::parse($payment_deadline), absolute: false), 0,) + 1;
            $candidate->save();
            Payment::where('candidate_id', $candidate->id)->delete();
        }
    } */
}
