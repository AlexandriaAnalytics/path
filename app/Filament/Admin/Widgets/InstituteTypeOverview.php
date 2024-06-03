<?php

namespace App\Filament\Admin\Widgets;

use App\Models\Change;
use App\Models\Exam;
use Filament\Widgets\StatsOverviewWidget as BaseWidget;
use Filament\Widgets\StatsOverviewWidget\Stat;
use App\Models\Institute;
use App\Models\Student;
use App\Models\User;

class InstituteTypeOverview extends BaseWidget
{
    protected function getStats(): array
    {
        return [
            Stat::make('Institutes', Institute::all()->count()),
            Stat::make('Exams', Exam::all()->count()),
            Stat::make('Students', Student::all()->count()),
            Stat::make('Pending changes', Change::where('status', 0)->count()),
            Stat::make('Users', User::all()->count())
        ];
    }
}
