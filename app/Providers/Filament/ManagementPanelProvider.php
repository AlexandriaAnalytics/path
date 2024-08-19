<?php

namespace App\Providers\Filament;

use App\Filament\Management\Pages\Tenancy\EditInstituteProfile;
use App\Models\Institute;
use Filament\Facades\Filament;
use Filament\Http\Middleware\Authenticate;
use Filament\Http\Middleware\DisableBladeIconComponents;
use Filament\Http\Middleware\DispatchServingFilamentEvent;
use Filament\Navigation\MenuItem;
use Filament\Navigation\NavigationItem;
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

class ManagementPanelProvider extends PanelProvider
{
    public function panel(Panel $panel): Panel
    {
        return $panel
            ->default()
            ->id('management')
            ->path('management')
            //->brandLogo(asset('images/logo/01-regular.png'))
            ->brandLogoHeight('4rem')
            ->login(
                \App\Filament\Management\Pages\Auth\Login::class
            )
            ->renderHook(
                'panels::auth.login.form.after',
                fn(): View => view('filament.management.login_management')
            )
            ->colors([
                'primary' => '#22526d',
            ])
            ->discoverResources(in: app_path('Filament/Management/Resources'), for: 'App\\Filament\\Management\\Resources')
            ->discoverPages(in: app_path('Filament/Management/Pages'), for: 'App\\Filament\\Management\\Pages')
            ->pages([
                Pages\Dashboard::class,
            ])
            ->discoverWidgets(in: app_path('Filament/Management/Widgets'), for: 'App\\Filament\\Management\\Widgets')
            ->widgets([
                // Widgets\AccountWidget::class,
                // Widgets\FilamentInfoWidget::class,
            ])
            ->navigationItems([
                NavigationItem::make()
                    ->label('My files')
                    ->icon('heroicon-o-folder')
                    ->url(fn() => Filament::getTenant()->files_url, shouldOpenInNewTab: true)
                    ->visible(fn() => Filament::getTenant()->files_url != null),
                NavigationItem::make()
                    ->label('General files')
                    ->icon('heroicon-o-folder')
                    ->url(fn() => Filament::getTenant()->instituteType->files_url, shouldOpenInNewTab: true)
                    ->visible(fn() => Filament::getTenant()->instituteType->files_url != null),
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
            ])
            ->tenant(Institute::class)
            ->tenantProfile(EditInstituteProfile::class)
            ->databaseNotifications();
    }
}
