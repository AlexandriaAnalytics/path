<?php

namespace App\Filament\Resources;

use App\Filament\Admin\Resources\CandidateResource as AdminCandidateResource;
use App\Filament\Resources\CandidateResource\Pages;
use App\Filament\Resources\CandidateResource\RelationManagers;
use App\Models\Candidate;
use Filament\Facades\Filament;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Support\Colors\Color;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\SoftDeletingScope;

class CandidateResource extends Resource
{
    protected static bool $isScopedToTenant = false;

    protected static ?string $model = Candidate::class;

    protected static ?string $navigationIcon = 'heroicon-o-rectangle-stack';

    protected static ?string $navigationGroup = 'Exam Management';

    protected static ?string $navigationParentItem = 'Exams';

    public static function form(Form $form): Form
    {
        return AdminCandidateResource::form($form);
    }

    public static function table(Table $table): Table
    {
        return AdminCandidateResource::table($table);
    }

    public static function getRelations(): array
    {
        return [
            //
        ];
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListCandidates::route('/'),
        ];
    }

    public static function getEloquentQuery(): Builder
    {
        return parent::getEloquentQuery()->whereHas('student', fn (Builder $query) => $query->whereBelongsTo(Filament::getTenant()));
    }
}
