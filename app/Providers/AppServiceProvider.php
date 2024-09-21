<?php

namespace App\Providers;

use Illuminate\Support\ServiceProvider;
use Livewire\Livewire;
use Filament\Facades\Filament;
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
        Livewire::component('order-total', \App\Http\Livewire\OrderTotal::class);
        Filament::registerPanel(
            Panel::make()
                ->id('agent')
                ->path('agent')
                ->login()
                ->authGuard('agent')
        );
    }

    
}
