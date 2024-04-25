<?php

namespace App\Filament\Management\Resources\CandidateResource\Pages;

use App\Filament\Management\Resources\CandidateResource;
use App\Models\Candidate;
use App\Models\CandidateExam;
use App\Models\CandidateModule;
use App\Models\Exam;
use App\Models\Module;
use App\Models\Payment;
use Carbon\Carbon;
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
                            ->label('Institution'),
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
                RepeatableEntry::make('concepts')
                    ->visible(
                        fn () =>
                        Filament::getTenant()->can_view_registration_fee

                            && Filament::getTenant()
                            ->candidates()
                            ->count() >= 30
                    )

                    ->columns(3)
                    ->schema([
                        TextEntry::make('description')
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
                TextEntry::make('payments')
                    ->formatStateUsing(function (Candidate $record) {
                        $payments = Payment::where('candidate_id', $record->id)->where('status', 'approved')->get();
                        $amount = 0;
                        foreach ($payments as $payment) {
                            $amount = $amount + $payment->amount;
                        }
                        return $amount;
                    }),
                TextEntry::make('installments')
                    ->default(fn ($record) => $record->installmentAttribute)
            ]);
    }

    protected function getHeaderActions(): array
    {
        return [

            Actions\DeleteAction::make()
                ->visible(fn (Candidate $candidate) => $candidate->status !== 'paid'),

            Action::make('Assign exam session')
                ->disabled(fn (Candidate $record) => $record->pendingModules->isEmpty())
                ->form([
                    Select::make('module_id')
                        ->label('Module')
                        ->placeholder('Select a module')
                        ->required()
                        ->native(false)
                        ->live()
                        ->multiple()
                        ->options(fn (Candidate $record) => $record->pendingModules->pluck('name', 'id'))
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
                    $candidate = Candidate::with('exams')->find($record->id);

                    $payment_deadline = $candidate
                        ->exams
                        ->min('payment_deadline');

                    $candidate->installments = max(
                        now()->diffInMonths(Carbon::parse($payment_deadline), absolute: false),
                        0,
                    ) + 1;

                    $candidate->save();
                }),
        ];
    }
}
