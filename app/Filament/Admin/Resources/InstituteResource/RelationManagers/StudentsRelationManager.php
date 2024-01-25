<?php

namespace App\Filament\Admin\Resources\InstituteResource\RelationManagers;

use App\Filament\Admin\Resources\InstituteResource;
use App\Filament\Admin\Resources\StudentResource;
use App\Models\Student;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\RelationManagers\RelationManager;
use Filament\Tables;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\SoftDeletingScope;

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
                ...StudentResource::getStudentColumns(),
                TextColumn::make('created_at')
                    ->label('Registered at')
                    ->sortable()
                    ->description('Registered at')
                    ->date('Y-m-d H:i:s')
                    ->toggleable(isToggledHiddenByDefault: true),
            ])
            ->filters([
                //
            ])
            ->headerActions([
                Tables\Actions\CreateAction::make()
                    ->label('Create'),
            ])
            ->actions([
                Tables\Actions\DeleteAction::make(),
            ])
            ->bulkActions([
                Tables\Actions\BulkActionGroup::make([
                    Tables\Actions\DeleteBulkAction::make(),
                ]),
            ])
            ->paginated([5, 10, 25])
            ->defaultPaginationPageOption(5);
    }
}
