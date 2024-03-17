<?php

namespace App\Filament\Management\Resources\CandidateResource\Widgets;

use App\Filament\Management\Resources\CandidateResource\Pages\ListCandidates;
use App\Models\Candidate;
use App\Models\Payment;
use Filament\Facades\Filament;
use Filament\Tables\Concerns\InteractsWithTable;
use Filament\Widgets\Concerns\InteractsWithPageTable;
use Filament\Widgets\StatsOverviewWidget as BaseWidget;
use Filament\Widgets\StatsOverviewWidget\Stat;

class CandidatesPaymentState extends BaseWidget
{
    use InteractsWithPageTable;

    protected function getStats(): array
    {
        $query = Payment::where('institute_id', Filament::getTenant()->id)->get();

        $total = 0;
        //$totalDue = $query->where('status', '!=', 'paid')->sum('amount');
        $totalPaid = 0;

        foreach ($query as $payment) {
            $total = $total + $payment->amount;
            if ($payment->status == 'approved') {
                $totalPaid = $totalPaid + $payment->amount;
            }
        }

        return [
            Stat::make('Total paid', $totalPaid),
            //Stat::make('Total due', $totalDue),
            Stat::make('Total', $total)
        ];
    }

    protected function getTablePage(): string
    {
        return ListCandidates::class;
    }
}
