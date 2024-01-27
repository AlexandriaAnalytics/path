<?php

namespace App\Filament\Admin\Resources\CandidateResource\Pages;

use App\Filament\Admin\Resources\CandidateResource;
use App\Models\Candidate;
use App\Models\CandidateModule;
use App\Models\ExamSession;
use Filament\Actions;
use Filament\Actions\Action;
use Filament\Forms\Components\Select;
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
            Action::make('Assign exam session')
                ->form([
                    Select::make('module')
                        ->required()
                        ->live()
                        ->options(function (Candidate $record) {
                            $candidateId = $record->getKey();

                            if (!$candidateId) {
                                return [];
                            }
                            return CandidateModule::query()
                                ->whereCandidateId($candidateId)
                                ->join('modules', 'modules.id', '=', 'candidate_module.module_id')
                                ->pluck('modules.name', 'modules.id');
                        })
                        ->preload()
                        ->afterStateUpdated(fn (callable $set) => $set('examsession_id', null)),
                    Select::make('examsession_id')
                        ->label('Exam Session')
                        ->options(function (callable $get) {
                            $moduleId = $get('module');

                            if (!$moduleId) {
                                return [];
                            }

                            return ExamSession::query()
                                ->where('module_id', $moduleId)
                                ->pluck('session_name', 'id');
                        })
                        ->required(),
                ])
                ->action(function (array $data, Candidate $record): void {
                    $candidateId = $record->getKey();
                    $examSessionId = $data['examsession_id'];
                    $examSession = ExamSession::findOrFail($examSessionId);
                    $record->examSessions()->attach($examSession, ['candidate_id' => $candidateId, 'examsession_id' => $examSessionId]);

                    $record->save();
                }),
        ];
    }
}
