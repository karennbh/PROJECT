<?php

namespace App\Providers\Filament;

use Filament\Actions\Action;
use Filament\Actions\EditAction;
use Filament\Actions\ViewAction;
use Filament\Http\Middleware\Authenticate;
use Filament\Http\Middleware\AuthenticateSession;
use Filament\Http\Middleware\DisableBladeIconComponents;
use Filament\Http\Middleware\DispatchServingFilamentEvent;
use Filament\Pages\Dashboard;
use Filament\Panel;
use Filament\PanelProvider;
use Filament\Support\Colors\Color;
use Filament\Tables\Table;
use Illuminate\Cookie\Middleware\AddQueuedCookiesToResponse;
use Illuminate\Cookie\Middleware\EncryptCookies;
use Illuminate\Foundation\Http\Middleware\VerifyCsrfToken;
use Illuminate\Routing\Middleware\SubstituteBindings;
use Illuminate\Session\Middleware\StartSession;
use Illuminate\View\Middleware\ShareErrorsFromSession;
use Filament\FontProviders\GoogleFontProvider;
use Filament\Enums\ThemeMode;
use Filament\View\PanelsRenderHook;

class AdminPanelProvider extends PanelProvider
{
    public function panel(Panel $panel): Panel
    {
        return $panel
            ->default()
            ->id('admin')
            ->path('admin')
            ->authGuard('web')
            
            // ============================================
            // BRANDING
            // ============================================
            ->brandName('COESMART EV')
            ->brandLogo(asset('assets/logo.png'))
            ->brandLogoHeight('2.5rem')
            ->favicon(asset('assets/logo.png'))
            
            // ============================================
            // CUSTOM THEME CSS
            // ============================================
            ->viteTheme('resources/css/filament/admin/theme.css')
            
            // ============================================
            // COLOR SCHEME - Bright Cyan & Deep Blue
            // ============================================
            ->colors([
                // Primary: Bright Cyan (untuk tombol utama)
                'primary' => [
                    50  => '#ecfeff',  // Lightest cyan
                    100 => '#cffafe',  // Very light cyan
                    200 => '#a5f3fc',  // Light cyan
                    300 => '#67e8f9',  // Bright cyan
                    400 => '#22d3ee',  // Cyan
                    500 => '#06b6d4',  // Base cyan
                    600 => '#0891b2',  // Dark cyan
                    700 => '#0e7490',  // Darker cyan
                    800 => '#155e75',  // Very dark cyan
                    900 => '#164e63',  // Darkest cyan
                    950 => '#083344',  // Ultra dark cyan
                ],
                
                // Gray: Slate (untuk sidebar & text)
                'gray' => Color::Slate,
                
                // Info: Cyan (untuk badges & info elements)
                'info' => Color::Cyan,
                
                // Success: Emerald (untuk tombol sukses & badges)
                'success' => Color::Emerald,
                
                // Warning: Amber (untuk badges warning)
                'warning' => Color::Amber,
                
                // Danger: Rose (untuk tombol hapus)
                'danger' => Color::Rose,
            ])
            
            // ============================================
            // TYPOGRAPHY
            // ============================================
            ->font('Plus Jakarta Sans', provider: GoogleFontProvider::class)
            
            // ============================================
            // LAYOUT & NAVIGATION
            // ============================================
            ->sidebarCollapsibleOnDesktop()
            ->sidebarFullyCollapsibleOnDesktop()
            ->maxContentWidth('full')
            
            // ============================================
            // FEATURES
            // ============================================
            // Database Notifications (uncomment setelah migrate)
            // ->databaseNotifications()
            // ->databaseNotificationsPolling('30s')
            
            // Unsaved changes ditangani oleh custom modal biru di navigation-guard.blade.php.
            
            // ============================================
            // THEME MODE
            // ============================================
            ->defaultThemeMode(ThemeMode::Light)
            ->darkMode()
            
            // --- RESOURCE & PAGES ---
            ->discoverResources(in: app_path('Filament/Admin/Resources'), for: 'App\\Filament\\Admin\\Resources')
            ->discoverPages(in: app_path('Filament/Admin/Pages'), for: 'App\\Filament\\Admin\\Pages')
            ->pages([
                \App\Filament\Admin\Pages\Dashboard::class,
            ])
            ->discoverWidgets(in: app_path('Filament/Admin/Widgets'), for: 'App\\Filament\\Admin\\Widgets')
            ->widgets([])
            
            // --- NAVIGASI ---
            ->navigationGroups([
                'Master Data',
                'Transaksi',
                'Pengajuan',
                'Laporan',
                'Pengaturan',
            ])

            
            // ============================================
            // MIDDLEWARE
            // ============================================
            ->middleware([
                 EncryptCookies::class,
                AddQueuedCookiesToResponse::class,
                StartSession::class,
                AuthenticateSession::class,
                \App\Http\Middleware\EnforceSessionIdleTimeout::class,
                ShareErrorsFromSession::class,
                VerifyCsrfToken::class,
                SubstituteBindings::class,
                DisableBladeIconComponents::class,
                DispatchServingFilamentEvent::class,
            ])
            ->authMiddleware([
                Authenticate::class,
            ])
            
            // ============================================
            // ADDITIONAL CONFIGURATION
            // ============================================
            ->bootUsing(function (): void {
                EditAction::configureUsing(function (EditAction $action): void {
                    $action->label('Edit');
                });

                ViewAction::configureUsing(function (ViewAction $action): void {
                    $action->color('primary');
                });

                Table::configureUsing(function (Table $table): void {
                    $table->filtersApplyAction(
                        fn (Action $action): Action => $action->close()
                    );
                });
            })
            // ->login()
            // ->registration()
            // ->passwordReset()
            // ->emailVerification()
            // ->profile()
            
            // Global Search (Provider Custom dan Pindah ke Sidebar)
            ->globalSearch(
                provider: \App\Providers\Filament\AppGlobalSearchProvider::class,
                position: \Filament\Enums\GlobalSearchPosition::Sidebar
            )
            ->login(\App\Filament\Auth\Login::class)
            // ->globalSearchKeyBindings(['command+k', 'ctrl+k'])
            
            // Render Hooks (optional untuk custom content)
            ->renderHook(
                PanelsRenderHook::BODY_END,
                fn (): \Illuminate\Contracts\View\View => view('partials.tab-auth-guard')
            )
            ->renderHook(
                PanelsRenderHook::BODY_END,
                fn (): \Illuminate\Contracts\View\View => view('partials.feature-back-guard', [
                    'fallbackUrl' => route('filament.admin.pages.dashboard'),
                ])
            )
            ->renderHook(
                PanelsRenderHook::BODY_END,
                fn (): \Illuminate\Contracts\View\View => view()->file(resource_path('views/filament/admin/hooks/navigation-guard.blade.php'))
            )
            ->renderHook(
                PanelsRenderHook::BODY_END,
                fn (): \Illuminate\Contracts\View\View => view('partials.admin-session-timeout', [
                    'timeoutMs' => (int) config('session.lifetime', 45) * 60 * 1000,
                    'logoutUrl' => route('logout'),
                    'loginUrl' => route('login'),
                ])
            )
            ;
    }
}
