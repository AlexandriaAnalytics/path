<?php

namespace App\Filament\Admin\Resources\ExamResource\RelationManagers;

use App\Filament\Admin\Resources\StudentResource;
use App\Models\Student;
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

class StudentsRelationManager extends RelationManager
{
    protected static string $relationship = 'students';

    public function form(Form $form): Form
    {
        return StudentResource::form($form);
    }

    public function table(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('pivot.id')
                    ->label('Candidate No.')
                    ->searchable()
                    ->sortable()
                    ->alignStart(),
                ColumnGroup::make('Student', [
                    TextColumn::make('first_name')
                        ->searchable()
                        ->sortable()
                        ->alignStart(),
                    TextColumn::make('last_name')
                        ->searchable()
                        ->sortable(),
                ]),
                ColumnGroup::make('Institute', [
                    TextColumn::make('institute.name')
                        ->label('Name')
                        ->searchable()
                        ->sortable(),
                ]),

            ])
            ->heading('Candidates')
            ->headerActions([
                AttachAction::make()
                    ->label('Add Candidate')
                    ->recordTitle(fn (Student $record) => $record->first_name . ' ' . $record->last_name)
                    ->recordSelectSearchColumns(['first_name', 'last_name'])
                    ->preloadRecordSelect()
                    ->form(fn (AttachAction $action): array => [
                        $action->getRecordSelect(),
                        Select::make('modules')
                            ->multiple()
                            ->required()
                            ->options(
                                $this
                                    ->getOwnerRecord()
                                    ->modules
                                    ->flatMap(fn ($module) => [
                                        $module['type']->value => "{$module['type']->getLabel()} (\${$module['price']})"
                                    ]),
                            ),
                    ]),
            ])
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
