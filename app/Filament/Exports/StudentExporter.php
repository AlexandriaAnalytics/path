<?php

namespace App\Filament\Exports;

use App\Models\Student;
use Filament\Actions\Exports\ExportColumn;
use Filament\Actions\Exports\Exporter;
use Filament\Actions\Exports\Models\Export;

class StudentExporter extends Exporter
{
    protected static ?string $model = Student::class;

    public static function getColumns(): array
    {
        return [
            ExportColumn::make('id')
                ->label('ID'),
            ExportColumn::make('institute.name')
                ->label('Institute'),
            ExportColumn::make('country.name')
                ->label('Country'),
            ExportColumn::make('name')
                ->label('Name'),
            ExportColumn::make('surname')
                ->label('Surname'),
            ExportColumn::make('email')
                ->label('Email'),
            ExportColumn::make('birth_date')
                ->label('Birth Date'),
            ExportColumn::make('cbu')
                ->label('CBU'),
        ];
    }

    public static function getCompletedNotificationBody(Export $export): string
    {
        $body = 'Your student export has completed and ' . number_format($export->successful_rows) . ' ' . str('row')->plural($export->successful_rows) . ' exported.';

        if ($failedRowsCount = $export->getFailedRowsCount()) {
            $body .= ' ' . number_format($failedRowsCount) . ' ' . str('row')->plural($failedRowsCount) . ' failed to export.';
        }

        return $body;
    }
}
