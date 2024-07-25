<?php

namespace App\Filament\Admin\Resources\ExamResource\Widgets;

use App\Models\Exam;
use Filament\Widgets\StatsOverviewWidget as BaseWidget;
use Filament\Widgets\StatsOverviewWidget\Stat;

class StatsOverview extends BaseWidget
{
    protected static bool $isLazy = false;

    protected function getStats(): array
    {
        $exams = Exam::all();
        return [
            Stat::make('All', $exams->count()),
            Stat::make('Active', $exams->where('status', 'active')->count()),
            Stat::make('In review', $exams->where('status', 'in review')->count()),
            Stat::make('Closed', $exams->where('status', 'closed')->count()),
            Stat::make('Finished', $exams->where('status', 'finished')->count()),
            Stat::make('Archived', $exams->where('status', 'archived')->count()),
        ];
    }
}
