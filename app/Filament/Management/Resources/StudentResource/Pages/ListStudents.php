<?php

namespace App\Filament\Management\Resources\StudentResource\Pages;

use App\Filament\Exports\StudentExporter;
use App\Filament\Exports\StudentMagnamentExporter;
use App\Filament\Imports\StudentImporter;
use App\Filament\Management\Resources\StudentResource;
use Filament\Actions;
use Filament\Facades\Filament;
use Filament\Resources\Pages\ListRecords;
use Filament\Support\Colors\Color;

class ListStudents extends ListRecords
{
    protected static string $resource = StudentResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\ImportAction::make()
                ->importer(StudentImporter::class)
                ->options([
                    'institute_id' => Filament::getTenant()->id,
                ])
                ->color(Color::hex('#d4ac71'))
                ->csvDelimiter(';'),
            Actions\ExportAction::make()
                ->label('Export all students')
                ->icon('heroicon-o-document-arrow-down')
                ->color(Color::hex('#83a982'))
                ->exporter(StudentMagnamentExporter::class),
            Actions\CreateAction::make()->color(Color::hex('#0086b3')),
        ];
    }
}
