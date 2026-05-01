# Filament Quick Login

[![GitHub Workflow Status](https://img.shields.io/github/actions/workflow/status/slimani-dev/filament-quick-login/run-tests.yml?branch=main&label=tests&style=flat-square)](https://github.com/slimani-dev/filament-quick-login/actions/workflows/run-tests.yml)
[![GitHub Workflow Status](https://img.shields.io/github/actions/workflow/status/slimani-dev/filament-quick-login/phpstan.yml?branch=main&label=phpstan&style=flat-square)](https://github.com/slimani-dev/filament-quick-login/actions/workflows/phpstan.yml)
[![GitHub Workflow Status](https://img.shields.io/github/actions/workflow/status/slimani-dev/filament-quick-login/fix-php-code-style-issues.yml?branch=main&label=code%20style&style=flat-square)](https://github.com/slimani-dev/filament-quick-login/actions/workflows/fix-php-code-style-issues.yml)
[![Total Downloads](https://img.shields.io/packagist/dt/slimani/filament-quick-login.svg?style=flat-square)](https://packagist.org/packages/slimani/filament-quick-login)
[![License](https://img.shields.io/packagist/l/slimani/filament-quick-login.svg?style=flat-square)](https://github.com/slimani-dev/filament-quick-login/blob/main/LICENSE)

A quick login plugin for Filament login page. It allows you to quickly log in as predefined users, perfect for development and testing environments.

## Features

- **Quick Login Buttons**: Adds buttons to the login page to log in instantly.
- **Customizable Users**: Define which users are available for quick login.
- **Environment Aware**: Easily enable or disable the plugin based on your application environment.
- **Model Support**: Works with any Eloquent user model.

## Installation

You can install the package via composer:

```bash
composer require slimani/filament-quick-login
```

## Usage

### Registering the Plugin

Register the plugin in your Panel Provider:

```php
use Slimani\QuickLogin\QuickLoginPlugin;

public function panel(Panel $panel): Panel
{
    return $panel
        ->plugin(QuickLoginPlugin::make());
}
```

### Customizing the Plugin

You can customize the plugin's behavior using the following methods:

```php
QuickLoginPlugin::make()
    ->enable(app()->environment('local')) // Only enable in local environment
    ->userModel(\App\Models\Admin::class) // Custom user model
    ->users([
        'admin@example.com',
        'user@example.com',
    ]) // Predefined users by email
```

You can also pass a Closure or a Collection to the `users()` method:

```php
QuickLoginPlugin::make()
    ->users(fn () => \App\Models\User::all())
```

## Testing

```bash
composer test
```

## Changelog

Please see [CHANGELOG](CHANGELOG.md) for more information on what has changed recently.

## Contributing

Please see [CONTRIBUTING](CONTRIBUTING.md) for details.

## Security Vulnerabilities

Please review [our security policy](SECURITY.md) on how to report security vulnerabilities.

## Credits

- [Mohamed Slimani](https://github.com/slimani-dev)
- [All Contributors](../../contributors)

## License

The MIT License (MIT). Please see [License File](LICENSE) for more information.
