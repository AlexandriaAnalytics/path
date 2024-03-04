<?php

namespace App\Filament\Management\Resources\CandidateResource\Pages;

use App\Filament\Management\Resources\CandidateResource;
use App\Models\Candidate;
use App\Models\CandidateModule;
use App\Models\Exam;
use App\Models\Module;
use Filament\Actions;
use Filament\Actions\Action;
use Filament\Facades\Filament;
use Filament\Forms\Components\Select;
use Filament\Infolists\Components\Fieldset;
use Filament\Infolists\Components\RepeatableEntry;
use Filament\Infolists\Components\TextEntry;
use Filament\Infolists\Infolist;
use Filament\Resources\Pages\ViewRecord;

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
                        TextEntry::make('name'),
                        TextEntry::make('surname')
                            ->label('Surname'),
                        TextEntry::make('institute.name')
                            ->label('Institute'),
                    ]),
                TextEntry::make('level.name')
                    ->label('Exam'),
                RepeatableEntry::make('exams')
                    ->columns(3)
                    ->schema([
                        TextEntry::make('session_name')
                            ->label('Exam session'),
                        TextEntry::make('scheduled_date')
                            ->label('Scheduled date'),
                        TextEntry::make('pivot.module_id')
                            ->label('Module')
                            ->formatStateUsing(fn ($state) => Module::find($state)->name),
                    ])
                    ->columnSpanFull()
                    ->grid(2),
                RepeatableEntry::make('billed_concepts')
                    ->hidden(fn () => Filament::getTenant()->can_view_registration_fee)
                    ->columns(3)
                    ->schema([
                        TextEntry::make('concept')
                            ->label('Concept'),
                        TextEntry::make('currency')
                            ->label('Currency'),
                        TextEntry::make('amount')
                            ->label('Amount')
                            ->numeric(decimalPlaces: 2),
                    ])
                    ->columnSpanFull(),
                TextEntry::make('granted_discount')
                    ->label('Discount granted')
                    ->numeric()
                    ->formatStateUsing(fn ($state) => $state . '%'),
                TextEntry::make('total_amount')
                    ->label('Total amount')
                    ->numeric()
                    ->money(
                        currency: $this->record->billa
                    ),
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
                        ->afterStateUpdated(fn (callable $set) => $set('exam_id', null)),
                    Select::make('exam_id')
                        ->label('Exam session')
                        ->options(function (callable $get, Candidate $record) {
                            $moduleId = $get('module');
                            $levelId = $record->level_id;

                            if (!$moduleId) {
                                return [];
                            }

                            $exams = Exam::whereHas('modules', function ($query) use ($moduleId) {
                                $query->where('module_id', $moduleId);
                            })->whereHas('levels', function ($query) use ($levelId) {
                                $query->where('level_id', $levelId);
                            })->get();
                            return $exams->pluck('session_name', 'id');
                        })
                        ->required(),
                ])
                ->action(function (array $data, Candidate $record): void {
                    $candidateId = $record->getKey();
                    $examId = $data['exam_id'];
                    $moduleId = $data['module'];
                    $exam = Exam::findOrFail($examId);
                    $record->exams()->attach($exam, ['candidate_id' => $candidateId, 'exam_id' => $examId, 'module_id' => $moduleId]);

                    $record->save();
                }),
        ];
    }
}
