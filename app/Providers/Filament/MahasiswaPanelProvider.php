<?php

namespace App\Providers\Filament;

use Filament\Panel;
use Filament\PanelProvider;
use Filament\Pages\Dashboard;
use Filament\Support\Colors\Color;
use Filament\Widgets\AccountWidget;
use Filament\Support\Icons\Heroicon;
use Filament\Navigation\NavigationGroup;
use Filament\Widgets\FilamentInfoWidget;
use Filament\Http\Middleware\Authenticate;
use Illuminate\Session\Middleware\StartSession;
use Illuminate\Cookie\Middleware\EncryptCookies;
use Filament\Http\Middleware\AuthenticateSession;
use Illuminate\Routing\Middleware\SubstituteBindings;
use Illuminate\View\Middleware\ShareErrorsFromSession;
use Filament\Http\Middleware\DisableBladeIconComponents;
use Filament\Http\Middleware\DispatchServingFilamentEvent;
use Illuminate\Foundation\Http\Middleware\VerifyCsrfToken;
use Illuminate\Cookie\Middleware\AddQueuedCookiesToResponse;

class MahasiswaPanelProvider extends PanelProvider
{
    public function panel(Panel $panel): Panel
    {
        return $panel
            ->id('mahasiswa')
            ->path('mahasiswa')
            ->login(\App\Filament\Mahasiswa\Auth\Login::class)
            ->colors([
                'primary' => Color::Emerald,
            ])
            ->discoverResources(in: app_path('Filament/Mahasiswa/Resources'), for: 'App\Filament\Mahasiswa\Resources')
            ->discoverPages(in: app_path('Filament/Mahasiswa/Pages'), for: 'App\Filament\Mahasiswa\Pages')
            ->pages([
                Dashboard::class,
            ])
            ->discoverWidgets(in: app_path('Filament/Mahasiswa/Widgets'), for: 'App\Filament\Mahasiswa\Widgets')
            ->widgets([
                AccountWidget::class,
                FilamentInfoWidget::class,
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
                \App\Http\Middleware\RedirectIfWrongPanel::class,
                Authenticate::class,
            ])
            ->databaseNotifications()
            ->viteTheme('resources/css/filament/mahasiswa/theme.css')
            ->navigationGroups([
                NavigationGroup::make()
                    ->label('Perkuliahan')
                    ->icon(Heroicon::OutlinedBookOpen),
                NavigationGroup::make()
                    ->label('Layanan Akademik')
                    ->icon(Heroicon::OutlinedCalendar),
                NavigationGroup::make()
                    ->label('Keuangan')
                    ->icon(Heroicon::OutlinedBanknotes),
            ]);
    }
}
