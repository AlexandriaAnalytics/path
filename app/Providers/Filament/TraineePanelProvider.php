<?php

namespace App\Providers\Filament;

use App\Filament\Trainee\Widgets\Files;
use Filament\Http\Middleware\Authenticate;
use Filament\Http\Middleware\DisableBladeIconComponents;
use Filament\Http\Middleware\DispatchServingFilamentEvent;
use Filament\Pages;
use Filament\Panel;
use Filament\PanelProvider;
use Filament\Support\Colors\Color;
use Filament\Widgets;
use Illuminate\Cookie\Middleware\AddQueuedCookiesToResponse;
use Illuminate\Cookie\Middleware\EncryptCookies;
use Illuminate\Foundation\Http\Middleware\VerifyCsrfToken;
use Illuminate\Routing\Middleware\SubstituteBindings;
use Illuminate\Session\Middleware\AuthenticateSession;
use Illuminate\Session\Middleware\StartSession;
use Illuminate\View\Middleware\ShareErrorsFromSession;
use Illuminate\View\View;

class TraineePanelProvider extends PanelProvider
{
    public function panel(Panel $panel): Panel
    {
        return $panel
            ->id('trainee')
            ->path('trainee')
            ->brandLogo(asset('images/logo/01-regular.png'))
            ->brandLogoHeight('4rem')
            ->login()
            ->renderHook(
                'panels::auth.login.form.after',
                fn (): View => view('filament.management.login_management')
            )
            ->colors([
                'primary' => '#22526d',
            ])
            ->discoverResources(in: app_path('Filament/Trainee/Resources'), for: 'App\\Filament\\Trainee\\Resources')
            ->discoverPages(in: app_path('Filament/Trainee/Pages'), for: 'App\\Filament\\Trainee\\Pages')
            ->pages([
                Pages\Dashboard::class,
            ])
            ->discoverWidgets(in: app_path('Filament/Trainee/Widgets'), for: 'App\\Filament\\Trainee\\Widgets')
            ->widgets([
                Widgets\AccountWidget::class,
            ])
            ->middleware([
                EncryptCookies::class,
                AddQueuedCookiesToResponse::class,
                StartSession::class,
                AuthenticateSession::class,
                ShareErrorsFromSession::class,
                VerifyCsrfToken::class,
                SubstituteBindings::class,
                DisableBladeIconComponents::class,
                DispatchServingFilamentEvent::class,
            ])
            ->authMiddleware([
                Authenticate::class,
            ]);
    }
}
