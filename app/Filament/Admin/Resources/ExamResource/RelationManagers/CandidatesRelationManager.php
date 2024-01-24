<?php

namespace App\Filament\Admin\Resources\ExamResource\RelationManagers;

use App\Filament\Admin\Resources\CandidateResource;
use App\Filament\Admin\Resources\StudentResource;
use App\Models\Candidate;
use App\Models\Student;
use Filament\Actions\CreateAction;
use Filament\Forms\Components\Select;
use Filament\Forms\Form;
use Filament\Resources\RelationManagers\RelationManager;
use Filament\Tables\Actions;
use Filament\Tables\Actions\AttachAction;
use Filament\Tables\Columns\ColumnGroup;
use Filament\Tables\Columns\Layout\Panel;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;

class CandidatesRelationManager extends RelationManager
{
    protected static string $relationship = 'candidates';

    public function form(Form $form): Form
    {
        return CandidateResource::form($form);
    }

    public function table(Table $table): Table
    {
        return CandidateResource::table($table)
            ->heading('Candidates')
            ->filters([
                SelectFilter::make('institute_id')
                    ->label('Institute')
                    ->placeholder('All Institutes')
                    ->relationship('institute', 'name')
                    ->native(false)
                    ->preload()
                    ->multiple()
                    ->searchable(),
            ]);
    }
}
