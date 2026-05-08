<?php

namespace Slimani\QuickLogin\Tests;

use Filament\FilamentServiceProvider;
use Livewire\LivewireServiceProvider;
use Orchestra\Testbench\TestCase as Orchestra;
use Slimani\QuickLogin\QuickLoginServiceProvider;

class TestCase extends Orchestra
{
    protected function getPackageProviders($app): array
    {
        return [
            \BladeUI\Heroicons\BladeHeroiconsServiceProvider::class,
            \BladeUI\Icons\BladeIconsServiceProvider::class,
            \Livewire\LivewireServiceProvider::class,
            \Filament\Support\SupportServiceProvider::class,
            \Filament\Actions\ActionsServiceProvider::class,
            \Filament\Forms\FormsServiceProvider::class,
            \Filament\Schemas\SchemasServiceProvider::class,
            \Filament\FilamentServiceProvider::class,
            QuickLoginServiceProvider::class,
        ];
    }

    protected function defineDatabaseMigrations(): void
    {
        $this->loadLaravelMigrations();
    }

    public function getEnvironmentSetUp($app): void
    {
        config()->set('database.default', 'testing');
        config()->set('auth.providers.users.model', User::class);
    }
}

class User extends \Illuminate\Foundation\Auth\User
{
    protected $guarded = [];
    protected $table = 'users';
}
