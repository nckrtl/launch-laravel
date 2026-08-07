## Launch Laravel

Launch Laravel scaffolds React applications built from `hardimpactdev/launch-starter-kit`.

### Choose a setup

| Goal | Command |
| --- | --- |
| Add authentication, dashboard, and account settings | `php artisan launch:setup app` |
| Add authentication and a Filament admin panel | `php artisan launch:setup filament` |
| Add JSON translations and React i18n | `php artisan launch:setup multilanguage` |

These are the only public setup names. Authentication is composed internally by the app and Filament setups.

### Application setup

@verbatim
<code-snippet name="Scaffold the React application" lang="bash">
npm install
php artisan launch:setup app
php artisan migrate
npm run build
</code-snippet>
@endverbatim

The app setup installs Fortify, passkeys, two-factor confirmation, login links, React authentication pages, dashboard and settings pages, Waymaker controllers, and feature tests.

### Filament setup

@verbatim
<code-snippet name="Scaffold Filament" lang="bash">
npm install
php artisan launch:setup filament
php artisan migrate
php artisan make:filament-user
npm run build
</code-snippet>
@endverbatim

The Filament setup includes authentication, the Filament 5 panel provider, user management, profile editing, passkey management, and two-factor management.

### Multilanguage setup

@verbatim
<code-snippet name="Enable Launch i18n" lang="bash">
php artisan launch:setup multilanguage
npm run build
</code-snippet>
@endverbatim

This creates `lang/en.json`, `lang/nl.json`, and `resources/js/pages/TranslationExample.tsx`, and enables `i18n` in `vite.config.ts`.

### Verification

After scaffolding, run the setup a second time to check idempotency, then run migrations, application tests, and the production frontend build.

### Strict defaults

The package enables strict runtime defaults through `config/launch-laravel.php`:

- strict Eloquent models and automatic relationship eager loading;
- immutable dates and aggressive Vite prefetching;
- HTTPS, safe database commands, and strong password defaults in production;
- blocked stray HTTP requests and faked sleep during tests.

Always add `declare(strict_types=1);` to PHP files. Assign the result of date operations instead of mutating dates, and fake external HTTP requests in tests.

To opt out of a default, publish the package configuration and set the relevant `launch-laravel.defaults` value to `false`.
