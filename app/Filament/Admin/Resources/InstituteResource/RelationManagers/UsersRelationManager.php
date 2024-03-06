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
                Tables\Actions\Action::make('impersonate')
                    ->label('Impersonate')
                    ->icon('heroicon-o-finger-print')
                    ->requiresConfirmation()
                    ->modalHeading('Log in as this user?')
                    ->modalDescription('Are you sure you want to log in as this user? You will be logged out of your current session.')
                    ->modalSubmitActionLabel('Yes, I am sure')
                    ->action(function (User $user) {
                        // Save the current user's ID to the session so we can log them back in later.
                        session()->put('impersonator_id', auth()->id());

                        auth()->login($user);

                        return redirect()
                            ->to(route('filament.management.auth.login'));
                    }),
            ])
            ->paginated([5, 10, 25])
            ->defaultPaginationPageOption(5);
    }
}
