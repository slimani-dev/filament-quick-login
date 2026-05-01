<?php

namespace Slimani\QuickLogin;

use Livewire\Livewire;
use Slimani\QuickLogin\Livewire\QuickLoginButtons;
use Spatie\LaravelPackageTools\Package;
use Spatie\LaravelPackageTools\PackageServiceProvider;

class QuickLoginServiceProvider extends PackageServiceProvider
{
    public function configurePackage(Package $package): void
    {
        $package
            ->name('filament-quick-login')
            ->hasViews();
    }

    public function packageBooted(): void
    {
        Livewire::component('quick-login-buttons', QuickLoginButtons::class);
    }
}
