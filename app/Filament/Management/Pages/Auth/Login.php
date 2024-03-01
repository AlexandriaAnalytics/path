<?php

namespace App\Filament\Management\Pages\Auth;

use Filament\Support\Colors\Color;

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
                ->url(route('filament.admin.auth.login'))
                ->button()->color(Color::Gray),

            Action::make('candidate')
                ->label('Go to Candidate Login')
                ->url(route('candidate.login'))
                ->link()->color('primary'),
        ];
    }
}
