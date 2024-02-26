<?php

namespace App\Filament\Exports;

use App\Models\Candidate;
use Filament\Actions\Exports\ExportColumn;
use Filament\Actions\Exports\Exporter;
use Filament\Actions\Exports\Models\Export;

class CandidateExporter extends Exporter
{
    protected static ?string $model = Candidate::class;

    public static function getColumns(): array
    {
        return [
            ExportColumn::make('id')
                ->label('Candidate No.'),
            ExportColumn::make('level.name')
                ->label('Level'),
            ExportColumn::make('student.name')
                ->label('Name'),
            ExportColumn::make('student.surname')
                ->label('Surname'),
            ExportColumn::make('granted_discount')
                ->label('Discount')
                ->enabledByDefault(false),
            ExportColumn::make('status'),
            ExportColumn::make('type_of_certificate')
                ->label('Type of Certificate')
                ->enabledByDefault(false),
        ];
    }

    public static function getCompletedNotificationBody(Export $export): string
    {
        $body = 'Your candidate export has completed and ' . number_format($export->successful_rows) . ' ' . str('row')->plural($export->successful_rows) . ' exported.';

        if ($failedRowsCount = $export->getFailedRowsCount()) {
            $body .= ' ' . number_format($failedRowsCount) . ' ' . str('row')->plural($failedRowsCount) . ' failed to export.';
        }

        return $body;
    }
}
