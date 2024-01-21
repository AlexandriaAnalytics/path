<?php

namespace App\Filament\Resources\ExamResource\RelationManagers;

use App\Filament\Resources\StudentResource;
use App\Models\Student;
use Filament\Facades\Filament;
use Filament\Forms;
use Filament\Forms\Form;
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
            ->headerActions([
                Tables\Actions\AttachAction::make()
                    ->label('Add Candidate')
                    ->recordSelectSearchColumns(['first_name', 'last_name'])
                    ->recordSelectOptionsQuery(fn (Builder $query) => $query->whereBelongsTo(Filament::getTenant())),
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
