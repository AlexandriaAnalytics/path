<?php

namespace App\Filament\Admin\Resources\InstituteResource\RelationManagers;

use App\Filament\Admin\Resources\CandidateResource;
use App\Models\Candidate;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\RelationManagers\RelationManager;
use Filament\Tables;
use Filament\Tables\Table;

class CandidatesRelationManager extends RelationManager
{
    protected static string $relationship = 'candidates';

    public function form(Form $form): Form
    {
        return CandidateResource::form($form);
    }

    public function table(Table $table): Table
    {
        return CandidateResource::table($table)

            ->headerActions([
                Tables\Actions\CreateAction::make(),
            ])
            ->actions([
                Tables\Actions\EditAction::make(),
                Tables\Actions\DeleteAction::make(),
            ])
            ->bulkActions([
                Tables\Actions\BulkActionGroup::make([
                    Tables\Actions\DeleteBulkAction::make(),
                ]),
            ]);
    }
}
