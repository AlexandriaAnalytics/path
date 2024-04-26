<?php

namespace App\Filament\Admin\Resources\CandidateResource\Pages;

use App\Filament\Admin\Resources\CandidateResource;
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
        Concept::where('candidate_id', $this->record->id)->delete();
        CandidateService::createConcepts($this->record);
    }



    protected function mutateFormDataBeforeSave(array $data): array
    {
        $status = $data['status'];
        if ($status == 'unpaid') {
            $payment_deadline = $this->record->exams->min('payment_deadline');
            $this->record->installments = round(now()->diffInMonths(Carbon::parse($payment_deadline), absolute: false), 0,) + 1;
            $this->record->save();
            Payment::query()->where('candidate_id', $this->record->id)->delete();
        }

        return $data;
    }
}
