<?php

namespace App\Filament\Exports;

use App\Models\Candidate;
use App\Models\Concept;
use App\Models\Institute;
use App\Models\Payment;
use Filament\Actions\Exports\ExportColumn;
use Filament\Actions\Exports\Exporter;
use Filament\Actions\Exports\Models\Export;
use Illuminate\Database\Eloquent\Builder;

class InstitutesExporter extends Exporter
{
    protected static ?string $model = Institute::class;

    public static function getColumns(): array
    {
        return [
            ExportColumn::make('name')
                ->label('Institute'),
            ExportColumn::make('instituteType.name'),
            ExportColumn::make('internal_payment_administration')
                ->formatStateUsing(function (string $state) {
                    return $state == 1 ? 'Yes' : 'No';
                }),
            ExportColumn::make('Number of candidates')
                ->default(function (Institute $record) {
                    return  Candidate::whereHas('student', function (Builder $query) use ($record) {
                        $query->where('institute_id', $record->id);
                    })->count();
                }),
            ExportColumn::make('Payments made')
                ->default(function (Institute $record) {
                    return Payment::where('institute_id', $record->id)->where('status', 'approved')->sum('amount');
                })
                ->suffix(
                    fn(Institute $record) => $record->currency,
                ),
            ExportColumn::make('Pending payments')
                ->default(function (Institute $record) {
                    return Concept::whereHas('candidate', function (Builder $query) use ($record) {
                        $query->whereHas('student', function (Builder $query) use ($record) {
                            $query->where('institute_id', $record->id);
                        });
                    })->sum('amount');
                })
                ->suffix(
                    fn(Institute $record) => $record->currency,
                ),
            ExportColumn::make('Payment_1')
                ->default(function (Institute $record) {
                    $differentPayments = Payment::where('institute_id', $record->id)
                        ->distinct('payment_id')
                        ->get();
                    $payments = 0;
                    if (isset($differentPayments[0])) {
                        $payments = Payment::where('payment_id', $differentPayments[0]->payment_id)->sum('amount');
                    }
                    $candidates = Candidate::where('installments', '>=', 1)->whereHas('student', function (Builder $query) use ($record) {
                        $query->where('institute_id', $record->id);
                    })->get();
                    foreach ($candidates as $candidate) {
                        if ($candidate->pendingInstallments != 0) {
                            $payments = $payments + ($candidate->pendingPayment / $candidate->pendingInstallments);
                        }
                    }
                    return $payments;
                })
                ->suffix(
                    fn(Institute $record) => $record->currency,
                ),
            ExportColumn::make('Payment_2')
                ->default(function (Institute $record) {
                    $differentPayments = Payment::where('institute_id', $record->id)
                        ->distinct('payment_id')
                        ->get();
                    $payments = 0;
                    if (isset($differentPayments[1])) {
                        $payments = Payment::where('payment_id', $differentPayments[1]->payment_id)->sum('amount');
                    }
                    $candidates = Candidate::where('installments', '>=', 2)->whereHas('student', function (Builder $query) use ($record) {
                        $query->where('institute_id', $record->id);
                    })->get();
                    foreach ($candidates as $candidate) {
                        if ($candidate->pendingInstallments != 0) {
                            $payments = $payments + ($candidate->pendingPayment / $candidate->pendingInstallments);
                        }
                    }
                    return $payments;
                })
                ->suffix(
                    fn(Institute $record) => $record->currency,
                ),
            ExportColumn::make('Payment_3')
                ->default(function (Institute $record) {
                    $differentPayments = Payment::where('institute_id', $record->id)
                        ->distinct('payment_id')
                        ->get();
                    $payments = 0;
                    if (isset($differentPayments[2])) {
                        $payments = Payment::where('payment_id', $differentPayments[2]->payment_id)->sum('amount');
                    }
                    $candidates = Candidate::where('installments', '>=', 3)->whereHas('student', function (Builder $query) use ($record) {
                        $query->where('institute_id', $record->id);
                    })->get();
                    foreach ($candidates as $candidate) {
                        if ($candidate->pendingInstallments != 0) {
                            $payments = $payments + ($candidate->pendingPayment / $candidate->pendingInstallments);
                        }
                    }
                    return $payments;
                })
                ->suffix(
                    fn(Institute $record) => $record->currency,
                ),
            ExportColumn::make('Payment_4')
                ->default(function (Institute $record) {
                    $differentPayments = Payment::where('institute_id', $record->id)
                        ->distinct('payment_id')
                        ->get();
                    $payments = 0;
                    if (isset($differentPayments[3])) {
                        $payments = Payment::where('payment_id', $differentPayments[3]->payment_id)->sum('amount');
                    }
                    $candidates = Candidate::where('installments', '>=', 4)->whereHas('student', function (Builder $query) use ($record) {
                        $query->where('institute_id', $record->id);
                    })->get();
                    foreach ($candidates as $candidate) {
                        if ($candidate->pendingInstallments != 0) {
                            $payments = $payments + ($candidate->pendingPayment / $candidate->pendingInstallments);
                        }
                    }
                    return $payments;
                })
                ->suffix(
                    fn(Institute $record) => $record->currency,
                ),
            ExportColumn::make('Payment_5')
                ->default(function (Institute $record) {
                    $differentPayments = Payment::where('institute_id', $record->id)
                        ->distinct('payment_id')
                        ->get();
                    $payments = 0;
                    if (isset($differentPayments[4])) {
                        $payments = Payment::where('payment_id', $differentPayments[4]->payment_id)->sum('amount');
                    }
                    $candidates = Candidate::where('installments', '>=', 5)->whereHas('student', function (Builder $query) use ($record) {
                        $query->where('institute_id', $record->id);
                    })->get();
                    foreach ($candidates as $candidate) {
                        if ($candidate->pendingInstallments != 0) {
                            $payments = $payments + ($candidate->pendingPayment / $candidate->pendingInstallments);
                        }
                    }
                    return $payments;
                })
                ->suffix(
                    fn(Institute $record) => $record->currency,
                ),
            ExportColumn::make('Payment_6')
                ->default(function (Institute $record) {
                    $differentPayments = Payment::where('institute_id', $record->id)
                        ->distinct('payment_id')
                        ->get();
                    $payments = 0;
                    if (isset($differentPayments[5])) {
                        $payments = Payment::where('payment_id', $differentPayments[5]->payment_id)->sum('amount');
                    }
                    $candidates = Candidate::where('installments', '>=', 6)->whereHas('student', function (Builder $query) use ($record) {
                        $query->where('institute_id', $record->id);
                    })->get();
                    foreach ($candidates as $candidate) {
                        if ($candidate->pendingInstallments != 0) {
                            $payments = $payments + ($candidate->pendingPayment / $candidate->pendingInstallments);
                        }
                    }
                    return $payments;
                })
                ->suffix(
                    fn(Institute $record) => $record->currency,
                ),
            ExportColumn::make('Payment_7')
                ->default(function (Institute $record) {
                    $differentPayments = Payment::where('institute_id', $record->id)
                        ->distinct('payment_id')
                        ->get();
                    $payments = 0;
                    if (isset($differentPayments[6])) {
                        $payments = Payment::where('payment_id', $differentPayments[6]->payment_id)->sum('amount');
                    }
                    $candidates = Candidate::where('installments', '>=', 7)->whereHas('student', function (Builder $query) use ($record) {
                        $query->where('institute_id', $record->id);
                    })->get();
                    foreach ($candidates as $candidate) {
                        if ($candidate->pendingInstallments != 0) {
                            $payments = $payments + ($candidate->pendingPayment / $candidate->pendingInstallments);
                        }
                    }
                    return $payments;
                })
                ->suffix(
                    fn(Institute $record) => $record->currency,
                ),
            ExportColumn::make('Payment_8')
                ->default(function (Institute $record) {
                    $differentPayments = Payment::where('institute_id', $record->id)
                        ->distinct('payment_id')
                        ->get();
                    $payments = 0;
                    if (isset($differentPayments[7])) {
                        $payments = Payment::where('payment_id', $differentPayments[7]->payment_id)->sum('amount');
                    }
                    $candidates = Candidate::where('installments', '>=', 8)->whereHas('student', function (Builder $query) use ($record) {
                        $query->where('institute_id', $record->id);
                    })->get();
                    foreach ($candidates as $candidate) {
                        if ($candidate->pendingInstallments != 0) {
                            $payments = $payments + ($candidate->pendingPayment / $candidate->pendingInstallments);
                        }
                    }
                    return $payments;
                })
                ->suffix(
                    fn(Institute $record) => $record->currency,
                ),
            ExportColumn::make('Payment_9')
                ->default(function (Institute $record) {
                    $differentPayments = Payment::where('institute_id', $record->id)
                        ->distinct('payment_id')
                        ->get();
                    $payments = 0;
                    if (isset($differentPayments[8])) {
                        $payments = Payment::where('payment_id', $differentPayments[8]->payment_id)->sum('amount');
                    }
                    $candidates = Candidate::where('installments', '>=', 9)->whereHas('student', function (Builder $query) use ($record) {
                        $query->where('institute_id', $record->id);
                    })->get();
                    foreach ($candidates as $candidate) {
                        if ($candidate->pendingInstallments != 0) {
                            $payments = $payments + ($candidate->pendingPayment / $candidate->pendingInstallments);
                        }
                    }
                    return $payments;
                })
                ->suffix(
                    fn(Institute $record) => $record->currency,
                ),
            ExportColumn::make('Payment_10')
                ->default(function (Institute $record) {
                    $differentPayments = Payment::where('institute_id', $record->id)
                        ->distinct('payment_id')
                        ->get();
                    $payments = 0;
                    if (isset($differentPayments[9])) {
                        $payments = Payment::where('payment_id', $differentPayments[9]->payment_id)->sum('amount');
                    }
                    $candidates = Candidate::where('installments', '>=', 10)->whereHas('student', function (Builder $query) use ($record) {
                        $query->where('institute_id', $record->id);
                    })->get();
                    foreach ($candidates as $candidate) {
                        if ($candidate->pendingInstallments != 0) {
                            $payments = $payments + ($candidate->pendingPayment / $candidate->pendingInstallments);
                        }
                    }
                    return $payments;
                })
                ->suffix(
                    fn(Institute $record) => $record->currency,
                ),
            ExportColumn::make('Payment_11')
                ->default(function (Institute $record) {
                    $differentPayments = Payment::where('institute_id', $record->id)
                        ->distinct('payment_id')
                        ->get();
                    $payments = 0;
                    if (isset($differentPayments[10])) {
                        $payments = Payment::where('payment_id', $differentPayments[10]->payment_id)->sum('amount');
                    }
                    $candidates = Candidate::where('installments', '>=', 11)->whereHas('student', function (Builder $query) use ($record) {
                        $query->where('institute_id', $record->id);
                    })->get();
                    foreach ($candidates as $candidate) {
                        if ($candidate->pendingInstallments != 0) {
                            $payments = $payments + ($candidate->pendingPayment / $candidate->pendingInstallments);
                        }
                    }
                    return $payments;
                })
                ->suffix(
                    fn(Institute $record) => $record->currency,
                ),
            ExportColumn::make('Payment_12')
                ->default(function (Institute $record) {
                    $differentPayments = Payment::where('institute_id', $record->id)
                        ->distinct('payment_id')
                        ->get();
                    $payments = 0;
                    if (isset($differentPayments[11])) {
                        $payments = Payment::where('payment_id', $differentPayments[11]->payment_id)->sum('amount');
                    }
                    $candidates = Candidate::where('installments', '>=', 12)->whereHas('student', function (Builder $query) use ($record) {
                        $query->where('institute_id', $record->id);
                    })->get();
                    foreach ($candidates as $candidate) {
                        if ($candidate->pendingInstallments != 0) {
                            $payments = $payments + ($candidate->pendingPayment / $candidate->pendingInstallments);
                        }
                    }
                    return $payments;
                })
                ->suffix(
                    fn(Institute $record) => $record->currency,
                ),
            ExportColumn::make('pending_installments')
                ->default(function (Institute $record) {
                    $candidates = $record->candidates;
                    foreach ($candidates as $candidate) {
                        if ($candidate->pendingInstallments > 0) {
                            return 'Yes';
                        }
                    }
                    return 'No';
                })
        ];
    }

    public static function getCompletedNotificationBody(Export $export): string
    {
        $body = 'Your institutes export has completed and ' . number_format($export->successful_rows) . ' ' . str('row')->plural($export->successful_rows) . ' exported.';

        if ($failedRowsCount = $export->getFailedRowsCount()) {
            $body .= ' ' . number_format($failedRowsCount) . ' ' . str('row')->plural($failedRowsCount) . ' failed to export.';
        }

        return $body;
    }
}
