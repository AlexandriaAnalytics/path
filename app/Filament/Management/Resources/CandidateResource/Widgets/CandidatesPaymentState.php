<?php

namespace App\Filament\Management\Resources\CandidateResource\Widgets;

use App\Filament\Management\Resources\CandidateResource\Pages\ListCandidates;
use App\Models\Candidate;
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
        $query = Candidate::query()
            ->whereHas('student', fn ($query) => $query->where('institute_id', Filament::getTenant()->id))
            ->get()
            ->flatMap(fn (Candidate $candidate) => $candidate->concepts);

        $total = $query->sum('amount');
        $totalDue = $query->where('status', '!=', 'paid')->sum('amount');
        $totalPaid = $query->where('status', 'paid')->sum('amount');

        return [
            Stat::make('Total paid', $totalPaid),
            Stat::make('Total due', $totalDue),
            Stat::make('Total', $total)
        ];
    }

    protected function getTablePage(): string
    {
        return ListCandidates::class;
    }
}
