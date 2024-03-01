<?php

namespace App\Filament\Management\Resources\StudentResource\Pages;

use App\Filament\Imports\StudentImporter;
use App\Filament\Management\Resources\StudentResource;
use Filament\Actions;
use Filament\Facades\Filament;
use Filament\Resources\Pages\ListRecords;

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
                ]),
            Actions\CreateAction::make(),
        ];
    }
}
