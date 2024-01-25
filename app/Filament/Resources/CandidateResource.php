<?php

namespace App\Filament\Resources;

use App\Filament\Admin\Resources\CandidateResource as AdminCandidateResource;
use App\Filament\Resources\CandidateResource\Pages;
use App\Models\Candidate;
use Filament\Facades\Filament;
use Filament\Forms\Components\Fieldset;
use Filament\Forms\Components\Select;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;

class CandidateResource extends Resource
{
    protected static bool $isScopedToTenant = false;

    protected static ?string $model = Candidate::class;

    protected static ?string $navigationIcon = 'heroicon-o-user-group';

    protected static ?string $navigationGroup = 'Exam Management';

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Fieldset::make('Student')
                    ->schema([
                        Select::make('student_id')
                            ->relationship(
                                name: 'student',
                                titleAttribute: 'first_name',
                                modifyQueryUsing: fn (Builder $query) => $query->whereBelongsTo(Filament::getTenant()),
                            )
                            ->searchable()
                            ->preload()
                            ->required()
                    ]),
                ...AdminCandidateResource::getExamFields(),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                ...AdminCandidateResource::getCandidateColumns(),
                ...AdminCandidateResource::getStudentColumns(),
                ...AdminCandidateResource::getExamColumns(),
            ])
            ->filters([
                //
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
            'create' => Pages\CreateCandidate::route('/create'),
        ];
    }

    public static function getEloquentQuery(): Builder
    {
        return parent::getEloquentQuery()->whereHas('student', fn (Builder $query) => $query->whereBelongsTo(Filament::getTenant()));
    }
}
