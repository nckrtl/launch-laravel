# Changelog

All notable changes to `launch-laravel` will be documented in this file.

## v0.3.9 - 2026-08-07

**Full Changelog**: https://github.com/nckrtl/launch-laravel/compare/v0.3.8...v0.3.9

## v0.3.8 - 2026-08-07

**Full Changelog**: https://github.com/nckrtl/launch-laravel/compare/v0.3.7...v0.3.8

## v0.3.7 - 2026-08-07

**Full Changelog**: https://github.com/nckrtl/launch-laravel/compare/v0.3.6...v0.3.7

## v0.3.6 - 2026-08-07

**Full Changelog**: https://github.com/nckrtl/launch-laravel/compare/v0.3.5...v0.3.6

## Unreleased

### Changed

- Rename package from `hardimpactdev/craft-laravel` / `HardImpact\\Craft` to `hardimpactdev/launch-laravel` / `HardImpact\\Launch`.
- Rename Artisan command `craft:setup` to `launch:setup`.
- Rename config file and key from `craft-laravel` to `launch-laravel`.
- Rename bundled React registry prefix from `@craft/*` to `@launch/*` (including `launch-app-scaffold`, `launch-auth-scaffold`, `launch-types`).
- Align Vite i18n detection with `defineLaunchConfig()` from `@hardimpactdev/launch-ui`.

## v0.3.4 - 2026-07-14

### What's changed

- Rebuilt the package scaffolds for the Laravel 13 + React 19 Launch starterkit.
- Bundled the complete Launch React registry so app and authentication setup no longer depends on an unpublished local package.
- Expanded the app scaffold with Fortify authentication, passkeys, two-factor confirmation, security settings, and generated Wayfinder imports.
- Added a Filament 5 admin scaffold with shared authentication, user management, profile editing, passkeys, and two-factor controls.
- Replaced the legacy Vue multilanguage example with JSON translations and a React example page.
- Cleaned package metadata, obsolete tasks and stubs, distribution exports, and the Laravel/OS CI matrices.

### Breaking changes

The supported public setup commands are now limited to:

- `php artisan launch:setup app`
- `php artisan launch:setup filament`
- `php artisan launch:setup multilanguage`

Authentication remains an internal building block of the app and Filament scaffolds; the old public `auth`, `dashboard`, and `cms` setup entry points have been removed.

### Compatibility

- PHP 8.3–8.5
- Laravel 12–13
- React 19 starterkit

**Full Changelog**: https://github.com/hardimpactdev/launch-laravel/compare/v0.3.3...v0.3.4

## v0.3.2 - 2026-05-12

### What's changed

- Add Laravel 13 support to `illuminate/contracts` constraint (^12.0 || ^13.0).
- Scaffolding refinements: simplify scaffolds to app + filament, add `RunMigrationsTask` and `BuildFrontendTask`, `ConfigureAppEntryTask` for app.tsx layout resolver, publish Fortify 2FA migrations, exclude Home page from sidebar layout, rename `password.update` route to `security.password`.

**Full Changelog**: https://github.com/hardimpactdev/launch-laravel/compare/v0.3.1...v0.3.2

## v0.2.9 - Auth Scaffold Namespace Fix - 2026-02-24

### Bug Fixes

- **Auth Scaffold**: Fixed namespace placeholder bug causing `Class App\Facades\App not found` errors
  - Updated stub files to use `{{namespace}}` placeholder for proper namespace replacement
  - Fixed CopyAppClassTask to trim trailing backslash for valid PHP namespace declarations
  - Added comprehensive regression tests
  

### Files Changed

- resources/stubs/app/app/App.php
- resources/stubs/auth/tests/Feature/Auth/RegistrationTest.php
- resources/stubs/auth/tests/Feature/Auth/EmailVerificationTest.php
- src/Setup/App/CopyAppClassTask.php
- tests/Feature/AuthScaffoldTest.php

## 0.2.9 - 2026-02-24

### Bug Fixes

- **Auth Scaffold**: Fixed namespace placeholder bug causing `Class App\Facades\App not found` errors
  - Updated `resources/stubs/app/app/App.php` to use `{{namespace}}` placeholder
  - Updated auth test stubs to use `{{namespace}}App` for proper namespace replacement
  - Fixed `CopyAppClassTask` to trim trailing backslash for valid PHP namespace declarations
  - Added comprehensive regression tests in `AuthScaffoldTest.php`
  

