<?php

use Filament\Panel;
use Slimani\QuickLogin\QuickLoginPlugin;

it('can be registered as a plugin', function () {
    $panel = Panel::make('test')
        ->plugin(QuickLoginPlugin::make());

    expect($panel->getPlugin('filament-quick-login'))
        ->toBeInstanceOf(QuickLoginPlugin::class);
});

it('can accept custom users', function () {
    $users = ['user1', 'user2'];
    $plugin = QuickLoginPlugin::make()->users($users);

    expect($plugin->getUsers())->toBe($users);
});
