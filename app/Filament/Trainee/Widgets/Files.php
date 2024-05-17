<?php

namespace App\Filament\Trainee\Widgets;

use App\Models\Trainee;
use App\Models\User;
use Filament\Widgets\StatsOverviewWidget as BaseWidget;
use Filament\Widgets\StatsOverviewWidget\Stat;

class Files extends BaseWidget
{
    protected function getStats(): array
    {
        return [
            Stat::make('Files', Trainee::where('user_id', auth()->user()->id)->first()->files ? Trainee::where('user_id', auth()->user()->id)->first()->files : '-')
                ->url(Trainee::where('user_id', auth()->user()->id)->first()->files ? Trainee::where('user_id', auth()->user()->id)->first()->files : '-')
                ->extraAttributes([
                    'style' => 'width: 70vw; overflow: hidden;
                    white-space: nowrap; '
                ])
        ];
    }
}
