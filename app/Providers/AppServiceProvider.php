<?php

namespace App\Providers;

use Illuminate\Support\ServiceProvider;
use Filament\Panel;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        //
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        //
    }


    public function panel(Panel $panel): Panel
    {
        return $panel
            ->default()
            ->id('admin')
            ->path('admin')
            ->brandName('My Admin')
            ->favicon(asset('favicon.ico'));
    }
}
