<?php

namespace App\Filament\Admin\Resources\ExamResource\RelationManagers;

use App\Filament\Admin\Resources\CandidateResource;
use Filament\Forms\Components\Select;
use Filament\Resources\RelationManagers\RelationManager;
use Filament\Tables\Actions\CreateAction;
use Filament\Tables\Actions\DeleteAction;
use Filament\Tables\Actions\ViewAction;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Model;

class CandidatesRelationManager extends RelationManager
{
    protected static string $relationship = 'candidates';

    public function table(Table $table): Table
    {
        return $table
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
                            ->options(fn () => $this->getOwnerRecord()->modules->flatMap(fn ($module) => [$module['type']->value => "{$module['type']->getLabel()} (\${$module['price']})"])),
                    ]),
            ])
            ->actions([
                ViewAction::make()
                    ->url(fn (Model $record) => CandidateResource::getUrl('view', [$record])),
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
