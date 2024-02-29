<?php

namespace App\Filament\Admin\Resources\InstituteResource\RelationManagers;

use App\Filament\Admin\Resources\UserResource;
use App\Models\User;
use Filament\Forms\Form;
use Filament\Resources\RelationManagers\RelationManager;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Model;

class UsersRelationManager extends RelationManager
{
    protected static string $relationship = 'users';

    public function form(Form $form): Form
    {
        return UserResource::form($form);
    }

    public function table(Table $table): Table
    {
        return UserResource::table($table)
            ->recordTitleAttribute('name')
            ->heading('Authorised users')
            ->description('Users authorised to access and manage this institute via the management portal')
            ->recordUrl(
                fn (Model $record): string => UserResource::getUrl('view', ['record' => $record]),
            )
            ->headerActions([
                Tables\Actions\AttachAction::make()
                    ->label('Add existing')
                    ->recordSelectSearchColumns(['name', 'email'])
                    ->after(fn () => $this->getOwnerRecord()->touch()),
                Tables\Actions\CreateAction::make()
                    ->label('Create')
                    ->after(fn () => $this->getOwnerRecord()->touch()),
            ])
            ->actions([
                Tables\Actions\DetachAction::make(),
            ])
            ->paginated([5, 10, 25])
            ->defaultPaginationPageOption(5);
    }
}
