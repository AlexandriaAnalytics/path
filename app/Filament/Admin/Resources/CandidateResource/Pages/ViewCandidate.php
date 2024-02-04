<?php

namespace App\Filament\Admin\Resources\CandidateResource\Pages;

use App\Filament\Admin\Resources\CandidateResource;
use App\Filament\Resources\CandidateResource as ResourcesCandidateResource;
use App\Models\Candidate;
use App\Models\CandidateModule;
use App\Models\Exam;
use App\Models\ExamSession;
use App\Models\Module;
use Filament\Actions;
use Filament\Actions\Action;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Infolists\Components\Fieldset;
use Filament\Infolists\Components\RepeatableEntry;
use Filament\Infolists\Components\TextEntry;
use Filament\Infolists\Infolist;
use Filament\Resources\Pages\ViewRecord;
use Filament\Tables\Columns\Column;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;
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
                        TextEntry::make('names'),
                        TextEntry::make('surnames')
                            ->label('Last Name'),
                        TextEntry::make('institute.name')
                            ->label('Institute'),
                        TextEntry::make('national_id')
                            ->label('National ID'),
                    ]),
                RepeatableEntry::make('exam')
                    ->schema([
                        TextEntry::make('session_name')
                            ->label('Exam session'),
                        TextEntry::make('candidates')
                            ->formatStateUsing(function ($record) {
                                $moduleId = $record->pivot->module_id;
                                return Module::where('id', $moduleId)->value('name');
                            }),
                        TextEntry::make('scheduled_date')
                    ])
                    ->columnSpanFull()
                    ->grid(2)
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
                        ->label('Exam Session')
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
                    $record->exam()->attach($exam, ['candidate_id' => $candidateId, 'exam_id' => $examId, 'module_id' => $moduleId]);

                    $record->save();
                }),
        ];
    }
}
