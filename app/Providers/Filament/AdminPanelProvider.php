<?php

namespace App\Providers\Filament;
use Filament\Navigation\MenuItem;
use App\Filament\Resources\LoanResource;
use App\Filament\Resources\BorrowerResource;
use Filament\Navigation\NavigationItem;
use Filament\Navigation\NavigationBuilder;
use Filament\Navigation\NavigationGroup;
use Filament\Http\Middleware\Authenticate;
use Filament\Http\Middleware\DisableBladeIconComponents;
use Filament\Http\Middleware\DispatchServingFilamentEvent;
use Filament\Pages;
use App\Filament\Pages\Dashboard;
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
use BezhanSalleh\FilamentShield\FilamentShieldPlugin;
use Rmsramos\Activitylog\ActivitylogPlugin;
use App\Http\Middleware\CheckSubscriptionValidity;
use App\Http\Middleware\CheckProfileCompleteness;
use App\Filament\Pages\Auth\Register;
use App\Http\Middleware\EagerLoadUserPermissions;

class AdminPanelProvider extends PanelProvider
{
    public function panel(Panel $panel): Panel
    {
        return $panel
            ->id('admin')
            ->path('admin')
            ->plugins([])
            ->sidebarCollapsibleOnDesktop()
            ->login()
            ->registration(Register::class)
            ->passwordReset()
            ->emailVerification()
            ->profile()
            ->colors([
                'primary' => Color::Amber,
            ])
            // Resource discovery temporarily disabled due to memory threshold issues
            // Investigating: Even minimal resources cause 94% memory exhaustion
            // ->discoverResources(in: app_path('Filament/Resources'), for: 'App\\Filament\\Resources')
            ->resources([])
            ->discoverPages(in: app_path('Filament/Pages'), for: 'App\\Filament\\Pages')
            ->discoverWidgets(in: app_path('Filament/Widgets'), for: 'App\\Filament\\Widgets')
            ->widgets([])
            ->navigationItems([
                NavigationItem::make('Statement of Financial Position')
                    ->url('/admin/assets/statement-of-financial-position')
                    ->icon('heroicon-m-banknotes')
                    ->group('Accounting')
                    ->isActiveWhen(fn(): bool => request()->is('admin/assets/statement-of-financial-position'))
                    ->sort(4)
                    ->visible(fn(): bool => true),
                NavigationItem::make('Statement of Comprehensive Income')
                    ->url('/admin/assets/statement-of-comprehensive-income')
                    ->icon('heroicon-m-chart-bar')
                    ->group('Accounting')
                    ->isActiveWhen(fn(): bool => request()->is('admin/assets/statement-of-comprehensive-income'))
                    ->sort(5)
                    ->visible(fn(): bool => true),
                NavigationItem::make('Cash Flow')
                    ->url('/admin/loans/cash-flow-statement')
                    ->icon('heroicon-m-calculator')
                    ->group('Accounting')
                    ->isActiveWhen(fn(): bool => request()->is('admin/loans/cash-flow-statement'))
                    ->sort(6)
                    ->visible(fn(): bool => true),
                NavigationItem::make('Company Profile Completion')
                    ->url('/admin/profile-completion')
                    ->icon('heroicon-m-building-office')
                    ->group('User Management')
                    ->isActiveWhen(fn(): bool => request()->is('admin/profile-completion'))
                    ->sort(2)
                    ->visible(fn(): bool => true),
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
                // DISABLED: EagerLoadUserPermissions causes memory exhaustion with 401 permissions
                // EagerLoadUserPermissions::class,
            ])
            ->authMiddleware([
                Authenticate::class,
            ]);
    }
}
