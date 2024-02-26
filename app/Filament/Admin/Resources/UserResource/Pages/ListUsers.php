<?php

namespace App\Filament\Admin\Resources\UserResource\Pages;

use App\Filament\Admin\Resources\UserResource;
use App\Filament\Exports\UserExporter;
use Filament\Actions;
use Filament\Resources\Pages\ListRecords;
use Filament\Support\Colors\Color;

class ListUsers extends ListRecords
{
    protected static string $resource = UserResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\ExportAction::make()
                ->label('Export Users')
                ->icon('heroicon-o-document-arrow-down')
                ->color(Color::Green)
                ->exporter(UserExporter::class),
            Actions\CreateAction::make(),
        ];
    }
}
