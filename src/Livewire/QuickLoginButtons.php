<?php

namespace Slimani\QuickLogin\Livewire;

use Filament\Actions\Action;
use Filament\Actions\ActionGroup;
use Filament\Actions\Concerns\InteractsWithActions;
use Filament\Actions\Contracts\HasActions;
use Filament\Facades\Filament;
use Filament\Forms\Components\TextInput;
use Filament\Schemas\Concerns\InteractsWithSchemas;
use Filament\Schemas\Contracts\HasSchemas;
use Filament\Schemas\Schema;
use Illuminate\Contracts\Auth\Authenticatable;
use Illuminate\Contracts\View\View;
use Illuminate\Support\Arr;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Auth;
use Livewire\Attributes\Url;
use Livewire\Component as LivewireComponent;
use Slimani\QuickLogin\QuickLoginPlugin;

class QuickLoginButtons extends LivewireComponent implements HasActions, HasSchemas
{
    use InteractsWithActions;
    use InteractsWithSchemas;

    #[Url]
    public string $search = '';

    public int $page = 1;

    protected int $perPage = 5;

    protected bool $hasMorePages = false;

    protected int $lastPage = 1;

    public function mount(): void
    {
        $this->perPage = count($this->getPlugin()?->getUsers() ?? []) ?: 5;
    }

    public function updatedSearch(): void
    {
        $this->page = 1;
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

        $pluginUsers = collect($plugin->getUsers());

        if (blank($this->search)) {
            $users = $pluginUsers;
            $this->hasMorePages = false;
            $this->lastPage = 1;
        } else {
            /** @var class-string<Authenticatable> $model */
            $model = $plugin->getUserModel();

            $query = $model::query()
                ->where(function ($query) {
                    $query->where('name', 'like', "%{$this->search}%")
                        ->orWhere('email', 'like', "%{$this->search}%")
                        ->orWhere('id', $this->search);
                });

            $paginator = $query->paginate($this->perPage, ['*'], 'page', $this->page);
            $users = collect($paginator->items());
            $this->hasMorePages = $paginator->hasMorePages();
            $this->lastPage = $paginator->lastPage();
        }

        if ($users->isEmpty() && blank($this->search)) {
            return [];
        }

        $actions = $users
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

        $actions['previousPage'] = $this->getPreviousPageAction()->livewire($this);
        $actions['nextPage'] = $this->getNextPageAction()->livewire($this);

        return $actions;
    }


    protected function getPreviousPageAction(): Action
    {
        return Action::make('previousPage')
            ->icon('heroicon-m-chevron-left')
            ->color(fn () => $this->page > 1 ? 'primary' : 'gray')
            ->disabled(fn () => $this->page <= 1)
            ->action(fn () => $this->page--);
    }

    protected function getNextPageAction(): Action
    {
        return Action::make('nextPage')
            ->icon('heroicon-m-chevron-right')
            ->color(fn () => $this->hasMorePages ? 'primary' : 'gray')
            ->disabled(fn () => ! $this->hasMorePages)
            ->action(fn () => $this->page++);
    }

    /**
     * @return array<ActionGroup>
     */
    public function getQuickLoginActionGroups(): array
    {
        return collect($this->getActions())
            ->filter(fn ($action, $name) => str_starts_with($name, 'login_as_'))
            ->chunk(3)
            ->map(fn (Collection $group): ActionGroup => ActionGroup::make($group->all())->buttonGroup())
            ->all();
    }

    public function content(Schema $schema): Schema
    {
        return $schema
            ->schema([
                TextInput::make('search')
                    ->label(__('Search Users'))
                    ->hiddenLabel()
                    ->live()
                    ->placeholder(__('Search by name, email or ID...'))
                    ->suffixActions([
                        $this->getPreviousPageAction(),
                        $this->getNextPageAction(),
                    ])
            ]);
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
