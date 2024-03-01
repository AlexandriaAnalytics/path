<?php

namespace App\Filament\Exports;

use App\Models\Candidate;
use Filament\Actions\Exports\ExportColumn;
use Filament\Actions\Exports\Exporter;
use Filament\Actions\Exports\Models\Export;
use OpenSpout\Common\Entity\Style\Color;
use OpenSpout\Common\Entity\Style\Style;

class CandidateExporterAsociated extends Exporter
{
    protected static ?string $model = Candidate::class;

    public static function getColumns(): array
    {
        return [
            ExportColumn::make('id')
                ->label('Candidate No.'),
            ExportColumn::make('student.name')
                ->label('Name'),
            ExportColumn::make('student.surname')
                ->label('Surname'),
            ExportColumn::make('granted_discount')
                ->label('Discount'),
            ExportColumn::make('level.name')
                ->label('Exam'),
            ExportColumn::make('status'),
            ExportColumn::make('modules.name')->label('Module Name'),
            ExportColumn::make('pendingModules.name')->label('Exams Session'),
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
