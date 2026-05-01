<?php

namespace Slimani\QuickLogin;

use Closure;
use Filament\Contracts\Plugin;
use Filament\Panel;
use Filament\View\PanelsRenderHook;
use Filament\Support\Concerns\EvaluatesClosures;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Blade;

class QuickLoginPlugin implements Plugin
{
    use EvaluatesClosures;

    protected bool|Closure $enabled = true;

    protected Closure|Collection|array|null $users = null;

    protected string $userModel = 'App\\Models\\User';

    public function getId(): string
    {
        return 'filament-quick-login';
    }

    public static function make(): static
    {
        return app(static::class);
    }

    public function enable(bool|Closure $condition = true): static
    {
        $this->enabled = $condition;

        return $this;
    }

    public function getEnabled(): bool
    {
        return (bool) $this->evaluate($this->enabled);
    }

    public function users(Closure|Collection|array $users): static
    {
        $this->users = $users;

        return $this;
    }

    public function userModel(string $model): static
    {
        $this->userModel = $model;

        return $this;
    }

    public function getUsers(): Collection|array|null
    {
        return $this->evaluate($this->users);
    }

    public function getUserModel(): string
    {
        return $this->userModel;
    }

    public function register(Panel $panel): void
    {
        if (! $this->getEnabled()) {
            return;
        }

        $panel->renderHook(
            PanelsRenderHook::AUTH_LOGIN_FORM_AFTER,
            fn (): string => Blade::render('@livewire(\'quick-login-buttons\')')
        );
    }

    public function boot(Panel $panel): void
    {
        //
    }
}
