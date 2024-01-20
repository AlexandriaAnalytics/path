<?php

namespace App\Filament\Admin\Pages\Auth;

use Filament\Forms\Form;
use Filament\Pages\Auth\Login as BaseLogin;
use Illuminate\Contracts\Support\Htmlable;

class Login extends BaseLogin
{
    public function getHeading(): string | Htmlable
    {
        return 'Admin Login';
    }
}
