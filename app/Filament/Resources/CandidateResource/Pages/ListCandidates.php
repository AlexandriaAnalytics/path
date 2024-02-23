<?php

namespace App\Filament\Resources\CandidateResource\Pages;

use App\Filament\Admin\Widgets\InstituteTypeOverview;
use App\Filament\Resources\CandidateResource;
use App\Filament\Resources\CandidateResource\Widgets\CandidatesPaymentState;
use Filament\Actions;
use Filament\Resources\Pages\ListRecords;

class ListCandidates extends ListRecords
{
    protected static string $resource = CandidateResource::class;

    

    protected function getHeaderActions(): array
    {
        return [
            Actions\CreateAction::make(),
        ];
    }

    protected function getHeaderWidgets(): array
    {
        return [
            CandidatesPaymentState::class,
        ];
    }
}
