<?php

namespace App\Filament\Management\Resources\CandidateResource\Widgets;

use App\Models\Candidate;
use Filament\Widgets\StatsOverviewWidget as BaseWidget;
use Filament\Widgets\StatsOverviewWidget\Stat;

class CandidatesPaymentState extends BaseWidget
{
    protected function getStats(): array
    {
        $total = Candidate::all()->sum('total_amount');
        $totalPaid = Candidate::where('status', 'paid')->get()->sum('total_amount');
        $totalDue = Candidate::where('status', '!=', 'paid')->get()->sum('total_amount');
        return [
            Stat::make('Total Paid', $totalPaid),
            Stat::make('Total due', $totalDue),
            Stat::make('Total', $total)
        ];
    }
}
