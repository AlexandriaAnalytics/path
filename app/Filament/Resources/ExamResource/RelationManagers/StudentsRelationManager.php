<?php

namespace App\Filament\Resources\ExamResource\RelationManagers;

use App\Filament\Resources\StudentResource;
use App\Models\Student;
use Filament\Facades\Filament;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Notifications\Notification;
use Filament\Resources\RelationManagers\RelationManager;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\SoftDeletingScope;

class StudentsRelationManager extends RelationManager
{
    protected static string $relationship = 'students';

    protected static ?string $title = 'Candidates';

    public function form(Form $form): Form
    {
        return StudentResource::form($form);
    }

    public function table(Table $table): Table
    {
        return StudentResource::table($table)
            ->recordTitle(fn (Student $record): string => $record->first_name . ' ' . $record->last_name)
            ->modifyQueryUsing(fn (Builder $query) => $query->whereBelongsTo(Filament::getTenant()))
            ->headerActions([
                Tables\Actions\AttachAction::make()
                    ->label('Add Candidate')
                    ->recordSelectSearchColumns(['first_name', 'last_name'])
                    ->recordSelectOptionsQuery(fn (Builder $query) => $query->whereBelongsTo(Filament::getTenant()))
                    ->disabled(fn () => !Filament::getTenant()->can_add_candidates)
                    ->before(function (Tables\Actions\AttachAction $action, Student $record) {
                        if (!Filament::getTenant()->can_add_candidates) {
                            Notification::make()
                                ->warning()
                                ->title('This institute cannot add candidates to exams')
                                ->body('Please contact the system administrator.')
                                ->persistent()
                                ->send();

                            $action->cancel();
                        }

                        $studentAge = $record->birth_date->floatDiffInYears($this->getOwnerRecord()->scheduled_date);
                        if ($this->getOwnerRecord()->minimum_age > $studentAge) {
                            Notification::make()
                                ->warning()
                                ->title('This student is too young for this exam')
                                ->body('The minimum age for this exam is ' . $this->getOwnerRecord()->minimum_age . ' years old (at the time of the exam).')
                                ->persistent()
                                ->send();

                            $action->cancel();
                        }

                        if ($this->getOwnerRecord()->maximum_age < $studentAge) {
                            Notification::make()
                                ->warning()
                                ->title('This student is too old for this exam')
                                ->body('The maximum age for this exam is ' . $this->getOwnerRecord()->maximum_age . ' years old (at the time of the exam).')
                                ->persistent()
                                ->send();

                            $action->cancel();
                        }

                        if ($this->getOwnerRecord()->max_candidates <= $this->getOwnerRecord()->students()->count()) {
                            Notification::make()
                                ->warning()
                                ->title('This exam has reached its maximum number of candidates')
                                ->send();

                            $action->cancel();
                        }
                    })
                    ->attachAnother(false),
            ])
            ->actions([
                Tables\Actions\ViewAction::make(),
                Tables\Actions\DetachAction::make(),
            ]);
    }

    public function isReadOnly(): bool
    {
        return false;
    }
}
