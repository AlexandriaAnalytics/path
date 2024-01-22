<?php

namespace App\Filament\Admin\Resources\ExamResource\RelationManagers;

use App\Filament\Admin\Resources\StudentResource;
use Filament\Forms\Form;
use Filament\Resources\RelationManagers\RelationManager;
use Filament\Tables\Table;

class StudentsRelationManager extends RelationManager
{
    protected static string $relationship = 'students';

    public function form(Form $form): Form
    {
        return StudentResource::form($form);
    }

    public function table(Table $table): Table
    {
        return StudentResource::table($table);
    }
}
