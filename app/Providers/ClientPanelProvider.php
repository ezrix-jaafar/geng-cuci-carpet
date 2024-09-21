<?php

namespace App\Providers;

use Filament\Panel;
use Filament\PanelProvider;

class ClientPanelProvider extends PanelProvider
{
    public function panel(Panel $panel): Panel
    {
        return $panel
            ->id('client')
            ->path('client')
            ->login()
            ->authGuard('client');
    }
}