<?php

namespace App\Filament\Management\Resources;

use App\Filament\Admin\Resources\ExamResource as AdminExamResource;
use App\Filament\Management\Resources\ExamResource\Pages;
use App\Filament\Management\Resources\ExamResource\RelationManagers;
use App\Models\Exam;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\SoftDeletingScope;

class ExamResource extends Resource
{
    protected static ?string $model = Exam::class;

    protected static ?string $navigationGroup = 'Exam management';

    protected static ?string $navigationIcon = 'heroicon-o-book-open';

    protected static ?string $navigationLabel = 'Exam sessions';

    protected static bool $hasTitleCaseModelLabel = false;

    protected static ?string $modelLabel = 'Exam session';

    protected static ?string $pluralModelLabel = 'Exam sessions';

    protected static ?int $navigationSort = 2;

    public static function form(Form $form): Form
    {
        return AdminExamResource::form($form);
    }

    public static function table(Table $table): Table
    {
        return AdminExamResource::table($table)
            ->actions([
                Tables\Actions\ViewAction::make(),
            ]);
    }

    public static function getRelations(): array
    {
        return [
            RelationManagers\CandidatesRelationManager::class,
        ];
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListExams::route('/'),
            'view' => Pages\ViewExam::route('/{record}'),
        ];
    }

    public static function getEloquentQuery(): Builder
    {
        return Exam::query()
            ->withoutGlobalScopes([
                SoftDeletingScope::class,
            ]);
    }
}
