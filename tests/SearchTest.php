<?php

use Filament\Facades\Filament;
use Filament\Panel;
use Slimani\QuickLogin\Livewire\QuickLoginButtons;
use Slimani\QuickLogin\QuickLoginPlugin;
use Slimani\QuickLogin\Tests\TestCase;
use Slimani\QuickLogin\Tests\User;

uses(TestCase::class);

beforeEach(function () {
    Filament::setCurrentPanel(
        Panel::make('admin')
            ->default()
            ->plugin(QuickLoginPlugin::make()->userModel(User::class))
    );
});

it('filters users by name', function () {
    User::create(['name' => 'John Doe', 'email' => 'john@example.com', 'password' => 'password']);
    User::create(['name' => 'Jane Smith', 'email' => 'jane@example.com', 'password' => 'password']);

    $component = new QuickLoginButtons;
    $component->search = 'John';

    $actions = $component->getActions();

    $loginActions = collect($actions)->filter(fn ($action, $key) => str_starts_with($key, 'login_as_'));

    expect($loginActions)->toHaveCount(1);
    expect($loginActions->first()->getLabel())->toBe('John Doe');
});

it('filters users by email', function () {
    User::create(['name' => 'John Doe', 'email' => 'john@example.com', 'password' => 'password']);
    User::create(['name' => 'Jane Smith', 'email' => 'jane@example.com', 'password' => 'password']);

    $component = new QuickLoginButtons;
    $component->search = 'jane@example.com';

    $actions = $component->getActions();

    $loginActions = collect($actions)->filter(fn ($action, $key) => str_starts_with($key, 'login_as_'));

    expect($loginActions)->toHaveCount(1);
    expect($loginActions->first()->getLabel())->toBe('Jane Smith');
});

it('filters users by id', function () {
    $user = User::create(['name' => 'John Doe', 'email' => 'john@example.com', 'password' => 'password']);
    User::create(['name' => 'Jane Smith', 'email' => 'jane@example.com', 'password' => 'password']);

    $component = new QuickLoginButtons;
    $component->search = (string) $user->id;

    $actions = $component->getActions();

    $loginActions = collect($actions)->filter(fn ($action, $key) => str_starts_with($key, 'login_as_'));

    expect($loginActions)->toHaveCount(1);
    expect($loginActions->first()->getLabel())->toBe('John Doe');
});

it('paginates results with 300 users', function () {
    $users = [];
    for ($i = 1; $i <= 300; $i++) {
        $users[] = [
            'name' => "Test User $i",
            'email' => "test$i@example.com",
            'password' => bcrypt('password'),
        ];
    }

    foreach (array_chunk($users, 100) as $chunk) {
        User::insert($chunk);
    }

    $component = new QuickLoginButtons;
    $component->search = 'Test User';
    $component->page = 1;

    $actions = $component->getActions();
    $loginActions = collect($actions)->filter(fn ($action, $key) => str_starts_with($key, 'login_as_'));

    expect($loginActions)->toHaveCount(12); // Default perPage is 12
    expect($loginActions->first()->getLabel())->toBe('Test User 1');

    // Test second page
    $component->page = 2;
    $actions = $component->getActions();
    $loginActions = collect($actions)->filter(fn ($action, $key) => str_starts_with($key, 'login_as_'));

    expect($loginActions)->toHaveCount(12);
    expect($loginActions->first()->getLabel())->toBe('Test User 13');

    // Test last page
    $component->page = 25; // 300 / 12 = 25
    $actions = $component->getActions();
    $loginActions = collect($actions)->filter(fn ($action, $key) => str_starts_with($key, 'login_as_'));

    expect($loginActions)->toHaveCount(12);
    expect($loginActions->last()->getLabel())->toBe('Test User 300');
});

it('returns default users when search is empty', function () {
    $defaultUser = User::create(['name' => 'Default User', 'email' => 'default@example.com', 'password' => 'password']);
    User::create(['name' => 'Other User', 'email' => 'other@example.com', 'password' => 'password']);

    Filament::getCurrentPanel()->getPlugin('filament-quick-login')->users([$defaultUser]);

    $component = new QuickLoginButtons;
    $component->search = '';

    $actions = $component->getActions();
    $loginActions = collect($actions)->filter(fn ($action, $key) => str_starts_with($key, 'login_as_'));

    expect($loginActions)->toHaveCount(1);
    expect($loginActions->first()->getLabel())->toBe('Default User');
});
