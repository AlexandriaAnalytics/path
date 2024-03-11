<?php

namespace App\Filament\Management\Resources\CandidateResource\Widgets;

use App\Models\Candidate;
use Filament\Facades\Filament;
use Filament\Widgets\StatsOverviewWidget as BaseWidget;
use Filament\Widgets\StatsOverviewWidget\Stat;

class CandidatesPaymentState extends BaseWidget
{
    protected function getStats(): array
    {
        $query = Filament::getTenant()
            ->candidates();

        $total = $query->sum('total_amount');
        $totalPaid = $query->where('status', 'paid')->sum('total_amount');
        $totalDue = $query->where('status', '!=', 'paid')->sum('total_amount');

        return [
            Stat::make('Total Paid', $totalPaid),
            Stat::make('Total due', $totalDue),
            Stat::make('Total', $total)
        ];
    }
}
