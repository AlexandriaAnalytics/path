<?php

namespace App\Filament\Admin\Resources\StudentResource\Pages;

use App\Filament\Admin\Resources\StudentResource;
use App\Filament\Exports\StudentExporter;
use Filament\Actions;
use Filament\Resources\Pages\ListRecords;
use Filament\Support\Colors\Color;

class ListStudents extends ListRecords
{
    protected static string $resource = StudentResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\ExportAction::make()
                ->label('Export Students')
                ->icon('heroicon-o-document-arrow-down')
                ->color(Color::Green)
                ->exporter(StudentExporter::class),
            Actions\CreateAction::make(),
        ];
    }
}
