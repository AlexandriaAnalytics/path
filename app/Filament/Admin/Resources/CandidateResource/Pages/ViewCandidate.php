<?php

namespace App\Filament\Admin\Resources\CandidateResource\Pages;

use App\Filament\Admin\Resources\CandidateResource;
use App\Models\Candidate;
use App\Models\Exam;
use App\Models\Module;
use Filament\Actions;
use Filament\Actions\Action;
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
                RepeatableEntry::make('billed_concepts')
                    ->hidden(function () {
                        /** @var \App\Models\User $user */
                        $user = auth()->user();

                        return !$user->hasRole('Superadministrator');
                    })
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
                ->disabled(fn (Candidate $record) => $record->pendingModules->isEmpty())
                ->form([
                    Select::make('module_id')
                        ->label('Module')
                        ->placeholder('Select a module')
                        ->required()
                        ->native(false)
                        ->live()
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
                                ->whereDoesntHave('candidates', fn ($query) => $query->where('candidates.id', $record->id))
                                ->pluck('session_name', 'id')
                                : []
                        )
                        ->required(),
                ])
                ->action(fn (Candidate $record, array $data) => $record
                    ->exams()
                    ->attach([
                        $data['exam_id'] => [
                            'module_id' => $data['module_id'],
                        ],
                    ])),
        ];
    }
}
