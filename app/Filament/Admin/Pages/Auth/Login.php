<?php

namespace App\Filament\Admin\Pages\Auth;

use Filament\Forms\Form;
use Filament\Pages\Auth\Login as BaseLogin;
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
}
