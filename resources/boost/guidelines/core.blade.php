## Craft Laravel

Companion scaffolding package for [craft-starterkit](https://github.com/hardimpactdev/craft-starterkit). Provides commands to set up authentication, dashboard, CMS (Filament), and multi-language support.

### Quick Decision Guide

Use this to determine which setup command to run:

| User Request | Command | Why |
|--------------|---------|-----|
| "Add authentication" | `php artisan craft:setup app` | `app` includes auth + dashboard + settings |
| "Add a dashboard" | `php artisan craft:setup app` | `app` includes auth + dashboard + settings |
| "Set up the app" | `php artisan craft:setup app` | Complete frontend application stack |
| "Add a CMS" | `php artisan craft:setup cms` | Filament admin panel with auth |
| "Add admin panel" | `php artisan craft:setup cms` | Filament admin panel with auth |
| "Set up everything" | Run both `app` then `cms` | Full stack: auth + dashboard + CMS |
| "Add translations" | `php artisan craft:setup multilanguage` | i18n support |

### Available Commands

| Command | What It Sets Up |
|---------|-----------------|
| `php artisan craft:setup app` | Auth + Dashboard + Settings (recommended for most apps) |
| `php artisan craft:setup cms` | Auth + Filament CMS admin panel |
| `php artisan craft:setup multilanguage` | Translation files and i18n support |
| `php artisan craft:setup auth` | Auth only (use `app` instead unless building custom dashboard) |
| `php artisan craft:setup dashboard` | Dashboard only (requires auth first, use `app` instead) |

### Complete Setup Workflows

#### Standard Application (Auth + Dashboard)

@verbatim
<code-snippet name="Setup standard application" lang="bash">
# Run the app scaffolder
php artisan craft:setup app

# Install dependencies and build
bun install
bun run build

# Run migrations
php artisan migrate
</code-snippet>
@endverbatim

#### Application with CMS Admin Panel

@verbatim
<code-snippet name="Setup application with CMS" lang="bash">
# Run the CMS scaffolder (includes auth)
php artisan craft:setup cms

# Install dependencies and build
bun install
bun run build

# Run migrations
php artisan migrate

# Create an admin user for Filament
php artisan make:filament-user
</code-snippet>
@endverbatim

#### Full Stack (Dashboard + CMS)

@verbatim
<code-snippet name="Setup complete stack with dashboard and CMS" lang="bash">
# Run app first (auth + dashboard)
php artisan craft:setup app

# Then add CMS (Filament admin panel)
php artisan craft:setup cms

# Install dependencies and build
bun install
bun run build

# Run migrations
php artisan migrate

# Create an admin user for Filament
php artisan make:filament-user
</code-snippet>
@endverbatim

#### Adding Multi-Language Support

@verbatim
<code-snippet name="Add i18n support to existing app" lang="bash">
php artisan craft:setup multilanguage
</code-snippet>
@endverbatim

### What Each Setup Creates

#### App Setup (Recommended)
- `app/Http/Controllers/Auth/*` - Authentication controllers
- `app/Http/Controllers/DashboardController.php` - Dashboard
- `app/Http/Controllers/Settings/*` - Profile, password, appearance settings
- `app/Http/Middleware/HandleInertiaRequests.php` - Inertia middleware
- `resources/js/pages/auth/*` - Login, register, password reset pages
- `resources/js/pages/dashboard/*` - Dashboard pages
- `resources/js/pages/settings/*` - Settings pages
- `tests/Feature/Auth/*` - Authentication tests
- `tests/Feature/Settings/*` - Settings tests
- Database migrations for users table

#### CMS Setup
- Everything from Auth setup (controllers, views, tests)
- `filament/filament:^5.2` Composer package
- `app/Filament/*` - Filament resources and pages
- `app/Providers/Filament/AdminPanelProvider.php` - Admin panel config
- `public/css/filament/*` - Filament theme
- NPM packages: `@tailwindcss/forms`, `@tailwindcss/typography`

#### MultiLanguage Setup
- `lang/*` - Translation files for multiple languages
- `resources/js/pages/TranslationExample.vue` - Example component

### Verification

After setup, verify everything works:

@verbatim
<code-snippet name="Verify setup" lang="bash">
# Run tests
php artisan test

# Start dev server and check manually
bun run dev
# Visit http://localhost:8000 in browser
</code-snippet>
@endverbatim

### Strict Defaults (Active by Default)

This project enforces strict runtime defaults via `config/laravel.php`. These are active unless explicitly disabled.

#### All Environments

- **Strict models** (`Model::shouldBeStrict()`) — prevents lazy loading, silently discarded attributes, and accessing missing attributes. Always eager-load relationships or rely on automatic eager loading.
- **Automatic eager loading** (`Model::automaticallyEagerLoadRelationships()`) — eliminates N+1 queries automatically (Laravel 12.8+). You still should define `$with` on models when the relationship is always needed.
- **Immutable dates** (`Date::use(CarbonImmutable::class)`) — all date casts return `CarbonImmutable`. Never mutate a date in place; assign the return value instead.
- **Aggressive prefetching** (`Vite::useAggressivePrefetching()`) — Vite prefetches all assets for faster page loads.

#### Production Only

- **Force HTTPS** (`URL::forceHttps()`) — all generated URLs use HTTPS.
- **Prohibit destructive commands** (`DB::prohibitDestructiveCommands()`) — prevents `migrate:fresh`, `migrate:reset`, `db:wipe` in production.
- **Password defaults** (`Password::defaults(...)`) — minimum 12 characters, mixed case, numbers, symbols, and uncompromised check.

#### Test Only

- **Prevent stray requests** (`Http::preventStrayRequests()`) — unfaked external HTTP calls throw an exception. Inertia SSR render, health, and Vite hot SSR calls are allowed when SSR is enabled.
- **Fake sleep** (`Sleep::fake()`) — `Sleep::for()` calls are faked so tests run instantly.

#### Writing Compatible Code

@verbatim
<code-snippet name="Strict defaults compatible patterns" lang="php">
// ✅ Always add strict types to new PHP files
declare(strict_types=1);

// ✅ Dates are immutable — assign return values
$nextWeek = $user->created_at->addWeek();

// ❌ Never mutate dates in place
$user->created_at->addWeek(); // has no effect with CarbonImmutable

// ✅ Always fake external HTTP calls in tests
Http::fake(['api.example.com/*' => Http::response(['ok' => true])]);

// ✅ Eager-load when not using automatic eager loading
$users = User::with('posts', 'comments')->get();
</code-snippet>
@endverbatim

#### Disabling a Default

To disable a specific default, set it to `false` in `config/laravel.php`:

@verbatim
<code-snippet name="Disable a strict default" lang="php">
// config/laravel.php
'defaults' => [
    'strict_models' => false, // disable strict models
    // ... other defaults remain true
],
</code-snippet>
@endverbatim

### Important Notes

1. **Always run migrations** after any setup that includes auth
2. **Always run `bun install && bun run build`** after scaffolding
3. **For CMS**, you must create an admin user with `php artisan make:filament-user`
4. **Routes are auto-generated** via Waymaker - no manual route configuration needed
5. **Setups are idempotent** - safe to run multiple times
