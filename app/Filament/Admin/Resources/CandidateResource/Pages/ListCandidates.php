<?php

namespace App\Filament\Admin\Resources\CandidateResource\Pages;

use App\Filament\Admin\Resources\CandidateResource;
use App\Filament\Exports\CandidateExporter;
use Filament\Actions;
use Filament\Resources\Pages\ListRecords;
use Filament\Support\Colors\Color;

class ListCandidates extends ListRecords
{
    protected static string $resource = CandidateResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\ExportAction::make()
                ->label('Export Candidates')
                ->icon('heroicon-o-document-arrow-down')
                ->color(Color::Green)
                ->exporter(CandidateExporter::class),
            Actions\CreateAction::make(),
        ];
    }
}
