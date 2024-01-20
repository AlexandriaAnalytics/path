<?php

namespace App\Filament\Pages\Auth;

use Filament\Actions\Action;
use Filament\Pages\Auth\Login as BaseLogin;

class Login extends BaseLogin
{
    protected function getFormActions(): array
    {
        return [
            $this->getAuthenticateFormAction(),
            Action::make('admin.auth.login')
                ->label('Go to Admin Login')
                ->url(fn (): string => route('filament.admin.auth.login')),
        ];
    }
}
