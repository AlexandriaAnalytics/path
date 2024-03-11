<?php

namespace App\Filament\Admin\Resources\CandidateResource\Pages;

use App\Filament\Admin\Resources\CandidateResource;
use App\Models\Candidate;
use App\Models\CandidateExam;
use App\Models\Exam;
use App\Models\Module;
use DateTime;
use Filament\Actions;
use Filament\Actions\Action;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
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
                            ->label('Member or centre'),
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
                        TextEntry::make('type')
                    ])
                    ->columnSpanFull()
                    ->grid(2),
                RepeatableEntry::make('concepts')
                    ->hidden(function () {
                        /** @var \App\Models\User $user */
                        $user = auth()->user();

                        return !$user->hasRole('Superadministrator');
                    })
                    ->columns(3)
                    ->schema([
                        TextEntry::make('description'),
                        TextEntry::make('currency'),
                        TextEntry::make('amount')
                            ->numeric(decimalPlaces: 2),
                    ])
                    ->columnSpanFull(),
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
            Actions\EditAction::make(),
            Actions\DeleteAction::make(),
            Action::make('Assign exam session')
                ->form([
                    Select::make('module_id')
                        ->label('Module')
                        ->placeholder('Select a module')
                        ->required()
                        ->native(false)
                        ->live()
                        ->multiple()
                        ->options(fn (Candidate $record) => $record->modules->pluck('name', 'id'))
                        ->preload()
                        ->afterStateUpdated(fn (callable $set) => $set('exam_id', null)),
                    Select::make('exam_id')
                        ->label('Exam session')
                        ->native(false)
                        ->options(
                            fn (callable $get, Candidate $record) =>
                            $get('module_id')
                                ? Exam::whereHas('modules', fn ($query) => $query->where('modules.id', $get('module_id')))
                                ->whereHas('levels', fn ($query) => $query->where('levels.id', $record->level_id))
                                ->whereDoesntHave('candidates', fn ($query) => $query->where('candidates.id', $record->id))
                                ->pluck('session_name', 'id')
                                : []
                        )
                        ->required(),
                ])
                ->action(function (Candidate $record, array $data) {
                    foreach ($data['module_id'] as $moduleId) {
                        if (CandidateExam::where(function ($query) use ($record, $data, $moduleId) {
                            $query->where('candidate_id', $record->id)
                                ->where('module_id', $moduleId)
                                ->exists();
                        })) {
                            CandidateExam::where(function ($query) use ($record, $data, $moduleId) {
                                $query->where('candidate_id', $record->id)
                                    ->where('module_id', $moduleId);
                            })->delete();
                        }
                        $record->exams()->attach([
                            $data['exam_id'] => [
                                'module_id' => $moduleId,
                            ],
                        ]);
                    }
                    $candidate = Candidate::find($record->id);
                    $payment_deadline = \Carbon\Carbon::now();
                    foreach ($candidate->exams as $exam) {
                        $payment_deadline = max($payment_deadline, $exam->payment_deadline);
                    }
                    $today = \Carbon\Carbon::now();
                    $intervalo = $today->diff($payment_deadline);
                    $meses_faltantes = $intervalo->m;
                    $candidate->installments = $meses_faltantes;
                    $candidate->save();
                }),
        ];
    }
}
