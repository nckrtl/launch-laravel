# Launch Laravel

[![Latest Version on Packagist](https://img.shields.io/packagist/v/hardimpactdev/launch-laravel.svg?style=flat-square)](https://packagist.org/packages/hardimpactdev/launch-laravel)
[![GitHub Tests Action Status](https://img.shields.io/github/actions/workflow/status/hardimpactdev/launch-laravel/run-tests.yml?branch=main&label=tests&style=flat-square)](https://github.com/hardimpactdev/launch-laravel/actions/workflows/run-tests.yml)
[![Total Downloads](https://img.shields.io/packagist/dt/hardimpactdev/launch-laravel.svg?style=flat-square)](https://packagist.org/packages/hardimpactdev/launch-laravel)

React, authentication, Filament, and localization scaffolding for the [Launch React starterkit](https://github.com/hardimpactdev/launch-starter-kit).

## Requirements

- PHP 8.3 or newer
- Laravel 12 or 13
- Node.js with npm, or Bun
- A Launch React starterkit application

## Installation

```bash
composer require hardimpactdev/launch-laravel
```

## Setups

The package exposes three setup commands:

| Command | Installs |
| --- | --- |
| `php artisan launch:setup app` | Fortify authentication, passkeys, two-factor authentication, dashboard, settings, and the React application shell |
| `php artisan launch:setup filament` | Authentication plus a Filament 5 admin panel, user management, profile, passkey, and two-factor pages |
| `php artisan launch:setup multilanguage` | JSON translations, a React example page, and Launch Vite i18n configuration |

The React scaffold registry is bundled with this package, so setup does not depend on a local `launch-ui` checkout.

### Application setup

```bash
npm install
php artisan launch:setup app
php artisan migrate
npm run build
```

The generated application includes Fortify login and registration, password reset, email verification, passkeys, two-factor confirmation, dashboard and account settings pages, Waymaker controllers, Wayfinder imports, and feature tests.

### Filament setup

```bash
npm install
php artisan launch:setup filament
php artisan migrate
php artisan make:filament-user
npm run build
```

Filament authentication redirects are configured for `/admin`. If the application scaffold already provides a dashboard, its redirect remains in place.

### Multilanguage setup

```bash
php artisan launch:setup multilanguage
npm run build
```

This creates `lang/en.json`, `lang/nl.json`, and `resources/js/pages/TranslationExample.tsx`, then enables `i18n` in `vite.config.ts`.

All setups generate Waymaker routes and are safe to run again.

## Development

```bash
composer test
composer analyse
composer format -- --test
composer validate --strict
```

The integration workflow also runs every public setup against the current Launch React starterkit and builds the resulting application.

## Changelog

See [CHANGELOG.md](CHANGELOG.md) for release history.

## Security

Report vulnerabilities through the repository's [security policy](../../security/policy).

## License

The MIT License. See [LICENSE.md](LICENSE.md).
