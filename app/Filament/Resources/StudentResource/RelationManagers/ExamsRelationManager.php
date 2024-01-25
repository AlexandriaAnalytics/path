<?php

namespace App\Filament\Resources\StudentResource\RelationManagers;

use App\Filament\Resources\ExamResource;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\RelationManagers\RelationManager;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\SoftDeletingScope;

class ExamsRelationManager extends RelationManager
{
    protected static string $relationship = 'exams';

    public function table(Table $table): Table
    {
        return ExamResource::table($table);
    }
}
