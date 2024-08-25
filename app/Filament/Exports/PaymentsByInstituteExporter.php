<?php

namespace App\Filament\Exports;

use App\Models\Candidate;
use App\Models\Payment;
use Filament\Actions\Exports\ExportColumn;
use Filament\Actions\Exports\Exporter;
use Filament\Actions\Exports\Models\Export;

class PaymentsByInstituteExporter extends Exporter
{
    protected static ?string $model = Candidate::class;

    public static function getColumns(): array
    {
        return [
            ExportColumn::make('id')
                ->label('Candidate No.'),
            ExportColumn::make('PaymentStatus'),
            ExportColumn::make('student.name')
                ->label('Names'),
            ExportColumn::make('student.surname')
                ->label('Surnames'),
            ExportColumn::make('level.name')
                ->label('Exam'),
            ExportColumn::make('modules.name'),
            ExportColumn::make('examCost')
                ->prefix('$ '),
            ExportColumn::make('registration_fee')
                ->prefix('% '),
            ExportColumn::make('granted_discount')
                ->label('Scholarship')
                ->suffix('%'),
            ExportColumn::make('installments')
                ->formatStateUsing(function (string $state, Candidate $record) {
                    $state = $record->installmentAttribute;
                    if ($record->paymentStatus == 'paid') {
                        $installmentsPaid = $state;
                    } else {
                        $installmentsPaid = Payment::query()->where('candidate_id', $record->id)->where('status', 'approved')->count();
                    }
                    return $installmentsPaid . ' / ' . $state;
                }),
            ExportColumn::make('installment_1')
                ->formatStateUsing(function (float $state, Candidate $record) {
                    $installmentsPaid = Payment::query()->where('candidate_id', $record->id)->where('status', 'approved')->count();
                    if ($installmentsPaid == 0) {
                        return $state + $record->discount;
                    }
                    return $state;
                }),
            ExportColumn::make('installment_2')
                ->formatStateUsing(function (float $state, Candidate $record) {
                    $installmentsPaid = Payment::query()->where('candidate_id', $record->id)->where('status', 'approved')->count();
                    if ($installmentsPaid == 1) {
                        return $state + $record->discount;
                    }
                    return $state;
                }),
            ExportColumn::make('installment_3')
                ->formatStateUsing(function (float $state, Candidate $record) {
                    $installmentsPaid = Payment::query()->where('candidate_id', $record->id)->where('status', 'approved')->count();
                    if ($installmentsPaid == 2) {
                        return $state + $record->discount;
                    }
                    return $state;
                }),
            ExportColumn::make('installment_4')
                ->formatStateUsing(function (float $state, Candidate $record) {
                    $installmentsPaid = Payment::query()->where('candidate_id', $record->id)->where('status', 'approved')->count();
                    if ($installmentsPaid == 3) {
                        return $state + $record->discount;
                    }
                    return $state;
                }),
            ExportColumn::make('installment_5')
                ->formatStateUsing(function (float $state, Candidate $record) {
                    $installmentsPaid = Payment::query()->where('candidate_id', $record->id)->where('status', 'approved')->count();
                    if ($installmentsPaid == 4) {
                        return $state + $record->discount;
                    }
                    return $state;
                }),
            ExportColumn::make('installment_6')
                ->formatStateUsing(function (float $state, Candidate $record) {
                    $installmentsPaid = Payment::query()->where('candidate_id', $record->id)->where('status', 'approved')->count();
                    if ($installmentsPaid == 5) {
                        return $state + $record->discount;
                    }
                    return $state;
                }),
            ExportColumn::make('installment_7')
                ->formatStateUsing(function (float $state, Candidate $record) {
                    $installmentsPaid = Payment::query()->where('candidate_id', $record->id)->where('status', 'approved')->count();
                    if ($installmentsPaid == 6) {
                        return $state + $record->discount;
                    }
                    return $state;
                }),
            ExportColumn::make('installment_8')
                ->formatStateUsing(function (float $state, Candidate $record) {
                    $installmentsPaid = Payment::query()->where('candidate_id', $record->id)->where('status', 'approved')->count();
                    if ($installmentsPaid == 7) {
                        return $state + $record->discount;
                    }
                    return $state;
                }),
            ExportColumn::make('installment_9')
                ->formatStateUsing(function (float $state, Candidate $record) {
                    $installmentsPaid = Payment::query()->where('candidate_id', $record->id)->where('status', 'approved')->count();
                    if ($installmentsPaid == 8) {
                        return $state + $record->discount;
                    }
                    return $state;
                }),
            ExportColumn::make('installment_10')
                ->formatStateUsing(function (float $state, Candidate $record) {
                    $installmentsPaid = Payment::query()->where('candidate_id', $record->id)->where('status', 'approved')->count();
                    if ($installmentsPaid == 9) {
                        return $state + $record->discount;
                    }
                    return $state;
                }),
            ExportColumn::make('installment_11')
                ->formatStateUsing(function (float $state, Candidate $record) {
                    $installmentsPaid = Payment::query()->where('candidate_id', $record->id)->where('status', 'approved')->count();
                    if ($installmentsPaid == 10) {
                        return $state + $record->discount;
                    }
                    return $state;
                }),
            ExportColumn::make('installment_12')
                ->formatStateUsing(function (float $state, Candidate $record) {
                    $installmentsPaid = Payment::query()->where('candidate_id', $record->id)->where('status', 'approved')->count();
                    if ($installmentsPaid == 11) {
                        return $state + $record->discount;
                    }
                    return $state;
                })
        ];
    }

    public static function getCompletedNotificationBody(Export $export): string
    {
        $body = 'Your payments by institute export has completed and ' . number_format($export->successful_rows) . ' ' . str('row')->plural($export->successful_rows) . ' exported.';

        if ($failedRowsCount = $export->getFailedRowsCount()) {
            $body .= ' ' . number_format($failedRowsCount) . ' ' . str('row')->plural($failedRowsCount) . ' failed to export.';
        }

        return $body;
    }
}
