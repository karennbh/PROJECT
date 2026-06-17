<?php

namespace App\Providers;

use Illuminate\Support\ServiceProvider;
use Illuminate\Support\Facades\URL;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        $this->app->bind(
            \Filament\GlobalSearch\Contracts\GlobalSearchProvider::class,
            \App\Providers\Filament\AppGlobalSearchProvider::class
        );

        $this->app->bind(
            \Filament\Commands\MakeUserCommand::class,
            \App\Console\Commands\MakeFilamentUserCommand::class
        );

        $this->app->bind(
            \Filament\Auth\Http\Responses\Contracts\LogoutResponse::class,
            \App\Filament\Auth\LogoutResponse::class
        );
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        $host = request()->getHost();

    if ($host && str_contains($host, 'ngrok')) {
        URL::forceScheme('https');
    }
    }
}
