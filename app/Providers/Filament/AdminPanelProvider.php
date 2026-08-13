<?php

namespace App\Providers\Filament;

use App\Filament\Pages\Auth\EditProfile;
use App\Services\OrganizationContext;
use BezhanSalleh\FilamentShield\FilamentShieldPlugin;
use DiscoveryDesign\FilamentGaze\FilamentGazePlugin;
use Filament\Http\Middleware\Authenticate;
use Filament\Http\Middleware\DisableBladeIconComponents;
use Filament\Http\Middleware\DispatchServingFilamentEvent;
use Filament\Pages;
use Filament\Panel;
use Filament\PanelProvider;
use Filament\Support\Colors\Color;
use Filament\View\PanelsRenderHook;
use Filament\Widgets;
use Illuminate\Cookie\Middleware\AddQueuedCookiesToResponse;
use Illuminate\Cookie\Middleware\EncryptCookies;
use Illuminate\Foundation\Http\Middleware\PreventRequestForgery;
use Illuminate\Routing\Middleware\SubstituteBindings;
use Illuminate\Session\Middleware\AuthenticateSession;
use Illuminate\Session\Middleware\StartSession;
use Illuminate\View\Middleware\ShareErrorsFromSession;

class AdminPanelProvider extends PanelProvider
{
    public function panel(Panel $panel): Panel
    {
        return $panel
            ->default()
            ->id('admin')
            ->sidebarCollapsibleOnDesktop()
            ->path('')
            ->login()
            ->passwordReset()
            ->emailVerification()
            ->profile(EditProfile::class, isSimple: true)
            ->brandName(fn (): string => app(OrganizationContext::class)->appName())
            ->brandLogo(fn (): string => app(OrganizationContext::class)->logoUrl()
                ?: asset('img/summarylight.png'))
            ->darkModeBrandLogo(fn (): string => app(OrganizationContext::class)->logoUrl()
                ?: asset('img/summarydark.png'))
            ->brandLogoHeight('4rem')
            ->colors([
                'primary' => Color::Amber,
            ])
            ->renderHook(
                PanelsRenderHook::HEAD_END,
                fn () => view('filament.styles.signature-theme'),
            )
            ->discoverResources(in: app_path('Filament/Resources'), for: 'App\\Filament\\Resources')
            ->favicon(fn (): string => app(OrganizationContext::class)->faviconUrl()
                ?: asset('img/logoweb.png'))
            ->discoverPages(in: app_path('Filament/Pages'), for: 'App\\Filament\\Pages')
            ->databaseNotifications()
            ->unsavedChangesAlerts()
            ->databaseTransactions()
            ->pages([
                // Pages\Dashboard::class,
            ])
            ->discoverWidgets(in: app_path('Filament/Widgets'), for: 'App\\Filament\\Widgets')
            ->widgets([
                // Widgets\AccountWidget::class,
                // Widgets\FilamentInfoWidget::class,
            ])
            ->middleware([
                EncryptCookies::class,
                AddQueuedCookiesToResponse::class,
                StartSession::class,
                AuthenticateSession::class,
                ShareErrorsFromSession::class,
                PreventRequestForgery::class,
                SubstituteBindings::class,
                DisableBladeIconComponents::class,
                DispatchServingFilamentEvent::class,
            ])
            ->authGuard('web')

            ->plugins([
                FilamentShieldPlugin::make(),
                FilamentGazePlugin::make(),

            ])
            ->userMenuItems([

            ])
            // ->domain('http://127.0.0.1:8000')
            ->authMiddleware([
                Authenticate::class,
            ]);
    }
}
