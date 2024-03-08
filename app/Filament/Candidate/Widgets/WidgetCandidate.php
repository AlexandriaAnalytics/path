<?php

namespace App\Filament\Candidate\Widgets;

use Filament\Tables\Table;
use Filament\Widgets\StatsOverviewWidget as BaseWidget;
use Filament\Widgets\StatsOverviewWidget\Stat;
use Filament\Widgets\TableWidget;
use App\Models\Candidate;
use App\Models\Module;
use Filament\Tables\Columns\TextColumn;
use Illuminate\Contracts\View\View;

class WidgetCandidate extends BaseWidget
{

    protected static bool $isLazy = false;
    protected function getStats(): array
    {
        $candidate =  Candidate::find(session('candidate')->id);

        return [ // i want to show the candidate number here from session variable call candidate
            Stat::make('Candidate number', $candidate->id),
            Stat::make('Student name', $candidate->student->full_name),
            Stat::make('Payment status', $candidate->status),

            /* Stat::make('Modules', function () use ($candidate) {
                $modules = $candidate->modules->reduce(function ($carry, $module) {
                    return $carry . $module->name . ' ';
                }, '');
                return $modules;
            }),

            Stat::make('Exam session', function () use ($candidate) {
                $sessions = '';
                $exams = $candidate->exams;
                foreach($exams as $exam) {
                    $sessions .= Module::find($exam->pivot->module_id)->name . ": " . $exam->session_name;
                }
                if ($exams) {
                return $sessions;
                }
                return 'No exam session assigned';
            }), */


            /*
            Stat::make('Exam Time', '10:00 AM'),
            Stat::make('Exam Duration', '2 Hours'),
            Stat::make('Exam Venue', 'Kathmandu'),
            */

        ];
    }
}
