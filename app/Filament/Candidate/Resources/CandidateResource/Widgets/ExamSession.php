<?php

namespace App\Filament\Candidate\Resources\CandidateResource\Widgets;

use App\Models\Candidate;
use App\Models\CandidateExam;
use Filament\Tables;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;
use Filament\Widgets\TableWidget as BaseWidget;
use Illuminate\Contracts\Support\Htmlable;

class ExamSession extends BaseWidget
{
    public function table(Table $table): Table
    {
        return $table
            ->query(CandidateExam::query())
            ->heading('Exam session')
            ->columns([
                TextColumn::make('exam.session_name')
                    ->label('Session name'),
                TextColumn::make('module.name'),
                TextColumn::make('exam.type')
                    ->label('Type'),
                TextColumn::make('exam.scheduled_date')
                    ->label('Scheduled date')
                    ->date('d-m-Y h:m'),
                TextColumn::make('exam.location')
                    ->label('Location')
            ]);
    }
}
