<?php

namespace Slimani\QuickLogin\Livewire;

use Illuminate\Contracts\Auth\Authenticatable;
use Filament\Actions\Action;
use Filament\Actions\ActionGroup;
use Filament\Actions\Concerns\InteractsWithActions;
use Filament\Actions\Contracts\HasActions;
use Filament\Facades\Filament;
use Filament\Schemas\Concerns\InteractsWithSchemas;
use Filament\Schemas\Contracts\HasSchemas;
use Illuminate\Contracts\View\View;
use Illuminate\Support\Arr;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Auth;
use Livewire\Component as LivewireComponent;
use Slimani\QuickLogin\QuickLoginPlugin;

class QuickLoginButtons extends LivewireComponent implements HasActions, HasSchemas
{
    use InteractsWithActions;
    use InteractsWithSchemas;

    public function mount(): void
    {
        $this->cachedActions = $this->getActions();
    }

    public function getAction(string|array $actions, bool $isMounting = true): ?Action
    {
        $actionNames = array_map(
            function (mixed $action): string {
                /** @phpstan-ignore function.impossibleType */
                if (is_array($action)) {
                    return (string) $action['name'];
                }

                return (string) $action;
            },
            Arr::wrap($actions),
        );

        $actionName = $actionNames ? end($actionNames) : null;

        return filled($actionName) ? ($this->getActions()[$actionName] ?? null) : null;
    }

    /**
     * @return array<string, Action>
     */
    public function getActions(): array
    {
        $plugin = $this->getPlugin();

        if (! $plugin || ! $plugin->getEnabled()) {
            return [];
        }

        $users = collect($plugin->getUsers());

        if ($users->isEmpty()) {
            return [];
        }

        return $users
            ->mapWithKeys(function (Authenticatable $user): array {
                /** @var string $id */
                $id = $user->getAuthIdentifier();

                /** @var string $name */
                $name = $user->{ 'name' } ?? 'User';

                $action = Action::make("login_as_{$id}")
                    ->label($name)
                    ->action(function () use ($user): mixed {
                        Auth::login($user);

                        return redirect()->to(Filament::getCurrentPanel()->getUrl());
                    });

                $action->livewire($this);

                return [
                    "login_as_{$id}" => $action,
                ];
            })
            ->all();
    }

    /**
     * @return array<ActionGroup>
     */
    public function getQuickLoginActionGroups(): array
    {
        return collect($this->getActions())
            ->chunk(3)
            ->map(fn (Collection $group): ActionGroup => ActionGroup::make($group->all())->buttonGroup())
            ->all();
    }

    public function content(\Filament\Schemas\Schema $schema): \Filament\Schemas\Schema
    {
        return $schema;
    }

    protected function resolveAction(array $action, array $parentActions): ?Action
    {
        return $this->getActions()[$action['name']] ?? null;
    }

    public function getPlugin(): ?QuickLoginPlugin
    {
        $panel = Filament::getCurrentPanel();

        if (! $panel || ! $panel->hasPlugin('filament-quick-login')) {
            return null;
        }

        /** @var QuickLoginPlugin $plugin */
        $plugin = $panel->getPlugin('filament-quick-login');

        return $plugin;
    }

    public function render(): View
    {
        /** @var view-string $view */
        $view = 'filament-quick-login::livewire.quick-login-buttons';

        return view($view);
    }
}
