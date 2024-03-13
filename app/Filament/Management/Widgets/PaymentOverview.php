<?php

namespace App\Filament\Management\Widgets;

use App\Models\Candidate;
use App\Models\Payment;
use Filament\Facades\Filament;
use Filament\Widgets\ChartWidget;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Carbon;
use Illuminate\Support\Collection;

class PaymentOverview extends ChartWidget
{
    protected static ?string $heading = 'Pending payments';

    protected function getData(): array
    {
        $months = Payment::query()
            ->whereHas('candidate.student.institute', fn (Builder $query) => $query->where('institute_id', Filament::getTenant()->id))
            ->whereIn('status', ['paying', 'processing payment'])
            ->whereMonth('current_period', Carbon::now()->month)
            ->whereYear('current_period', Carbon::now()->year)
            ->get()
            ->groupBy(fn (Payment $payment) => Carbon::parse($payment->current_period)->format('F'))
            ->mapWithKeys(fn (Collection $payments) => [
                Carbon::parse($payments->first()->current_period)->format('F Y') => $payments->sum('amount'),
            ]);

        return [
            'datasets' => [
                [
                    'label' => 'Monthly exam installments',
                    'data' => $months->values(),
                ]
            ],
            'labels' => [
                ...$months->keys(),
            ],
        ];
    }

    protected function getType(): string
    {
        return 'bar';
    }
}
