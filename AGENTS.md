# Craft Laravel Package

Companion scaffolding package for [craft-starterkit](https://github.com/hardimpactdev/craft-starterkit). Provides commands to set up the app scaffold, Filament admin panel, and multi-language support.

## Quick Reference: Scaffolding Commands

| Command | What It Sets Up |
|---------|-----------------|
| `php artisan craft:setup app` | Auth + Dashboard + Settings (recommended for most apps) |
| `php artisan craft:setup filament` | Auth + Filament admin panel |
| `php artisan craft:setup multilanguage` | Translation files and i18n support |

**After scaffolding:**
```bash
bun install && bun run build
php artisan migrate
php artisan make:filament-user  # Only for Filament setup
```

## Laravel Boost Integration

This package provides AI guidelines for [Laravel Boost](https://laravel.com/ai/boost) in `resources/boost/guidelines/core.blade.php`. When users have both this package and Laravel Boost installed, AI assistants will automatically understand the available scaffolding commands.

To use with Laravel Boost:
1. Install Laravel Boost: `composer require laravel/boost --dev`
2. Run: `php artisan boost:install`
3. AI assistants will now recognize Craft's scaffolding capabilities

## Creating Setups

Setups are used to automate the setup of various features in a Laravel application. Here's a comprehensive guide on how to create new Setups.

### Setup Architecture

The setup system consists of:

1. **Setup Class** - Orchestrates the execution of tasks
2. **Task Classes** - Individual units of work that perform specific operations
3. **SetupCommand** - Entry point that resolves and runs Setups

### Directory Structure

```
src/
├── Commands/
│   └── SetupCommand.php
└── Setup/
    ├── SetupInterface.php
    ├── Setup.php (abstract base class)
    ├── SetupApp.php
    ├── SetupFilament.php
    ├── SetupMultilanguage.php
    ├── Tasks/
    │   ├── TaskInterface.php
    │   └── Task.php (abstract base class)
    ├── Auth/
    │   ├── AddAuthRoutesTask.php
    │   ├── CopyAuthControllersTask.php
    │   └── ... (other auth tasks)
    └── Cms/
        ├── InstallFilamentComposerPackageTask.php
        ├── CopyCmsFilesTask.php
        └── ... (other Filament tasks)
```

### Creating a New Setup

#### Step 1: Create the Setup Class

Create a new file `src/Setup/YourFeatureScaffolder.php`:

```php
<?php

namespace HardImpact\Craft\Setups;

use Illuminate\Filesystem\Filesystem;
use HardImpact\Craft\Setup\YourFeature\Task1;
use HardImpact\Craft\Setup\YourFeature\Task2;

class YourFeatureScaffolder extends Setup
{
    /**
     * The tasks to run in order.
     *
     * @var array
     */
    protected $tasks = [
        Task1::class,
        Task2::class,
        // Add more tasks as needed
    ];

    /**
     * Create a new setup instance.
     *
     * @return void
     */
    public function __construct(Filesystem $filesystem)
    {
        parent::__construct($filesystem);
    }
}
```

#### Step 2: Create Task Directory

Create a directory for your tasks:

```bash
mkdir src/Setup/YourFeature
```

#### Step 3: Create Task Classes

Each task should extend the base Task class. Here are common task patterns:

##### Basic Task Template

```php
<?php

namespace HardImpact\Craft\Setup\YourFeature;

use Illuminate\Console\Command;
use Illuminate\Filesystem\Filesystem;
use HardImpact\Craft\Setup\Tasks\Task;

class YourTask extends Task
{
    /**
     * Create a new task instance.
     *
     * @return void
     */
    public function __construct(Filesystem $filesystem, ?Command $command = null)
    {
        parent::__construct($filesystem, $command);
    }

    /**
     * Run the task.
     */
    public function run(): bool
    {
        // Your task logic here
        $this->info('Running task...');

        // Return true on success, false on failure
        return true;
    }

    /**
     * Get the task description.
     */
    public function description(): string
    {
        return 'Description of what this task does';
    }
}
```

### Common Task Patterns

#### 1. Installing Composer Packages

```php
use Symfony\Component\Process\Process;

public function run(): bool
{
    $this->info('Installing package via Composer...');

    $process = new Process(['composer', 'require', 'vendor/package:^1.0', '-W'], base_path());
    $process->setTimeout(300); // 5 minutes timeout

    if ($this->command) {
        $process->run(function ($type, $buffer) {
            $this->command->line($buffer);
        });
    } else {
        $process->run();
    }

    if (!$process->isSuccessful()) {
        $this->error('Failed to install package: ' . $process->getErrorOutput());
        return false;
    }

    $this->info('Package installed successfully.');
    return true;
}
```

#### 2. Copying Files/Directories

```php
public function run(): bool
{
    $stubPath = __DIR__ . '/../../../resources/stubs/yourfeature';
    $destinationPath = app_path('YourFeature');

    $replacements = [
        '{{namespace}}' => app()->getNamespace(),
        '{{variable}}' => 'value',
    ];

    if ($this->copyDirectory($stubPath, $destinationPath, $replacements)) {
        $this->info('Files copied successfully.');
        return true;
    }

    $this->error('Failed to copy files.');
    return false;
}
```

#### 3. Modifying Existing Files

```php
public function run(): bool
{
    $filePath = base_path('some/file.php');

    if (!$this->filesystem->exists($filePath)) {
        $this->error('File not found: ' . $filePath);
        return false;
    }

    $content = $this->filesystem->get($filePath);

    // Check if modification already exists
    if (str_contains($content, 'your_marker')) {
        $this->info('File already modified.');
        return true;
    }

    // Modify content
    $newContent = str_replace('search', 'replace', $content);

    if ($this->filesystem->put($filePath, $newContent) === false) {
        $this->error('Failed to update file.');
        return false;
    }

    $this->info('File updated successfully.');
    return true;
}
```

#### 4. Running NPM/Bun Commands

```php
use Symfony\Component\Process\Process;

public function run(): bool
{
    $this->info('Installing npm packages...');

    $packages = ['package1', 'package2'];
    $process = new Process(array_merge(['bun', 'add', '-D'], $packages), base_path());
    $process->setTimeout(300);

    if ($this->command) {
        $process->run(function ($type, $buffer) {
            $this->command->line($buffer);
        });
    } else {
        $process->run();
    }

    if (!$process->isSuccessful()) {
        $this->error('Failed to install packages: ' . $process->getErrorOutput());
        return false;
    }

    $this->info('Packages installed successfully.');
    return true;
}
```

#### 5. Updating JSON Files (like package.json)

```php
public function run(): bool
{
    $jsonPath = base_path('package.json');

    if (!$this->filesystem->exists($jsonPath)) {
        $this->error('package.json not found');
        return false;
    }

    $json = json_decode($this->filesystem->get($jsonPath), true);

    if (!is_array($json)) {
        $this->error('Failed to parse package.json');
        return false;
    }

    // Modify JSON
    if (!isset($json['scripts'])) {
        $json['scripts'] = [];
    }

    $json['scripts']['your-command'] = 'your command here';

    $newContent = json_encode($json, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES) . "\n";

    if ($this->filesystem->put($jsonPath, $newContent) === false) {
        $this->error('Failed to update package.json');
        return false;
    }

    $this->info('Updated package.json');
    return true;
}
```

### Stub Files

Place your stub files in `resources/stubs/yourfeature/`. The directory structure should mirror where files will be copied in the target application.

Example structure:

```
resources/stubs/yourfeature/
├── app/
│   └── YourFeature/
│       └── ExampleClass.php
├── config/
│   └── yourfeature.php
└── resources/
    └── views/
        └── yourfeature/
            └── index.blade.php
```

### Using the Setup

Public setup commands are intentionally limited to `app`, `filament`, and `multilanguage`. If a new setup should become public, add it explicitly to `SetupCommand::SETUPS`.

### Best Practices

1. **Task Order Matters**: Tasks are executed in the order they're defined in the `$tasks` array
2. **Idempotency**: Tasks should be idempotent - running them multiple times should not cause errors
3. **Error Handling**: Always return `false` on failure and use `$this->error()` to provide feedback
4. **Progress Feedback**: Use `$this->info()` to keep users informed of progress
5. **Check Before Modifying**: Always check if changes have already been applied
6. **Replacements**: Use the `$replacements` parameter when copying files to customize content
7. **Timeouts**: Set appropriate timeouts for long-running processes

### Available Helper Methods in Task Base Class

-   `$this->info($message)` - Output info message
-   `$this->error($message)` - Output error message
-   `$this->copyFile($from, $to, $replacements = [])` - Copy single file with replacements
-   `$this->copyDirectory($from, $to, $replacements = [])` - Copy directory recursively with replacements
-   `$this->filesystem` - Access to Laravel's Filesystem instance
-   `$this->command` - Access to the Command instance (if available)

### Testing Setups

When developing Setups:

1. Test on a fresh Laravel installation
2. Run the setup multiple times to ensure idempotency
3. Verify all files are created in the correct locations
4. Check that all replacements work correctly
5. Ensure error cases are handled gracefully

## CI/CD Workflows

### GitHub Actions Workflows

The package uses several GitHub Actions workflows for continuous integration and maintenance:

| Workflow | Trigger | Purpose |
|----------|---------|---------|
| `run-tests.yml` | Push (PHP files) | Matrix testing across PHP 8.3-8.4, Laravel 10-12 |
| `integration-tests.yml` | Push to main, PRs | Tests scaffolding against craft-starterkit |
| `phpstan.yml` | Push (PHP files) | Static analysis at level 5 |
| `fix-php-code-style-issues.yml` | Push (PHP files) | Auto-fixes with Laravel Pint |
| `dependabot-auto-merge.yml` | PR from Dependabot | Auto-merges minor/patch updates |
| `weekly-dependency-update.yml` | Weekly (Sunday midnight UTC) | Full dependency update with integration testing |

### Weekly Dependency Update Workflow

The `weekly-dependency-update.yml` workflow runs every Sunday at midnight UTC and performs:

1. **Dependency Updates**: Runs `composer update` to update all dependencies
2. **Unit Tests**: Runs `pest --ci` to ensure tests still pass
3. **Static Analysis**: Runs `phpstan` to check for type errors
4. **Starterkit Integration Test**:
   - Clones `craft-starterkit`, `waymaker`, and `laravel-toolbar`
   - Configures composer to use the updated local package
   - Runs `php artisan craft:setup app` (Auth + Dashboard)
   - Updates Bun dependencies
   - Builds frontend assets
   - Verifies homepage returns HTTP 200
5. **PR Creation**: If all tests pass, creates a PR with the updates
6. **Failure Notification**: If tests fail, creates a GitHub issue for investigation

**Manual Trigger**: The workflow can be triggered manually from the Actions tab with the option to enable/disable PR creation.

### Required Secrets

- `GH_PAT`: Personal Access Token with repo access for checking out private repositories and creating PRs/issues

### Dependabot Configuration

Dependabot is configured to check weekly for:
- GitHub Actions updates
- Composer dependency updates

Minor and patch updates are auto-merged when CI passes.
