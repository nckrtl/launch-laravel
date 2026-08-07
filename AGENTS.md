# Launch Laravel Package

Laravel 12/13 scaffolding for the React-based [Launch starterkit](https://github.com/nckrtl/launch-starter-kit).

## Public API

Only these setup commands are public:

| Command | Setup class |
| --- | --- |
| `php artisan launch:setup app` | `SetupApp` |
| `php artisan launch:setup filament` | `SetupFilament` |
| `php artisan launch:setup multilanguage` | `SetupMultilanguage` |

`SetupAuth` and `SetupCms` are internal composition classes. Add a setup to `SetupCommand::SETUPS` only when it is intentionally part of the public API.

## Architecture

- `src/Commands/SetupCommand.php` resolves the public setup name.
- `src/Setup/Setup.php` runs each setup's ordered task list and stops on the first failure.
- `src/Setup/{App,Auth,Cms,MultiLanguage}/` contains small, idempotent tasks.
- `resources/stubs/` contains backend and configuration files copied into host applications.
- `resources/registry/` is the package-owned, self-contained shadcn registry used for React scaffolding.
- `tests/Feature/` covers setup composition, file transformations, registry behavior, and package booting.

Keep tasks idempotent. Check before modifying or registering files, return `false` with a useful error when work cannot complete, and preserve a host application's unrelated content.

## Project conventions

- Add `declare(strict_types=1);` to PHP files.
- Follow Laravel conventions and use explicit types.
- Use Waymaker attributes in generated controllers and Wayfinder action helpers in React files.
- Keep React output compatible with React 19, Inertia 3, Tailwind CSS 4, Base UI, and VitePlus.
- Do not add Vue stubs or depend on an unpublished local `launch-ui` registry.
- Keep the public command surface and README synchronized.

## Verification

Run the package checks before pushing:

```bash
composer test
composer analyse
composer format -- --test
composer validate --strict
```

For scaffold changes, also run the affected public setup twice in a clean `launch-starter-kit` checkout, then run its migrations, tests, and production build.

## Release workflow

Pushing to `main` runs the test matrix. Once it passes, `.github/workflows/run-tests.yml` creates the next patch tag and GitHub release. After that workflow succeeds on `main`, `.github/workflows/update-changelog.yml` reads the published release notes and updates `CHANGELOG.md`; it can also be dispatched manually for recovery. Do not create a competing manual tag for a normal release.

Integration tests require the `GH_PAT` repository secret to check out the private React starterkit.
