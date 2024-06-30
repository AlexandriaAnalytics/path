<?php

namespace App\Filament\Admin\Resources\CandidateRecordResource\Pages;

use App\Filament\Admin\Resources\CandidateRecordResource;
use App\Models\TypeOfTraining;
use Filament\Actions;
use Filament\Resources\Pages\CreateRecord;

class CreateCandidateRecord extends CreateRecord
{
    protected static string $resource = CandidateRecordResource::class;

    protected function mutateFormDataBeforeCreate(array $data): array
    {
        $data['type_of_training_id'] = TypeOfTraining::where('name', 'Candidate')->first()->id;
        return $data;
    }
}
