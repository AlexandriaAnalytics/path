<?php

namespace App\Filament\Admin\Resources\CandidateResource\Pages;

use App\Filament\Admin\Resources\CandidateResource;
use Filament\Actions;
use Filament\Infolists\Components\Fieldset;
use Filament\Infolists\Components\TextEntry;
use Filament\Infolists\Infolist;
use Filament\Resources\Pages\ViewRecord;
use Illuminate\Database\Eloquent\Model;

class ViewCandidate extends ViewRecord
{
    protected static string $resource = CandidateResource::class;

    public function infolist(Infolist $infolist): Infolist
    {
        return $infolist
            ->schema([
                Fieldset::make('Candidate')
                    ->schema([
                        TextEntry::make('id')
                            ->label('No.')
                            ->numeric(),
                        TextEntry::make('modules.name')
                            ->label('Modules'),
                    ]),
                Fieldset::make('Student')
                    ->relationship('student')
                    ->schema([
                        TextEntry::make('first_name')
                            ->label('First Name'),
                        TextEntry::make('last_name')
                            ->label('Last Name'),
                        TextEntry::make('institute.name')
                            ->label('Institute'),
                        TextEntry::make('national_id')
                            ->label('National ID'),
                    ]),
                Fieldset::make('Exam')
                    ->relationship('exam')
                    ->schema([
                        TextEntry::make('session_name')
                            ->label('Session Name'),
                        TextEntry::make('scheduled_date')
                            ->label('Scheduled Date')
                            ->tooltip(fn (Model $record): string => $record->exam->scheduled_date)
                            ->date()
                            ->since(),
                    ]),
            ]);
    }

    protected function getHeaderActions(): array
    {
        return [
            Actions\DeleteAction::make(),
        ];
    }
}
