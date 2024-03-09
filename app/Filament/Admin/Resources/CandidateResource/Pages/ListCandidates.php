<?php

namespace App\Filament\Admin\Resources\CandidateResource\Pages;

use App\Filament\Admin\Resources\CandidateResource;
use App\Filament\Exports\CandidateExporter;
use Filament\Actions;
use Filament\Resources\Pages\ListRecords;
use Filament\Support\Colors\Color;
use Illuminate\Database\Eloquent\Builder;

class ListCandidates extends ListRecords
{
    protected static string $resource = CandidateResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\ExportAction::make()
                ->label('Export candidates')
                ->icon('heroicon-o-document-arrow-down')
                ->color(Color::hex('#83a982'))
                ->exporter(CandidateExporter::class)
                ->modifyQueryUsing(fn (Builder $query) => $query->where('is_active', true)),
            Actions\CreateAction::make()->color(Color::hex('#0086b3')),
        ];
    }
}
