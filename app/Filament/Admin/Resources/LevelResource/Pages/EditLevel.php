<?php

namespace App\Filament\Admin\Resources\LevelResource\Pages;

use App\Filament\Admin\Resources\LevelResource;
use App\Models\Candidate;
use App\Models\Concept;
use App\Services\CandidateService;
use Filament\Actions;
use Filament\Resources\Pages\EditRecord;
use Filament\Support\Colors\Color;

class EditLevel extends EditRecord
{
    protected static string $resource = LevelResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\DeleteAction::make()->color(Color::hex('#c94f40')),
        ];
    }

    protected function afterSave(): void
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
