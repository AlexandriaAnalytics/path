<?php

namespace App\Filament\Resources\ExamResource\RelationManagers;

use App\Filament\Resources\StudentResource;
use App\Models\Student;
use Filament\Facades\Filament;
use Filament\Forms;
use Filament\Forms\Components\Select;
use Filament\Forms\Form;
use Filament\Notifications\Notification;
use Filament\Resources\RelationManagers\RelationManager;
use Filament\Tables;
use Filament\Tables\Actions\AttachAction;
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
            /*->recordTitle(fn (Student $record): string => $record->first_name . ' ' . $record->surnames)
            ->modifyQueryUsing(fn (Builder $query) => $query->whereBelongsTo(Filament::getTenant()))
            ->headerActions([
                Tables\Actions\AttachAction::make()
                    ->recordSelectOptionsQuery(fn (Builder $query) => $query->where('status', 1))
                    ->label('Add candidates')
                    ->recordSelect(
                        fn (Select $select) => $select
                            ->placeholder('Select a student')
                            ->multiple()
                            ->options(
                                Student::where('status', 1)->get()->mapWithKeys(function ($student) {
                                    return [$student->id => $student->first_name . ' ' . $student->surnames];
                                })
                            )
                    )

            ])
            ->actions([
                Tables\Actions\ViewAction::make(),
                Tables\Actions\DetachAction::make(),
            ])*/;
    }

    public function isReadOnly(): bool
    {
        return false;
    }
}
