<?php

namespace App\Filament\Resources\ExamResource\RelationManagers;

use App\Filament\Resources\CandidateResource;
use App\Models\ExamModule;
use Filament\Forms\Components\Select;
use Filament\Resources\RelationManagers\RelationManager;
use Filament\Tables\Actions\CreateAction;
use Filament\Tables\Actions\DeleteAction;
use Filament\Tables\Actions\ViewAction;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Model;
use App\Filament\Admin\Resources\CandidateResource as AdminCandidateResource;

class CandidatesRelationManager extends RelationManager
{
    protected static string $relationship = 'candidates';

    public function table(Table $table): Table
    {
        return $table
            ->columns([
                ...AdminCandidateResource::getCandidateColumns(),
                ...AdminCandidateResource::getInstituteColumns(),
                ...AdminCandidateResource::getStudentColumns(),
            ])
            ->headerActions([
                CreateAction::make()
                    ->createAnother(false)
                    ->form([
                        ...AdminCandidateResource::getStudentFields(),
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
                    ->url(fn (Model $record) => AdminCandidateResource::getUrl('view', [$record])),
                DeleteAction::make(),
            ])
            ->filters([
                SelectFilter::make('institute_id')
                    ->label('Institute')
                    ->relationship('student.institute', 'name')
                    ->searchable()
                    ->multiple()
                    ->preload(),
            ]);
    }
}
