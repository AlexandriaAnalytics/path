<?php

namespace App\Filament\Admin\Resources\UserResource\RelationManagers;

use App\Filament\Admin\Resources\InstituteResource;
use App\Models\Institute;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\RelationManagers\RelationManager;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\SoftDeletingScope;

class InstitutesRelationManager extends RelationManager
{
    protected static string $relationship = 'institutes';

    public function form(Form $form): Form
    {
        return InstituteResource::form($form);
    }

    public function table(Table $table): Table
    {
        return InstituteResource::table($table)
            ->description('Institutions managed by this user')
            ->actions([
                Tables\Actions\ViewAction::make()
                    ->url(fn (Institute $record) => InstituteResource::getUrl('view', [$record])),
            ]);
    }
}
