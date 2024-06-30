<?php

namespace App\Filament\Admin\Resources\CandidateRecordResource\Pages;

use App\Filament\Admin\Resources\CandidateRecordResource;
use Filament\Actions;
use Filament\Resources\Pages\ListRecords;

class ListCandidateRecords extends ListRecords
{
    protected static string $resource = CandidateRecordResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\CreateAction::make(),
        ];
    }
}
