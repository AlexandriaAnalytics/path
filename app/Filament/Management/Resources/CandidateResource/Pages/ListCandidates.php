<?php

namespace App\Filament\Management\Resources\CandidateResource\Pages;

use App\Filament\Admin\Widgets\InstituteTypeOverview;
use App\Filament\Exports\CandidateExporter;
use App\Filament\Management\Resources\CandidateResource;
use App\Filament\Management\Resources\CandidateResource\Widgets\CandidatesPaymentState;
use Filament\Actions;
use Filament\Facades\Filament;
use Filament\Resources\Pages\ListRecords;

class ListCandidates extends ListRecords
{
    protected static string $resource = CandidateResource::class;



    protected function getHeaderActions(): array
    {
        return [
            Actions\ExportAction::make()
                ->label('Export all')
                ->icon('heroicon-o-document-arrow-down')
                ->exporter(CandidateExporter::class)
                ->options([
                    'institute_id' => Filament::getTenant()->id,
                ]),
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
