# Craft Laravel Package

[![Latest Version on Packagist](https://img.shields.io/packagist/v/hardimpactdev/craft-laravel.svg?style=flat-square)](https://packagist.org/packages/hardimpactdev/craft-laravel)
[![GitHub Tests Action Status](https://img.shields.io/github/actions/workflow/status/hardimpactdev/craft-laravel/run-tests.yml?branch=main&label=tests&style=flat-square)](https://github.com/hardimpactdev/craft-laravel/actions?query=workflow%3Arun-tests+branch%3Amain)
[![GitHub Code Style Action Status](https://img.shields.io/github/actions/workflow/status/hardimpactdev/craft-laravel/fix-php-code-style-issues.yml?branch=main&label=code%20style&style=flat-square)](https://github.com/hardimpactdev/craft-laravel/actions?query=workflow%3A"Fix+PHP+code+style+issues"+branch%3Amain)
[![Total Downloads](https://img.shields.io/packagist/dt/hardimpactdev/craft-laravel.svg?style=flat-square)](https://packagist.org/packages/hardimpactdev/craft-laravel)

Companion scaffolding package for [craft-starterkit](https://github.com/hardimpactdev/craft-starterkit). Provides commands to rapidly set up the app scaffold, Filament admin panel, and multi-language support.

## Support us

[<img src="https://github-ads.s3.eu-central-1.amazonaws.com/Laravel.jpg?t=1" width="419px" />](https://spatie.be/github-ad-click/Laravel)

We invest a lot of resources into creating [best in class open source packages](https://spatie.be/open-source). You can support us by [buying one of our paid products](https://spatie.be/open-source/support-us).

We highly appreciate you sending us a postcard from your hometown, mentioning which of our package(s) you are using. You'll find our address on [our contact page](https://spatie.be/about-us). We publish all received postcards on [our virtual postcard wall](https://spatie.be/open-source/postcards).

## Requirements

-   PHP 8.1 or higher
-   Laravel 10.x, 11.x, or 12.x
-   Node.js and npm/bun for frontend assets

## AI-Assisted Development

This package includes [Laravel Boost](https://laravel.com/ai/boost) integration. When you have both packages installed, AI assistants will automatically understand the available scaffolding commands.

```bash
composer require laravel/boost --dev
php artisan boost:install
```

## Installation

You can install the package via composer:

```bash
composer require hardimpactdev/craft-laravel
```

## Scaffolders

The package provides powerful scaffolding commands to quickly set up different aspects of your application. All scaffolders automatically generate routes using the waymaker package.

### Available Scaffolders

#### 1. App Scaffolder (Full Application Setup)

The most comprehensive scaffolder that sets up a complete application with authentication and dashboard.

```bash
php artisan craft:setup app
```

This scaffolder includes:

-   ✅ App class with redirect configuration
-   ✅ Dashboard controller and views
-   ✅ Settings pages (profile, password, appearance)
-   ✅ HandleInertiaRequests middleware
-   ✅ TypeScript type definitions
-   ✅ Feature tests
-   ✅ Full authentication system (runs internal auth setup)
-   ✅ Automatic route generation

**Note:** For Filament admin functionality, use `php artisan craft:setup filament` instead.

#### 2. Filament Scaffolder

Sets up a Filament admin panel with user management and authentication.

```bash
php artisan craft:setup filament
```

This scaffolder includes:

-   ✅ App class with redirect configuration
-   ✅ Full authentication system
-   ✅ Filament package installation
-   ✅ User resource for managing users
-   ✅ Admin panel configuration
-   ✅ Filament CSS build process
-   ✅ Automatic route generation

#### 3. Multilanguage Scaffolder

Sets up multi-language/i18n support with translation files.

```bash
php artisan craft:setup multilanguage
```

This scaffolder includes:

-   ✅ Language translation files
-   ✅ Example translation component
-   ✅ Automatic route generation

### Route Generation

All scaffolders use the waymaker package to automatically generate routes from controller attributes. Routes are generated at the end of each scaffolding process, eliminating the need to manually run `php artisan waymaker:generate`.

### Files and Directories Created

#### App Scaffolder creates:

```
app/
├── App.php
├── Http/
│   ├── Controllers/
│   │   ├── DashboardController.php
│   │   └── Settings/
│   │       ├── AppearanceController.php
│   │       ├── PasswordController.php
│   │       └── ProfileController.php
│   ├── Middleware/
│   │   └── HandleInertiaRequests.php
│   └── Requests/
│       └── Settings/
│           └── ProfileUpdateRequest.php
resources/js/
├── pages/
│   ├── Dashboard.vue
│   └── settings/
│       ├── Appearance.vue
│       ├── Password.vue
│       └── Profile.vue
└── types/
    └── index.d.ts
tests/Feature/
├── DashboardTest.php
└── Settings/
    ├── PasswordUpdateTest.php
    └── ProfileUpdateTest.php
```

#### Authentication files included by app and filament:

```
app/
├── Actions/Fortify/
│   ├── CreateNewUser.php
│   └── ResetUserPassword.php
├── Concerns/
│   ├── PasswordValidationRules.php
│   └── ProfileValidationRules.php
├── Http/
│   ├── Controllers/Settings/
│   │   └── TwoFactorAuthenticationController.php
│   └── Requests/Settings/
│       └── TwoFactorAuthenticationRequest.php
└── Providers/
    └── FortifyServiceProvider.php
config/
└── fortify.php
resources/js/
├── components/
│   ├── TwoFactorRecoveryCodes.vue
│   └── TwoFactorSetupModal.vue
├── composables/
│   └── useTwoFactorAuth.ts
└── pages/auth/
    ├── ConfirmPassword.vue
    ├── ForgotPassword.vue
    ├── Login.vue
    ├── Register.vue
    ├── ResetPassword.vue
    ├── TwoFactorChallenge.vue
    └── VerifyEmail.vue
tests/Feature/Auth/
├── AuthenticationTest.php
├── EmailVerificationTest.php
├── PasswordConfirmationTest.php
├── PasswordResetTest.php
└── RegistrationTest.php
```

### Usage Examples

#### Quick Start - Full Application

```bash
# Install the package
composer require hardimpactdev/craft-laravel

# Run the app scaffolder for a complete setup
php artisan craft:setup app

# Install frontend dependencies
npm install # or bun install

# Run migrations
php artisan migrate

# Start development server
npm run dev # or bun dev
```

#### Filament with Authentication

```bash
# Run the Filament scaffolder (includes auth)
php artisan craft:setup filament

# Install frontend dependencies
npm install # or bun install

# Run migrations
php artisan migrate

# Create a Filament admin user (required to access /admin)
php artisan make:filament-user
```

### Important Notes

1. **Middleware Replacement**: The HandleInertiaRequests middleware will be replaced if it already exists in your application.

2. **Route Attributes**: All controllers use route attributes from the waymaker package, eliminating the need for manual route definitions.

3. **App Class**: The App class provides a centralized location for application configuration, including login redirect routes.

4. **File Merging**: When copying directories, existing files are preserved unless they have the same name as files being copied.

5. **Dependencies**: Make sure to install the waymaker package if not already installed:
    ```bash
    composer require nckrtl/waymaker
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

Please review [our security policy](../../security/policy) on how to report security vulnerabilities.

## Credits

-   [nckrtl](https://github.com/nckrtl)
-   [All Contributors](../../contributors)

## License

The MIT License (MIT). Please see [License File](LICENSE.md) for more information.