**Full Changelog**: https://github.com/hardimpactdev/launch-laravel/compare/v0.2.6...v0.2.7

## 0.2.0 - 2026-01-19

### Breaking Changes

- **Laravel 12 only** - Dropped support for Laravel 10 and 11
- Requires PHP 8.3+

### Changes

- Simplified dependency matrix
- Auth scaffolder improvements for new projects

## 0.1.9 - 2026-01-19

### Changes

- fix: exclude stubs from pint (resolves parse errors)
- fix: support PHP 8.3+
- fix: drop Laravel 10 support (incompatible with pest-plugin-laravel 3)

### Auth Scaffolder

- Auth scaffolder now better works in new projects

## 0.1.8 - 2026-01-19

refactor: update stubs to use useAppNavigation composable

## 0.1.7 - 2026-01-18

### New Features

- Migrated auth to Laravel Fortify with TOTP/2FA support
- Simplified auth pages to use craft-ui components

### Bug Fixes

- Fixed incorrect package import (`@hardimpactdev/craft-vue` → `@hardimpactdev/craft-ui`)
- Fixed layout component names (`AppLayout` → `AppSidebarLayout`)

**Full Changelog**: https://github.com/hardimpactdev/launch-laravel/compare/0.1.6...0.1.7

## 0.1.6 - 2026-01-16

### Maintenance

- Removed "v" prefix from version tags for cleaner Composer versioning (use `0.1.6` instead of `v0.1.6`)

**Full Changelog**: https://github.com/hardimpactdev/launch-laravel/compare/v0.1.5...0.1.6

## v0.1.5 - 2026-01-15

### Maintenance

- Converted CLAUDE.md to AGENTS.md with symlink for OpenCode compatibility
- Updated dependabot/fetch-metadata from 2.4.0 to 2.5.0

**Full Changelog**: https://github.com/hardimpactdev/launch-laravel/compare/v0.1.4...v0.1.5

## v0.1.4 - 2026-01-08

### New Features

- Added release skill for standardized GitHub releases via GitHub CLI

### Documentation

- Document versioning workflow and CHANGELOG format
- Include troubleshooting guide for releases

**Full Changelog**: https://github.com/hardimpactdev/launch-laravel/compare/v0.1.3...v0.1.4

## v0.1.3 - 2026-01-08

### New Features

- Added Laravel Boost integration with AI guidelines for scaffolding commands

### Documentation

- Improved README with clearer setup instructions
- Added documentation for Dashboard and Multilanguage scaffolders
- Added AI-assisted development section

## v0.1.2 - 2026-01-08

### Bug Fixes

- Fixed HandleInertiaRequests middleware using route() helper which caused 500 errors when routes weren't registered
- Changed navigation URLs to use direct paths instead of route names for reliability

## v0.1.1 - 2026-01-08

### Bug Fixes

- Fixed HandleInertiaRequests middleware stub referencing non-existent HomeController route
- Changed default navigation route to use DashboardController.show
- Fixed typo in footer navigation: 'GitHewqub' → 'GitHub'

## v0.1.0 - Initial Release - 2026-01-07

### Initial Release

First tagged release of launch-laravel, the scaffolding companion package for craft-starterkit.

#### Features

- **Modular Setup System**: Run individual setups as needed
  - `launch:setup auth` - Add authentication
  - `launch:setup dashboard` - Add dashboard + settings pages
  - `launch:setup app` - Full app (auth + dashboard combined)
  - `launch:setup cms` - Add Filament CMS
  - `launch:setup multilanguage` - Add language files
  

#### Bug Fixes

- Fixed namespace resolution in SetupCommand
- Fixed `scaffold()` → `setup()` method call in CMS auth task
- Renamed task file to match class name (RunSetupAuthTask)

#### Compatibility

- Aligned with craft-starterkit's current state
- Removed redundant vite i18n config tasks (already in starterkit)
- Added `language` field to User type stub
- SetupApp no longer forces CMS installation

#### Breaking Changes

- `launch:setup app` no longer includes CMS - run `launch:setup cms` separately if needed
