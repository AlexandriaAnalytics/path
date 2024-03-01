<?php

namespace App\Filament\Admin\Resources\StudentResource\Pages;

use App\Filament\Admin\Resources\StudentResource;
use App\Filament\Exports\StudentExporter;
use App\Filament\Imports\StudentImporter;
use Filament\Actions;
use Filament\Resources\Pages\ListRecords;
use Filament\Support\Colors\Color;

class ListStudents extends ListRecords
{
    protected static string $resource = StudentResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\ImportAction::make()
                ->label('Import')
                ->icon('heroicon-o-document-arrow-up')
                ->color(Color::Amber)
                ->importer(StudentImporter::class),
            Actions\ExportAction::make()
                ->label('Export all')
                ->icon('heroicon-o-document-arrow-down')
                ->color(Color::Green)
                ->exporter(StudentExporter::class),
            Actions\CreateAction::make(),
        ];
    }
}
