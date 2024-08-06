<?php

namespace App\Filament\Admin\Pages\Auth;

use Filament\Actions\Action;
use Filament\Forms\Form;
use Filament\Pages\Auth\Login as BaseLogin;
use Filament\Support\Colors\Color;
use Illuminate\Contracts\Support\Htmlable;

class Login extends BaseLogin
{

    // when you login, you will be redirected to the admin dashboard
    protected function redirectTo(): string
    {
        return route('filament.admin.dashboard');
    }

    public function getHeading(): string | Htmlable
    {
        return 'Admin Login';
    }

    protected function getFormActions(): array
    {
        return [
            $this->getAuthenticateFormAction(),
            Action::make('admin.auth.login')
                ->label('Go Associated Login')
                ->url(route('filament.management.auth.login'))
                ->button()->color(Color::Gray),

            Action::make('candidate')
                ->label('Go to Candidate Login')
                ->url('/candidates/login')
                ->link()->color('primary'),
        ];
    }
}
