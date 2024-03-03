<?php

namespace App\Filament\Candidate\Widgets;

use Filament\Tables\Table;
use Filament\Widgets\StatsOverviewWidget as BaseWidget;
use Filament\Widgets\StatsOverviewWidget\Stat;
use Filament\Widgets\TableWidget;
use App\Models\Candidate;

class WidgetCandidate extends BaseWidget
{
    protected function getStats(): array
    {
        $candidate =  Candidate::find(session('candidate')->id);
        
        return [ // i want to show the candidate number here from session variable call candidate
            Stat::make('Candidate Number', $candidate->id),
            Stat::make('Student name', $candidate->student->full_name),
            Stat::make('Payment status', $candidate->status),
            
            Stat::make('Modules', function () use ($candidate) {
                $concatenatedNames = $candidate->modules->reduce(function ($carry, $module) {
                    return $carry . $module->name . ' ';
                }, '');
                return $concatenatedNames;
            }),
            
            Stat::make('Exam session', '2021-10-10'), // i want to show the exam date here from session variable call candidate


            /*
            Stat::make('Exam Time', '10:00 AM'),
            Stat::make('Exam Duration', '2 Hours'),
            Stat::make('Exam Venue', 'Kathmandu'),
            */

        ];
    }
}
