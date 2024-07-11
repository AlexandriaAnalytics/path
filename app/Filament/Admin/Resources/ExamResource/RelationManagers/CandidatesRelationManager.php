<?php

namespace App\Filament\Admin\Resources\ExamResource\RelationManagers;

use App\Filament\Admin\Resources\CandidateResource;
use App\Models\Candidate;
use App\Models\ExamModule;
use Filament\Forms\Components\Select;
use Filament\Resources\RelationManagers\RelationManager;
use Filament\Tables\Actions\CreateAction;
use Filament\Tables\Actions\DeleteAction;
use Filament\Tables\Actions\ViewAction;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\DB;

class CandidatesRelationManager extends RelationManager
{
    protected static string $relationship = 'candidates';

    public function table(Table $table): Table
    {
        return $table
        ->modifyQueryUsing(fn () => Candidate::distinct()->whereHas('exams', fn ($query) => $query->where('exam_id', $this->getOwnerRecord()->id)))
            ->columns([
                ...CandidateResource::getCandidateColumns(),
                ...CandidateResource::getInstituteColumns(),
                ...CandidateResource::getStudentColumns(),
            ])
            ->headerActions([
                CreateAction::make()
                    ->createAnother(false)
                    ->form([
                        ...CandidateResource::getStudentFields(),
                        Select::make('modules')
                            ->multiple()
                            ->required()
                            ->live()
                            ->relationship(name: 'modules', titleAttribute: 'name')
                            ->options(function (callable $get) {
                                $examId = $get('exam_id');

                                if (!$examId) {
                                    return [];
                                }
                                return ExamModule::query()
                                    ->whereExamId($examId)
                                    ->join('modules', 'modules.id', '=', 'exam_module.module_id')
                                    ->pluck('modules.name', 'modules.id');
                            })
                            ->preload(),
                    ]),
            ])
            ->actions([
                ViewAction::make()
                    ->url(fn (Model $record) => CandidateResource::getUrl('view', [$record])),
                DeleteAction::make(),
            ])
            ->filters([
                SelectFilter::make('institute_id')
                    ->label('Member or centre')
                    ->relationship('student.institute', 'name')
                    ->searchable()
                    ->multiple()
                    ->preload(),
            ]);
    }
}
